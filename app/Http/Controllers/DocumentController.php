<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentClassifierService;
use App\Services\DocumentFieldExtractorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

class DocumentController extends Controller
{
    // ===================== STAFF =====================

    public function staffDashboard()
    {
        $userId = Auth::id();

        $total    = Document::where('user_id', $userId)->count();
        $pending  = Document::where('user_id', $userId)->where('status', 'pending')->count();
        $approved = Document::where('user_id', $userId)->where('status', 'approved')->count();
        $rejected = Document::where('user_id', $userId)->where('status', 'rejected')->count();

        $documents = Document::where('user_id', $userId)->latest()->take(5)->get();

        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[] = Document::where('user_id', $userId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', $month)
                ->count();
        }

        return view('staff.dashboard', compact(
            'total', 'pending', 'approved', 'rejected', 'documents', 'monthlyData'
        ));
    }

    public function index()
    {
        $documents = Document::where('user_id', Auth::id())->latest()->paginate(10);

        return view('staff.documents', compact('documents'));
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
            'file_name'  => $file->getClientOriginalName(),
            'file_path'  => $path,
            'status'     => 'draft',
            'user_id'    => Auth::id(),
            'ocr_text'   => null,
            'ship_name'  => null,
            'category'   => null,
            'ocr_result' => null,
            'confidence' => null,
        ]);

        return redirect()
            ->route('documents.show', $document->id)
            ->with('success', 'Dokumen berhasil diupload. Silakan lanjut proses OCR.');
    }

    public function show($id)
    {
        $document = Document::findOrFail($id);

        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        return view('staff.document-detail', compact('document'));
    }

    public function download($id)
    {
        $doc = Document::findOrFail($id);

        if (Auth::user()->role === 'staff' && $doc->user_id !== Auth::id()) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($doc->file_path)) {
            return back()->with('error', 'File tidak ditemukan');
        }

        return Storage::disk('public')->download($doc->file_path, $doc->file_name);
    }

    public function processOCR($id)
    {
        set_time_limit(300);

        $document = Document::findOrFail($id);

        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        $pdfPath = storage_path('app/public/' . $document->file_path);

        $tempDir = storage_path("app/temp/doc_{$document->id}");
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $outputPrefix = $tempDir . '/page';
        $popplerPath  = 'C:\poppler\Library\bin\pdftoppm.exe';

        $command = "\"$popplerPath\" -png -r 200 \"$pdfPath\" \"$outputPrefix\"";
        shell_exec($command);

        $images = glob($tempDir . '/page-*.png');
        sort($images);

        $fullText = '';

        foreach ($images as $imagePath) {
            $ocr = new TesseractOCR($imagePath);
            $ocr->executable('C:\Program Files\Tesseract-OCR\tesseract.exe');
            $ocr->lang('eng', 'ind');

            $fullText .= $ocr->run() . "\n\n";

            unlink($imagePath);
        }

        @rmdir($tempDir);

        $classifier     = new DocumentClassifierService();
        $classification = $classifier->classify($fullText);

        $extractor = new DocumentFieldExtractorService();
        $extracted = $extractor->extract($fullText);

        $document->update([
            'ocr_text'         => $fullText,
            'category'         => $classification['category'],
            'confidence_score' => $classification['confidence'],
            'vessel_name'      => $extracted['vessel_name'],
            'voyage'           => $extracted['voyage'],
            'loading_date'     => $extracted['loading_date'],
            'discharge_date'   => $extracted['discharge_date'],
            'bl_liters_obs'    => $extracted['bl_liters_obs'],
            'liters_15c'       => $extracted['liters_15c'],
        ]);

        return back()->with('success', 'OCR berhasil diproses. Kategori: ' . $classification['label']);
    }

    public function submit($id)
    {
        $document = Document::findOrFail($id);

        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$document->ocr_text) {
            return back()->with('error', 'Dokumen harus diproses OCR sebelum disubmit ke admin.');
        }

        $document->update(['status' => 'pending']);

        return back()->with('success', 'Dokumen berhasil disubmit ke admin untuk verifikasi.');
    }

    // ===================== ADMIN =====================

    public function adminDashboard()
    {
        $total    = Document::whereIn('status', ['pending', 'approved', 'rejected'])->count();
        $pending  = Document::where('status', 'pending')->count();
        $approved = Document::where('status', 'approved')->count();
        $rejected = Document::where('status', 'rejected')->count();

        $documents = Document::whereIn('status', ['pending', 'approved', 'rejected'])
            ->latest()
            ->take(5)
            ->get();

        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[] = Document::whereIn('status', ['pending', 'approved', 'rejected'])
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', $month)
                ->count();
        }

        return view('admin.dashboard', compact(
            'total', 'pending', 'approved', 'rejected', 'documents', 'monthlyData'
        ));
    }

    public function adminIndex()
    {
        $documents = Document::whereIn('status', ['pending', 'approved', 'rejected'])
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
        $doc = Document::findOrFail($id);

        if ($doc->status !== 'pending') {
            return redirect()->route('admin.documents')->with('error', 'Dokumen sudah diproses');
        }

        $doc->update(['status' => 'approved']);

        return redirect()->route('admin.documents')->with('success', 'Dokumen berhasil di-approve');
    }

    public function reject($id)
    {
        $doc = Document::findOrFail($id);

        if ($doc->status !== 'pending') {
            return redirect()->route('admin.documents')->with('error', 'Dokumen sudah diproses');
        }

        $doc->update(['status' => 'rejected']);

        return redirect()->route('admin.documents')->with('success', 'Dokumen berhasil di-reject');
    }

    public function library()
    {
        $documents = Document::whereIn('status', ['pending', 'approved', 'rejected'])
            ->whereNotNull('vessel_name')
            ->get();

        $ships = $documents
            ->groupBy(function ($doc) {
                return Document::normalizeVesselName($doc->vessel_name) . '|' . ($doc->voyage ?? 'NOVOYAGE');
            })
            ->map(function ($group) {
                $first = $group->first();

                $categoryLabels = [
                    'invoice'   => 'Invoice',
                    'loading'   => 'Loading',
                    'unloading' => 'Bongkar',
                ];

                $categories = $group
                    ->pluck('category')
                    ->filter()
                    ->map(fn ($cat) => $categoryLabels[$cat] ?? $cat)
                    ->unique()
                    ->values();

                return (object) [
                    'ship_name'       => $first->vessel_name,
                    'voyage'          => $first->voyage ?? '-',
                    'total_documents' => $group->count(),
                    'categories'      => $categories,
                    'document_ids'    => $group->pluck('id'),
                ];
            })
            ->values();

        return view('admin.library', compact('ships'));
    }

    public function monitoring()
    {
        $documents = Document::whereIn('status', ['pending', 'approved', 'rejected'])
            ->whereNotNull('vessel_name')
            ->with('user')
            ->get();

        $total    = $documents->count();
        $pending  = $documents->where('status', 'pending')->count();
        $approved = $documents->where('status', 'approved')->count();
        $rejected = $documents->where('status', 'rejected')->count();

        $categoryLabels = [
            'invoice'   => 'Invoice',
            'loading'   => 'Loading',
            'unloading' => 'Bongkar',
        ];

        $ships = $documents
            ->groupBy(function ($doc) {
                return Document::normalizeVesselName($doc->vessel_name) . '|' . ($doc->voyage ?? 'NOVOYAGE');
            })
            ->map(function ($group) use ($categoryLabels) {
                $first = $group->first();

                $categories = $group
                    ->pluck('category')
                    ->filter()
                    ->map(fn ($cat) => $categoryLabels[$cat] ?? $cat)
                    ->unique()
                    ->values();

                return (object) [
                    'ship_name'       => $first->vessel_name,
                    'voyage'          => $first->voyage ?? '-',
                    'total_documents' => $group->count(),
                    'categories'      => $categories,
                    'pending'         => $group->where('status', 'pending')->count(),
                    'approved'        => $group->where('status', 'approved')->count(),
                    'rejected'        => $group->where('status', 'rejected')->count(),
                    'documents'       => $group->map(fn ($d) => (object) [
                        'id'         => $d->id,
                        'file_name'  => $d->file_name,
                        'status'     => $d->status,
                        'uploader'   => $d->user->name ?? '-',
                        'created_at' => $d->created_at,
                    ]),
                ];
            })
            ->values();

        return view('admin.monitoring', compact(
            'ships', 'total', 'pending', 'approved', 'rejected'
        ));
    }
}