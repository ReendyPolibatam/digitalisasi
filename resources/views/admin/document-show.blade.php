@extends('layouts.admin')

@section('content')

<div class="mb-8">

    <h1 class="text-3xl font-bold text-gray-800">
        Detail Verifikasi Dokumen
    </h1>

    <p class="text-gray-500 mt-2">
        Tinjau dokumen sebelum disetujui
    </p>

</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Preview -->
    <div class="bg-white rounded-xl shadow p-6">

        <h2 class="font-bold text-lg mb-4">

            Preview Dokumen

        </h2>

        <div class="border rounded-lg h-[450px] flex flex-col items-center justify-center bg-gray-50">

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-20 h-20 text-gray-400 mb-4"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">

                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M7 3h8l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"/>

            </svg>

            <p class="font-medium">

                {{ $document->file_name }}

            </p>

            <a href="{{ route('documents.download',$document->id) }}"
               class="mt-5 bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg">

                Download Dokumen

            </a>

        </div>

    </div>

    <!-- OCR -->
    <div class="bg-white rounded-xl shadow p-6">

        <h2 class="font-bold text-lg mb-4">

            Data Detail

        </h2>

        <div class="grid grid-cols-2 gap-y-5">

            <div>

                <p class="text-gray-400 text-sm">
                    Nama File
                </p>

                <p class="font-medium">
                    {{ $document->file_name }}
                </p>

            </div>

            <div>

                <p class="text-gray-400 text-sm">
                    Status
                </p>

                <p class="font-medium">
                    {{ ucfirst($document->status) }}
                </p>

            </div>

            <div>

                <p class="text-gray-400 text-sm">
                    Upload Oleh
                </p>

                <p class="font-medium">
                    {{ $document->user->name ?? '-' }}
                </p>

            </div>

            <div>

                <p class="text-gray-400 text-sm">
                    Tanggal Upload
                </p>

                <p class="font-medium">
                    {{ $document->created_at->format('d M Y') }}
                </p>

            </div>

            <div>

                <p class="text-gray-400 text-sm">
                    Kategori
                </p>

                <p class="font-medium">
                    Belum Diklasifikasi
                </p>

            </div>

            <div>

                <p class="text-gray-400 text-sm">
                    Voyage
                </p>

                <p class="font-medium">
                    -
                </p>

            </div>

            <div class="col-span-2">

                <p class="text-gray-400 text-sm">
                    Hasil OCR
                </p>

                <div class="border rounded-lg p-4 bg-gray-50 mt-2 h-48 overflow-y-auto">

                    OCR akan tampil disini setelah integrasi OCR

                </div>

            </div>

        </div>

    </div>

</div>

@if($document->status == 'pending')

<div class="bg-white rounded-xl shadow p-6 mt-6">

    <div class="flex justify-center gap-4">

        <a href="{{ route('documents.approve',$document->id) }}"
           class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg">

            ✓ Approve

        </a>

        <a href="{{ route('documents.reject',$document->id) }}"
           class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg">

            ✕ Reject

        </a>

    </div>

</div>

@endif

@endsection