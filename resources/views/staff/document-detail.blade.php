@extends('layouts.staff')

@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Detail Dokumen</h1>
    <p class="text-gray-500 mt-2">Upload, OCR, dan klasifikasi dokumen shipping</p>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-300 text-green-700 p-4 rounded-lg mb-6">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded-lg mb-6">
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- PDF Preview --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow">
            <div class="border-b px-6 py-4">
                <h2 class="font-semibold text-gray-700">Preview Dokumen</h2>
            </div>

            <div class="p-4">
                @php $extension = pathinfo($document->file_name, PATHINFO_EXTENSION); @endphp

                @if(strtolower($extension) == 'pdf')
                    <iframe
                        src="{{ asset('storage/' . $document->file_path) }}"
                        class="w-full h-[700px] rounded-lg border">
                    </iframe>
                @else
                    <img src="{{ asset('storage/' . $document->file_path) }}"
                         class="w-full rounded-lg border">
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        <div class="bg-white rounded-xl shadow">
            <div class="border-b px-6 py-4">
                <h2 class="font-semibold text-gray-700">Informasi Dokumen</h2>
            </div>

            <div class="p-6 space-y-4">

                <div>
                    <label class="text-sm text-gray-500">Nama File</label>
                    <p class="font-medium">{{ $document->file_name }}</p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Status</label>
                    <p>
                        @if($document->status == 'approved')
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Approved</span>
                        @elseif($document->status == 'rejected')
                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">Rejected</span>
                        @elseif($document->status == 'pending')
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">Menunggu Verifikasi</span>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">Draft</span>
                        @endif
                    </p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Tanggal Upload</label>
                    <p>{{ $document->created_at->format('d M Y H:i') }}</p>
                </div>

                <hr>

                <div class="space-y-3">

                    <form action="{{ route('documents.ocr', $document->id) }}"
                          method="POST"
                          id="ocrForm">
                        @csrf
                        <button type="submit"
                                id="ocrBtn"
                                class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-lg">
                            Proses OCR
                        </button>
                    </form>

                    @if($document->ocr_text)
                        @if($document->status == 'draft')
                            <form action="{{ route('documents.submit', $document->id) }}" method="POST" id="submitForm">
                                @csrf
                                {{-- Diisi dari textarea saat mode edit, supaya editan yang belum disimpan ikut terkirim --}}
                                <input type="hidden" name="ocr_text" id="submitOcrText" disabled>
                                <button type="submit"
                                        class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg">
                                    Submit ke Admin
                                </button>
                            </form>
                        @else
                            <button disabled
                                    class="block w-full text-center bg-gray-300 text-gray-500 py-3 rounded-lg cursor-not-allowed">
                                Sudah Disubmit
                            </button>
                        @endif
                    @else
                        <button disabled
                                class="block w-full text-center bg-gray-300 text-gray-500 py-3 rounded-lg cursor-not-allowed">
                            Submit ke Admin (Proses OCR Dahulu)
                        </button>
                    @endif

                </div>

            </div>
        </div>
    </div>

</div>

{{-- OCR RESULT --}}
<div class="mt-6">
    <div class="bg-white rounded-xl shadow">
        <div class="border-b px-6 py-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-gray-700">Hasil OCR</h2>
                @if($document->ocr_text_edited)
                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Sudah diedit</span>
                @endif
            </div>

            @if($document->ocr_text && $document->status == 'draft')
                <button type="button" id="ocrEditBtn"
                        class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg">
                    Edit Hasil OCR
                </button>
            @endif
        </div>

        <div class="p-6">
            @if($document->ocr_text)
                <form action="{{ route('documents.ocr.update', $document->id) }}" method="POST" id="ocrTextForm">
                    @csrf
                    <textarea name="ocr_text" id="ocrText"
                              class="w-full border rounded-lg p-4 h-96 read-only:bg-gray-50"
                              readonly>{{ $document->ocr_text_edited ?: $document->ocr_text }}</textarea>

                    @error('ocr_text')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror

                    <div id="ocrEditActions" class="hidden mt-4 flex justify-end gap-3">
                        <button type="button" id="ocrCancelBtn"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            @else
                <div class="text-center py-10 text-gray-500">
                    OCR belum dijalankan
                </div>
            @endif
        </div>
    </div>
</div>

{{-- KLASIFIKASI --}}
<div class="mt-6">
    <div class="bg-white rounded-xl shadow">
        <div class="border-b px-6 py-4 flex items-center justify-between">

            <h2 class="font-semibold text-gray-700">Hasil Klasifikasi</h2>

            @if($document->category)
                @php
                    $categoryLabels = [
                        'invoice'   => ['label' => 'Invoice', 'color' => 'bg-blue-100 text-blue-700'],
                        'loading'   => ['label' => 'Dokumen Loading', 'color' => 'bg-green-100 text-green-700'],
                        'bongkar'   => ['label' => 'Dokumen Bongkar', 'color' => 'bg-orange-100 text-orange-700'],
                        'unloading' => ['label' => 'Dokumen Bongkar', 'color' => 'bg-orange-100 text-orange-700'],
                    ];

                    $categoryInfo = $categoryLabels[$document->category] ?? [
                        'label' => 'Tidak Terklasifikasi',
                        'color' => 'bg-gray-100 text-gray-700',
                    ];
                @endphp

                <span class="px-3 py-1 {{ $categoryInfo['color'] }} rounded-full text-sm font-medium">
                    {{ $categoryInfo['label'] }}
                    @if($document->confidence_score)
                        ({{ number_format($document->confidence_score * 100, 0) }}%)
                    @endif
                </span>
            @else
                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">
                    Belum Diklasifikasikan
                </span>
            @endif

        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="text-sm text-gray-500">Nama Kapal</label>
                    <p class="font-medium text-gray-800 mt-1">{{ $document->vessel_name ?? '-' }}</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="text-sm text-gray-500">Voyage</label>
                    <p class="font-medium text-gray-800 mt-1">{{ $document->voyage ?? '-' }}</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="text-sm text-gray-500">Tanggal Loading</label>
                    <p class="font-medium text-gray-800 mt-1">
                        {{ $document->loading_date ? $document->loading_date->format('d M Y') : '-' }}
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="text-sm text-gray-500">Tanggal Bongkar</label>
                    <p class="font-medium text-gray-800 mt-1">
                        {{ $document->discharge_date ? $document->discharge_date->format('d M Y') : '-' }}
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="text-sm text-gray-500">BL Liters Observed</label>
                    <p class="font-medium text-gray-800 mt-1">
                        {{ $document->bl_liters_obs ? number_format($document->bl_liters_obs, 0, ',', '.') . ' L' : '-' }}
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="text-sm text-gray-500">Liters @ 15°C</label>
                    <p class="font-medium text-gray-800 mt-1">
                        {{ $document->liters_15c ? number_format($document->liters_15c, 0, ',', '.') . ' L' : '-' }}
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="text-sm text-gray-500">Tingkat Keyakinan</label>
                    <p class="font-medium text-gray-800 mt-1">
                        {{ $document->confidence_score ? number_format($document->confidence_score * 100, 0) . '%' : '-' }}
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Loading Overlay --}}
<div id="loadingOverlay"
     class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">

    <div class="bg-white rounded-2xl shadow-xl p-8 flex flex-col items-center gap-4 w-80">

        <div class="w-16 h-16 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin"></div>

        <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-800">Memproses OCR</h3>
            <p class="text-sm text-gray-500 mt-1">Sedang membaca dan menganalisis dokumen...</p>
            <p class="text-xs text-gray-400 mt-2">Proses ini bisa memakan waktu beberapa menit</p>
        </div>

        <div class="w-full bg-gray-200 rounded-full h-2">
            <div id="progressBar"
                 class="bg-purple-600 h-2 rounded-full transition-all duration-1000"
                 style="width: 0%">
            </div>
        </div>

        <p id="progressText" class="text-xs text-purple-600 font-medium">Memulai proses...</p>

    </div>

</div>

<script>
document.getElementById('ocrForm').addEventListener('submit', function() {
    document.getElementById('loadingOverlay').classList.remove('hidden');

    const bar  = document.getElementById('progressBar');
    const text = document.getElementById('progressText');

    const steps = [
        { pct: 10, msg: 'Mengkonversi PDF ke gambar...' },
        { pct: 25, msg: 'Membaca halaman dokumen...' },
        { pct: 45, msg: 'Menganalisis teks OCR...' },
        { pct: 65, msg: 'Mengklasifikasikan dokumen...' },
        { pct: 80, msg: 'Mengekstrak data penting...' },
        { pct: 95, msg: 'Menyimpan hasil...' },
    ];

    let i = 0;

    const interval = setInterval(function() {
        if (i < steps.length) {
            bar.style.width  = steps[i].pct + '%';
            text.textContent = steps[i].msg;
            i++;
        } else {
            clearInterval(interval);
        }
    }, 4000);
});

// Edit hasil OCR: textarea terkunci sampai tombol Edit ditekan
const ocrText = document.getElementById('ocrText');
const ocrEditBtn = document.getElementById('ocrEditBtn');

if (ocrText && ocrEditBtn) {
    const actions = document.getElementById('ocrEditActions');
    const original = ocrText.value;

    const setEditing = function (editing) {
        ocrText.readOnly = !editing;
        actions.classList.toggle('hidden', !editing);
        ocrEditBtn.classList.toggle('hidden', editing);
        if (editing) {
            ocrText.focus();
        }
    };

    ocrEditBtn.addEventListener('click', () => setEditing(true));

    document.getElementById('ocrCancelBtn').addEventListener('click', function () {
        ocrText.value = original;
        setEditing(false);
    });

    // Submit ke admin saat masih mode edit: kirim juga teks yang belum disimpan
    const submitForm = document.getElementById('submitForm');
    if (submitForm) {
        submitForm.addEventListener('submit', function () {
            if (!ocrText.readOnly) {
                const field = document.getElementById('submitOcrText');
                field.disabled = false;
                field.value = ocrText.value;
            }
        });
    }

    @if($errors->has('ocr_text'))
        setEditing(true);
    @endif
}
</script>

@endsection