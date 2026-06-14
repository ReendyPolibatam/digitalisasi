@extends('layouts.staff')

@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">
        Detail Dokumen
    </h1>
    <p class="text-gray-500 mt-2">
        Upload, OCR, dan klasifikasi dokumen shipping
    </p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- PDF Preview --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow">
            <div class="border-b px-6 py-4">
                <h2 class="font-semibold text-gray-700">
                    Preview Dokumen
                </h2>
            </div>

            <div class="p-4">
                @php
                    $extension = pathinfo($document->file_name, PATHINFO_EXTENSION);
                @endphp

                @if(strtolower($extension) == 'pdf')
                    <iframe
                        src="{{ asset('storage/' . $document->file_path) }}"
                        class="w-full h-[700px] rounded-lg border">
                    </iframe>
                @else
                    <img
                        src="{{ asset('storage/' . $document->file_path) }}"
                        class="w-full rounded-lg border">
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        <div class="bg-white rounded-xl shadow">
            <div class="border-b px-6 py-4">
                <h2 class="font-semibold text-gray-700">
                    Informasi Dokumen
                </h2>
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
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                                Approved
                            </span>
                        @elseif($document->status == 'rejected')
                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">
                                Rejected
                            </span>
                        @elseif($document->status == 'pending')
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">
                                Menunggu Verifikasi
                            </span>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">
                                Draft
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Tanggal Upload</label>
                    <p>{{ $document->created_at->format('d M Y H:i') }}</p>
                </div>

                <hr>

                <div class="space-y-3">

                    <form action="{{ route('documents.ocr', $document->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-lg">
                            Proses OCR
                        </button>
                    </form>

                    @if($document->ocr_text)

                        @if($document->status == 'draft')

                            <form action="{{ route('documents.submit', $document->id) }}" method="POST">
                                @csrf
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
        <div class="border-b px-6 py-4">
            <h2 class="font-semibold text-gray-700">
                Hasil OCR
            </h2>
        </div>

        <div class="p-6">
            @if($document->ocr_text)
                <textarea
                    class="w-full border rounded-lg p-4 h-96"
                    readonly>{{ $document->ocr_text }}</textarea>
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

            <h2 class="font-semibold text-gray-700">
                Hasil Klasifikasi
            </h2>

            @if($document->category)
                @php
                    $categoryLabels = [
                        'invoice'   => ['label' => 'Invoice', 'color' => 'bg-blue-100 text-blue-700'],
                        'loading'   => ['label' => 'Dokumen Loading', 'color' => 'bg-green-100 text-green-700'],
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
                    <p class="font-medium text-gray-800 mt-1">
                        {{ $document->vessel_name ?? '-' }}
                    </p>
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

@endsection