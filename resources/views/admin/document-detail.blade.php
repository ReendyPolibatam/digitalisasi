@extends('layouts.admin')

@section('content')

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

                    @else

                        <span class="inline-flex px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-sm font-medium">
                            Pending
                        </span>

                    @endif

                </div>

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

                    <a
                        href="{{ route('documents.approve', $document->id) }}"
                        class="w-full inline-flex justify-center bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium">

                        Approve

                    </a>

                    <a
                        href="{{ route('documents.reject', $document->id) }}"
                        class="w-full inline-flex justify-center bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-medium">

                        Reject

                    </a>

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

    @if($document->ocr_text)

        <div class="bg-gray-50 border rounded-lg p-4">

            <pre class="whitespace-pre-wrap text-sm text-gray-700">{{ $document->ocr_text }}</pre>

        </div>

    @else

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">

            <p class="text-yellow-700">
                OCR belum diproses.
            </p>

        </div>

    @endif

</div>

@endsection