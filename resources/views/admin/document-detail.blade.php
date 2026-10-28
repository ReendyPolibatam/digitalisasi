@extends('layouts.admin')

@section('content')

@php
    $categoryLabels = [
        'invoice'   => ['label' => 'Invoice',          'color' => 'bg-blue-100 text-blue-700'],
        'loading'   => ['label' => 'Dokumen Loading',  'color' => 'bg-green-100 text-green-700'],
        'unloading' => ['label' => 'Dokumen Bongkar',  'color' => 'bg-orange-100 text-orange-700'],
        'bongkar'   => ['label' => 'Dokumen Bongkar',  'color' => 'bg-orange-100 text-orange-700'],
    ];

    // Tampilkan teks hasil editan staff kalau ada (itu yang dianalisis saat submit)
    $finalOcrText = $document->ocr_text_edited ?: $document->ocr_text;
@endphp

@if(session('success'))
    <div class="bg-green-100 border border-green-300 text-green-700 p-4 rounded-lg mb-5">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded-lg mb-5">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded-lg mb-5">
        {{ $errors->first() }}
    </div>
@endif

<div class="mb-6">

    <h1 class="text-3xl font-bold text-gray-800">
        Detail Dokumen
    </h1>

    <p class="text-gray-500 mt-2">
        Informasi lengkap dokumen yang diupload staff
    </p>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Preview PDF --}}
    <div class="lg:col-span-2">

        <div class="bg-white rounded-xl shadow p-4">

            <h2 class="text-lg font-semibold mb-4">
                Preview Dokumen
            </h2>

            <iframe
                src="{{ asset('storage/' . $document->file_path) }}"
                class="w-full h-[850px] border rounded-lg">
            </iframe>

        </div>

    </div>

    {{-- Informasi Dokumen --}}
    <div>

        <div class="bg-white rounded-xl shadow p-6">

            <h2 class="text-lg font-semibold mb-5">
                Informasi Dokumen
            </h2>

            <div class="space-y-4">

                <div>
                    <p class="text-sm text-gray-500">
                        Nama File
                    </p>

                    <p class="font-medium break-all">
                        {{ $document->file_name }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">
                        Status
                    </p>

                    @if($document->status == 'approved')

                        <span class="inline-flex px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-medium">
                            Approved
                        </span>

                    @elseif($document->status == 'rejected')

                        <span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-700 text-sm font-medium">
                            Rejected
                        </span>

                    @elseif($document->status == 'draft')

                        <span class="inline-flex px-3 py-1 rounded-full bg-gray-200 text-gray-700 text-sm font-medium">
                            Draft
                        </span>

                    @else

                        <span class="inline-flex px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-sm font-medium">
                            Pending
                        </span>

                    @endif

                </div>

                @if($document->status == 'rejected' && $document->admin_note)
                    <div>
                        <p class="text-sm text-gray-500">
                            Alasan Reject
                        </p>

                        <p class="font-medium text-red-700 break-words">
                            {{ $document->admin_note }}
                        </p>
                    </div>
                @endif

                <div>
                    <p class="text-sm text-gray-500">
                        Upload Oleh
                    </p>

                    <p class="font-medium">
                        {{ $document->user->name ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">
                        Tanggal Upload
                    </p>

                    <p class="font-medium">
                        {{ $document->created_at->format('d M Y H:i') }}
                    </p>
                </div>

            </div>

            <hr class="my-6">

            <div class="space-y-3">

                <a
                    href="{{ route('documents.download', $document->id) }}"
                    class="w-full inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium">

                    Download Dokumen

                </a>

                @if($document->status == 'pending')

                    {{-- Approve: harus POST, jadi pakai form (link biasa mengirim GET → 405) --}}
                    <form action="{{ route('documents.approve', $document->id) }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            onclick="return confirm('Approve dokumen ini?')"
                            class="w-full inline-flex justify-center bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium">

                            Approve

                        </button>
                    </form>

                    {{-- Reject: ikut kirim alasan (admin_note) ke controller --}}
                    <form action="{{ route('documents.reject', $document->id) }}" method="POST" class="space-y-2">
                        @csrf
                        <textarea
                            name="admin_note"
                            rows="2"
                            maxlength="500"
                            placeholder="Alasan reject (opsional)"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">{{ old('admin_note') }}</textarea>

                        <button
                            type="submit"
                            onclick="return confirm('Reject dokumen ini?')"
                            class="w-full inline-flex justify-center bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-medium">

                            Reject

                        </button>
                    </form>

                @endif

                <a
                    href="{{ route('admin.documents') }}"
                    class="w-full inline-flex justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-3 rounded-lg font-medium">

                    Kembali

                </a>

            </div>

        </div>

    </div>

</div>

{{-- OCR Result --}}
<div class="bg-white rounded-xl shadow p-6 mt-6">

    <h2 class="text-lg font-semibold mb-4">
        Hasil OCR
    </h2>

    @if($finalOcrText)

        <div class="bg-gray-50 border rounded-lg p-4">

            <pre class="whitespace-pre-wrap text-sm text-gray-700">{{ $finalOcrText }}</pre>

        </div>

    @else

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">

            <p class="text-yellow-700">
                OCR belum diproses.
            </p>

        </div>

    @endif

</div>

{{-- Hasil Klasifikasi --}}
<div class="bg-white rounded-xl shadow p-6 mt-6">

    <div class="flex items-center justify-between mb-5">

        <h2 class="text-lg font-semibold">
            Hasil Klasifikasi
        </h2>

        @if($document->category)
            @php
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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-500">Nama Kapal</p>
            <p class="font-medium text-gray-800 mt-1">
                {{ $document->vessel_name ?? '-' }}
            </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-500">Voyage</p>
            <p class="font-medium text-gray-800 mt-1 break-all">
                {{ $document->voyage ?: '-' }}
            </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-500">Tanggal Loading</p>
            <p class="font-medium text-gray-800 mt-1">
                {{ $document->loading_date ? $document->loading_date->format('d M Y') : '-' }}
            </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-500">Tanggal Bongkar</p>
            <p class="font-medium text-gray-800 mt-1">
                {{ $document->discharge_date ? $document->discharge_date->format('d M Y') : '-' }}
            </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-500">BL Liters Observed</p>
            <p class="font-medium text-gray-800 mt-1">
                {{ $document->bl_liters_obs ? number_format($document->bl_liters_obs, 0, ',', '.') . ' L' : '-' }}
            </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-500">Liters @ 15°C</p>
            <p class="font-medium text-gray-800 mt-1">
                {{ $document->liters_15c ? number_format($document->liters_15c, 0, ',', '.') . ' L' : '-' }}
            </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-500">Tingkat Keyakinan</p>
            <p class="font-medium text-gray-800 mt-1">
                {{ $document->confidence_score ? number_format($document->confidence_score * 100, 0) . '%' : '-' }}
            </p>
        </div>

    </div>

</div>

@endsection