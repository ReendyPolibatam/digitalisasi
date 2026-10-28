<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentClassifierService;
use App\Services\DocumentFieldExtractorService;
use App\Services\OcrService;
use App\Services\PdfPageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    // Status dokumen yang sudah masuk ke admin (bukan draft)
    private const SUBMITTED_STATUSES = ['pending', 'approved', 'rejected'];

    private const CATEGORY_LABELS = [
        'invoice' => 'Invoice',
        'loading' => 'Loading',
        'unloading' => 'Bongkar',
        'bongkar' => 'Bongkar',
    ];

    // ===================== STAFF =====================

    public function staffDashboard()
    {
        $base = Document::where('user_id', Auth::id());

        return view('staff.dashboard', array_merge($this->dashboardStats($base), [
            'documents' => (clone $base)->latest()->take(5)->get(),
        ]));
    }

    public function index()
    {
        // get() (bukan paginate) supaya satu kapal tidak terpecah antar halaman
        $documents = Document::where('user_id', Auth::id())->latest()->get();

        // Statistik untuk kartu di atas
        $total = $documents->count();
        $draft = $documents->where('status', 'draft')->count();
        $pending = $documents->where('status', 'pending')->count();
        $approved = $documents->where('status', 'approved')->count();
        $rejected = $documents->where('status', 'rejected')->count();

        // Dokumen yang belum di-OCR ditampilkan terpisah di "Menunggu Proses OCR"
        $waitingOcr = $documents
            ->filter(fn ($d) => empty($d->ocr_text))
            ->values();

        $processed = $documents->reject(fn ($d) => empty($d->ocr_text));

        // Satu kartu per kapal (nama dinormalisasi), voyage ditampilkan di dalam kartu
        $ships = $processed
            ->groupBy(function ($d) {
                $key = $d->vessel_name ? Document::normalizeVesselName($d->vessel_name) : '';

                return $key !== '' ? $key : 'TIDAK_TERDETEKSI';
            })
            ->map(function ($group, $key) {
                $count = $group->count();
                $approved = $group->where('status', 'approved')->count();
                $detected = $key !== 'TIDAK_TERDETEKSI';

                $voyages = $group
                    ->filter(fn ($d) => $this->voyageKey($d->voyage) !== 'NOVOYAGE')
                    ->groupBy(fn ($d) => $this->voyageKey($d->voyage))
                    ->map(fn ($g) => $this->displayVoyage($g))
                    ->values();

                return (object) [
                    'detected' => $detected,
                    'vessel_name' => $detected ? $this->displayVesselName($group) : 'Kapal tidak terdeteksi',
                    'voyages' => $voyages,
                    'total_documents' => $count,
                    'categories' => $this->categoryLabels($group),
                    'draft' => $group->where('status', 'draft')->count(),
                    'pending' => $group->where('status', 'pending')->count(),
                    'approved' => $approved,
                    'rejected' => $group->where('status', 'rejected')->count(),
                    'all_approved' => $count > 0 && $approved === $count,
                    'progress' => $count > 0 ? (int) round($approved / $count * 100) : 0,
                    'documents' => $group->sortByDesc('created_at')->values(),
                ];
            })
            // Kapal tidak terdeteksi paling atas (perlu dicek staff), lalu yang belum selesai, lalu urut nama
            ->sortBy([
                fn ($a, $b) => $a->detected <=> $b->detected,
                fn ($a, $b) => $a->all_approved <=> $b->all_approved,
                fn ($a, $b) => strcmp($a->vessel_name, $b->vessel_name),
            ])
            ->values();

        return view('staff.documents', compact(
            'ships', 'waitingOcr', 'total', 'draft', 'pending', 'approved', 'rejected'
        ));
    }

    public function create()
    {
        return view('staff.upload');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        $document = Document::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'draft',
            'user_id' => Auth::id(),
            'ocr_text' => null,
            'vessel_name' => null,
            'category' => null,
        ]);

        return redirect()
            ->route('documents.show', $document->id)
            ->with('success', 'Dokumen berhasil diupload. Silakan lanjut proses OCR.');
    }

    public function show($id)
    {
        $document = $this->ownedDocument($id);

        return view('staff.document-detail', compact('document'));
    }

    public function download($id)
    {
        $doc = Document::findOrFail($id);

        // Default-nya tolak: hanya admin atau pemilik dokumen yang boleh mengunduh
        if (Auth::user()->role !== 'admin' && $doc->user_id !== Auth::id()) {
            abort(403);
        }

        if (! Storage::disk('public')->exists($doc->file_path)) {
            return back()->with('error', 'File tidak ditemukan');
        }

        return Storage::disk('public')->download($doc->file_path, $doc->file_name);
    }

    public function processOCR($id)
    {
        // PDF banyak halaman (foto, perlu diputar/diperbesar) bisa butuh beberapa menit
        set_time_limit(900);

        $document = $this->ownedDocument($id);

        if ($document->status !== 'draft') {
            return back()->with('error', 'Dokumen sudah disubmit, OCR tidak bisa diulang.');
        }

        if (! Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'File dokumen tidak ditemukan di storage.');
        }

        $sourcePath = Storage::disk('public')->path($document->file_path);
        $pages = app(OcrService::class)->pages($sourcePath);

        if (trim(implode('', $pages)) === '') {
            return back()->with('error', 'OCR tidak menghasilkan teks. Periksa kualitas dokumen lalu coba lagi.');
        }

        $groups = $this->groupPagesByCategory($pages);

        // Satu file berisi beberapa kategori: dipecah jadi satu dokumen per kategori
        if (count($groups) > 1 && strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION)) === 'pdf') {
            $documents = $this->splitDocument($document, $sourcePath, $groups);

            $summary = $documents
                ->map(fn ($d, $i) => $groups[$i]['label'].' ('.count($groups[$i]['pages']).' hal.)')
                ->implode(', ');

            return back()->with('success', "OCR berhasil. File dipisah menjadi {$documents->count()} dokumen: {$summary}.");
        }

        $group = $groups[0];

        $document->update(array_merge($this->groupAttributes($group), [
            'ocr_text_edited' => null, // OCR ulang menggantikan hasil editan lama
        ]));

        return back()->with('success', 'OCR berhasil diproses. Kategori: '.$group['label']);
    }

    /**
     * Simpan hasil edit teks OCR sebagai draft (belum disubmit).
     */
    public function updateOcrText(Request $request, $id)
    {
        $document = $this->ownedDocument($id);

        if ($document->status !== 'draft') {
            abort(403, 'Dokumen sudah disubmit dan tidak bisa diedit.');
        }

        $data = $request->validate([
            'ocr_text' => ['required', 'string'],
        ]);

        $document->update([
            'ocr_text_edited' => $this->normalizeLineBreaks($data['ocr_text']),
        ]);

        return back()->with('success', 'Perubahan teks disimpan.');
    }

    public function submit(Request $request, $id)
    {
        $document = $this->ownedDocument($id);

        if ($document->status !== 'draft') {
            return back()->with('error', 'Dokumen ini sudah disubmit.');
        }

        if (! $document->ocr_text) {
            return back()->with('error', 'Dokumen harus diproses OCR sebelum disubmit ke admin.');
        }

        $request->validate([
            'ocr_text' => ['nullable', 'string'],
        ]);

        // Teks dari textarea ikut tersimpan, jadi editan yang belum di-"Simpan" tidak hilang
        if ($request->has('ocr_text')) {
            if (! $request->filled('ocr_text')) {
                return back()->with('error', 'Teks hasil OCR tidak boleh kosong.');
            }

            $document->ocr_text_edited = $this->normalizeLineBreaks($request->input('ocr_text'));
        }

        // Klasifikasi dan ekstraksi dijalankan ulang dari teks final,
        // supaya nama kapal dan voyage mengikuti koreksi staff
        $finalText = $document->ocr_text_edited ?: $document->ocr_text;
        $attributes = $this->analyzeText($finalText)['attributes'];

        // Kategori sudah ditentukan per halaman saat OCR, jangan ditimpa klasifikasi teks gabungan
        if ($document->category) {
            unset($attributes['category'], $attributes['confidence_score']);
        }

        $document->fill($attributes);
        $document->status = 'pending';
        $document->submitted_at = now();
        $document->save();

        return redirect()
            ->route('documents.index')
            ->with('success', 'Dokumen berhasil disubmit ke admin untuk verifikasi.');
    }

    // ===================== ADMIN =====================

    public function adminDashboard()
    {
        $base = Document::whereIn('status', self::SUBMITTED_STATUSES);

        return view('admin.dashboard', array_merge($this->dashboardStats($base), [
            'documents' => (clone $base)->latest()->take(5)->get(),
        ]));
    }

    public function adminIndex()
    {
        $documents = Document::whereIn('status', self::SUBMITTED_STATUSES)
            ->latest()
            ->paginate(10);

        return view('admin.documents', compact('documents'));
    }

    public function showDocument($id)
    {
        $document = Document::findOrFail($id);

        return view('admin.document-detail', compact('document'));
    }

    public function approve($id)
    {
        $processed = $this->resolvePending($id, [
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        if (! $processed) {
            return redirect()->route('admin.documents')->with('error', 'Dokumen sudah diproses');
        }

        return redirect()->route('admin.documents')->with('success', 'Dokumen berhasil di-approve');
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $processed = $this->resolvePending($id, [
            'status' => 'rejected',
            'admin_note' => $data['admin_note'] ?? null,
        ]);

        if (! $processed) {
            return redirect()->route('admin.documents')->with('error', 'Dokumen sudah diproses');
        }

        return redirect()->route('admin.documents')->with('success', 'Dokumen berhasil di-reject');
    }

    // ---------- Library: kapal -> voyage -> klasifikasi ----------

    public function library()
    {
        $ships = $this->libraryDocuments()
            ->groupBy(fn ($doc) => Document::normalizeVesselName($doc->vessel_name))
            ->map(fn ($group, $key) => (object) [
                'key' => $key,
                'vessel_name' => $this->displayVesselName($group),
                'total_voyages' => $group->groupBy(fn ($d) => $this->voyageKey($d->voyage))->count(),
                'total_documents' => $group->count(),
            ])
            ->sortBy('vessel_name')
            ->values();

        return view('admin.library', compact('ships'));
    }

    public function libraryVessel(string $vessel)
    {
        $documents = $this->vesselDocuments($vessel);

        $voyages = $documents
            ->groupBy(fn ($d) => $this->voyageKey($d->voyage))
            ->map(fn ($group, $key) => (object) [
                'key' => $key,
                'voyage' => $this->displayVoyage($group),
                'total_documents' => $group->count(),
                'pending' => $group->where('status', 'pending')->count(),
                'categories' => $this->categoryLabels($group),
            ])
            ->sortBy('voyage')
            ->values();

        return view('admin.library-vessel', [
            'vesselKey' => $vessel,
            'vesselName' => $this->displayVesselName($documents),
            'voyages' => $voyages,
        ]);
    }

    public function libraryVoyage(string $vessel, string $voyage)
    {
        $vesselDocs = $this->vesselDocuments($vessel);

        $documents = $vesselDocs
            ->filter(fn ($d) => $this->voyageKey($d->voyage) === $voyage)
            ->values();

        abort_if($documents->isEmpty(), 404);

        $byCategory = $documents->groupBy(fn ($d) => match ($d->category) {
            'invoice' => 'invoice',
            'unloading', 'bongkar' => 'bongkar',
            'loading' => 'loading',
            default => 'lainnya',
        });

        $sections = collect([
            'invoice' => 'Invoice',
            'bongkar' => 'Dokumen Bongkar',
            'loading' => 'Dokumen Loading',
        ])->map(fn ($label, $key) => (object) [
            'label' => $label,
            'documents' => $byCategory->get($key, collect()),
        ]);

        return view('admin.library-voyage', [
            'vesselKey' => $vessel,
            'vesselName' => $this->displayVesselName($vesselDocs),
            'voyageName' => $this->displayVoyage($documents),
            'sections' => $sections,
            'uncategorized' => $byCategory->get('lainnya', collect()),
        ]);
    }

    public function monitoring()
    {
        $documents = $this->libraryDocuments();

        $total = $documents->count();
        $pending = $documents->where('status', 'pending')->count();
        $approved = $documents->where('status', 'approved')->count();
        $rejected = $documents->where('status', 'rejected')->count();

        $ships = $documents
            // Satu kartu per kapal, voyage ditampilkan di dalam kartu
            ->groupBy(fn ($doc) => Document::normalizeVesselName($doc->vessel_name))
            ->map(function ($group) {
                $count = $group->count();
                $approvedCount = $group->where('status', 'approved')->count();
                $rejectedCount = $group->where('status', 'rejected')->count();
                $pendingCount = $group->where('status', 'pending')->count();

                // Daftar voyage unik (dokumen tanpa voyage tidak dihitung)
                $voyages = $group
                    ->filter(fn ($d) => $this->voyageKey($d->voyage) !== 'NOVOYAGE')
                    ->groupBy(fn ($d) => $this->voyageKey($d->voyage))
                    ->map(fn ($g) => $this->displayVoyage($g))
                    ->values();

                return (object) [
                    'vessel_name' => $this->displayVesselName($group),
                    'voyages' => $voyages,
                    'voyage' => $voyages->isEmpty() ? '-' : $voyages->implode(', '),
                    'total_documents' => $count,
                    'categories' => $this->categoryLabels($group),
                    'pending' => $pendingCount,
                    'approved' => $approvedCount,
                    'rejected' => $rejectedCount,
                    'all_approved' => $count > 0 && $approvedCount === $count,
                    'progress' => $count > 0 ? (int) round($approvedCount / $count * 100) : 0,
                    'documents' => $group
                        ->sortByDesc('created_at')
                        ->values()
                        ->map(fn ($d) => (object) [
                            'id' => $d->id,
                            'file_name' => $d->file_name,
                            'status' => $d->status,
                            'voyage' => $d->voyage,
                            'uploader' => $d->user->name ?? '-',
                            'created_at' => $d->created_at,
                        ]),
                ];
            })
            // Kapal yang belum selesai tampil duluan, lalu urut nama
            ->sortBy([
                fn ($a, $b) => $a->all_approved <=> $b->all_approved,
                fn ($a, $b) => strcmp($a->vessel_name, $b->vessel_name),
            ])
            ->values();

        return view('admin.monitoring', compact(
            'ships', 'total', 'pending', 'approved', 'rejected'
        ));
    }

    // ===================== HELPER =====================

    /**
     * Ambil dokumen milik staff yang sedang login, selain itu 403.
     */
    private function ownedDocument($id): Document
    {
        $document = Document::findOrFail($id);

        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        return $document;
    }

    /**
     * Ubah dokumen berstatus pending menjadi approved/rejected secara atomik.
     * Kalau dua admin menekan tombol bersamaan, hanya satu yang berhasil.
     */
    private function resolvePending($id, array $attributes): bool
    {
        Document::findOrFail($id); // 404 kalau dokumen memang tidak ada

        return Document::where('id', $id)
            ->where('status', 'pending')
            ->update($attributes) > 0;
    }

    /**
     * Statistik dashboard dengan 2 query (bukan 16): hitungan per status dan per bulan.
     * $base adalah query dasar yang sudah dibatasi (milik staff, atau yang sudah disubmit).
     */
    private function dashboardStats(Builder $base): array
    {
        $byStatus = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $byMonth = (clone $base)
            ->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()])
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        return [
            'total' => (int) $byStatus->sum(),
            'pending' => (int) ($byStatus['pending'] ?? 0),
            'approved' => (int) ($byStatus['approved'] ?? 0),
            'rejected' => (int) ($byStatus['rejected'] ?? 0),
            'monthlyData' => collect(range(1, 12))
                ->map(fn ($m) => (int) ($byMonth[$m] ?? 0))
                ->all(),
        ];
    }

    /**
     * Jalankan klasifikasi dan ekstraksi field dari sebuah teks.
     */
    private function analyzeText(string $text): array
    {
        $classification = (new DocumentClassifierService)->classify($text);
        $extracted = (new DocumentFieldExtractorService)->extract($text);

        return [
            'label' => $classification['label'],
            'attributes' => [
                'category' => $classification['category'],
                'confidence_score' => $classification['confidence'],
                'vessel_name' => $extracted['vessel_name'],
                'voyage' => $extracted['voyage'],
                'loading_date' => $extracted['loading_date'],
                'discharge_date' => $extracted['discharge_date'],
                'bl_liters_obs' => $extracted['bl_liters_obs'],
                'liters_15c' => $extracted['liters_15c'],
            ],
        ];
    }

    /**
     * Klasifikasi per halaman, lalu kelompokkan halaman per kategori (urutan kemunculan
     * pertama dipertahankan). Halaman yang sama sekali tidak dikenali jadi satu grup tanpa kategori.
     *
     * @param  list<string>  $pages
     * @return list<array{category: ?string, label: string, confidence: float, pages: list<int>, text: string}>
     */
    private function groupPagesByCategory(array $pages): array
    {
        $groups = [];

        foreach ((new DocumentClassifierService)->classifyPages($pages) as $i => $result) {
            $key = $result['category'] ?? '';

            $groups[$key] ??= [
                'category' => $result['category'],
                'label' => $result['label'],
                'confidences' => [],
                'pages' => [],
                'texts' => [],
            ];

            $groups[$key]['confidences'][] = $result['confidence'];
            $groups[$key]['pages'][] = $i + 1;
            $groups[$key]['texts'][] = $pages[$i];
        }

        return array_values(array_map(fn (array $group) => [
            'category' => $group['category'],
            'label' => $group['label'],
            'confidence' => round(array_sum($group['confidences']) / count($group['confidences']), 2),
            'pages' => $group['pages'],
            'text' => implode("\n\n", $group['texts']),
        ], $groups));
    }

    /**
     * Atribut dokumen dari satu grup halaman: teks OCR, kategori, dan field hasil ekstraksi.
     *
     * @param  array{category: ?string, label: string, confidence: float, pages: list<int>, text: string}  $group
     */
    private function groupAttributes(array $group): array
    {
        return array_merge($this->analyzeText($group['text'])['attributes'], [
            'ocr_text' => $group['text'],
            'category' => $group['category'],
            'confidence_score' => $group['confidence'],
        ]);
    }

    /**
     * Pecah PDF menjadi satu file per kategori. Dokumen asli dipakai untuk grup pertama,
     * grup lain menjadi dokumen draft baru milik staff yang sama. File asli dihapus
     * karena seluruh halamannya sudah tersalin ke file hasil pemecahan.
     *
     * @param  list<array{category: ?string, label: string, confidence: float, pages: list<int>, text: string}>  $groups
     * @return Collection<int, Document>
     */
    private function splitDocument(Document $document, string $sourcePath, array $groups): Collection
    {
        $disk = Storage::disk('public');
        $baseName = pathinfo($document->file_name, PATHINFO_FILENAME);
        $oldPath = $document->file_path;
        $results = collect();

        foreach ($groups as $i => $group) {
            $path = 'documents/'.Str::random(40).'.pdf';
            app(PdfPageService::class)->extract($sourcePath, $group['pages'], $disk->path($path));

            $attributes = array_merge($this->groupAttributes($group), [
                'file_name' => "{$baseName} - {$group['label']}.pdf",
                'file_path' => $path,
                'ocr_text_edited' => null,
            ]);

            if ($i === 0) {
                $document->update($attributes);
                $results->push($document);
            } else {
                $results->push(Document::create(array_merge($attributes, [
                    'status' => 'draft',
                    'user_id' => $document->user_id,
                ])));
            }
        }

        $disk->delete($oldPath);

        return $results;
    }

    /**
     * Textarea browser mengirim baris baru berupa CRLF, samakan dengan hasil Tesseract (LF).
     */
    private function normalizeLineBreaks(string $text): string
    {
        return str_replace("\r\n", "\n", $text);
    }

    /**
     * Dokumen yang tampil di library/monitoring: sudah disubmit dan nama kapalnya terdeteksi.
     */
    private function libraryDocuments(): Collection
    {
        return Document::with('user')
            ->whereIn('status', self::SUBMITTED_STATUSES)
            ->whereNotNull('vessel_name')
            ->get()
            ->filter(fn ($d) => Document::normalizeVesselName($d->vessel_name) !== '')
            ->values();
    }

    /**
     * Semua dokumen library milik satu kapal (berdasarkan key). 404 kalau kosong.
     */
    private function vesselDocuments(string $vessel): Collection
    {
        $documents = $this->libraryDocuments()
            ->filter(fn ($d) => Document::normalizeVesselName($d->vessel_name) === $vessel)
            ->values();

        abort_if($documents->isEmpty(), 404);

        return $documents;
    }

    /**
     * Daftar label kategori unik dari sekelompok dokumen.
     */
    private function categoryLabels(Collection $group): Collection
    {
        return $group
            ->pluck('category')
            ->filter()
            ->map(fn ($cat) => self::CATEGORY_LABELS[$cat] ?? $cat)
            ->unique()
            ->values();
    }

    /**
     * Nama kapal untuk ditampilkan: huruf besar, dan pilih ejaan yang punya spasi.
     */
    private function displayVesselName($group): string
    {
        $names = $group->pluck('vessel_name')
            ->filter()
            ->map(fn ($n) => mb_strtoupper(trim(preg_replace('/\s+/', ' ', $n))))
            ->countBy();

        return $names->keys()
            ->sortByDesc(fn ($n) => substr_count($n, ' ') * 1000 + $names[$n])
            ->first() ?? '-';
    }

    /**
     * Hapus segmen satu huruf L/D (Loading/Discharge) di antara garis miring,
     * supaya 022/L/P.G/... dan 022/D/P.G/... dianggap satu voyage.
     */
    private function stripLoadDischargeMarker(string $voyage): string
    {
        return preg_replace('#/[LD](?=/)#i', '', trim($voyage));
    }

    /**
     * Kunci voyage untuk URL dan pengelompokan: huruf dan angka saja.
     */
    private function voyageKey(?string $voyage): string
    {
        $voyage = $this->stripLoadDischargeMarker(strtoupper((string) $voyage));
        $key = preg_replace('/[^A-Z0-9]/', '', $voyage);

        return $key !== '' ? $key : 'NOVOYAGE';
    }

    /**
     * Nama voyage untuk ditampilkan: ejaan yang paling sering muncul, tanpa penanda L/D.
     */
    private function displayVoyage($group): string
    {
        $raw = $group->pluck('voyage')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        if (! $raw) {
            return 'Voyage tidak terdeteksi';
        }

        return $this->stripLoadDischargeMarker($raw);
    }
}
