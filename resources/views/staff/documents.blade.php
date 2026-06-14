@extends('layouts.app')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between items-center mb-6">

        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Dokumen Saya
            </h2>

            <p class="text-gray-500 text-sm">
                Upload, proses OCR, dan pantau status dokumen
            </p>
        </div>

        <a href="{{ route('documents.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">
            + Upload Dokumen
        </a>

    </div>

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

    <div class="overflow-x-auto">

        <table class="w-full border border-gray-200">

            <thead class="bg-gray-100">

                <tr>

                    <th class="p-4 text-left">
                        Nama File
                    </th>

                    <th class="p-4 text-left">
                        Status Verifikasi
                    </th>

                    <th class="p-4 text-left">
                        Status OCR
                    </th>

                    <th class="p-4 text-left">
                        Upload
                    </th>

                    <th class="p-4 text-center">
                        Aksi
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($documents as $doc)

                <tr class="border-t hover:bg-gray-50">

                    <td class="p-4">

                        <div class="font-medium text-gray-800">
                            {{ $doc->file_name }}
                        </div>

                        @if($doc->ocr_text)
                            <div class="text-xs text-green-600 mt-1">
                                OCR berhasil diproses
                            </div>
                        @endif

                    </td>

                    <td class="p-4">

                        @if($doc->status == 'approved')

                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                                Approved
                            </span>

                        @elseif($doc->status == 'rejected')

                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm">
                                Rejected
                            </span>

                        @else

                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm">
                                Pending
                            </span>

                        @endif

                    </td>

                    <td class="p-4">

                        @if($doc->ocr_text)

                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                                OCR Selesai
                            </span>

                        @else

                            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm">
                                Belum Diproses
                            </span>

                        @endif

                    </td>

                    <td class="p-4 text-gray-500">
                        {{ $doc->created_at->format('d M Y H:i') }}
                    </td>

                    <td class="p-4">

                        <div class="flex flex-wrap justify-center gap-2">

                            <a href="{{ route('documents.show', $doc->id) }}"
                               class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg">
                                Preview
                            </a>

                            <a href="{{ route('documents.download',$doc->id) }}"
                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg">
                                Download
                            </a>

                            @if(!$doc->ocr_text)

                                <form action="{{ route('documents.ocr',$doc->id) }}"
                                      method="POST">

                                    @csrf

                                    <button type="submit"
                                            class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg">
                                        Proses OCR
                                    </button>

                                </form>

                            @else

                                <button
                                    onclick="document.getElementById('ocrModal{{ $doc->id }}').classList.remove('hidden')"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg">
                                    Lihat Hasil OCR
                                </button>

                            @endif

                        </div>

                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="5"
                        class="text-center p-6 text-gray-500">

                        Belum ada dokumen.

                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-6">
        {{ $documents->links() }}
    </div>

</div>

@foreach($documents as $doc)

    @if($doc->ocr_text)

    <div id="ocrModal{{ $doc->id }}"
         class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

        <div class="bg-white rounded-xl shadow-xl w-11/12 max-w-5xl p-6">

            <div class="flex justify-between items-center mb-4">

                <h3 class="text-xl font-bold">
                    Hasil OCR - {{ $doc->file_name }}
                </h3>

                <button
                    onclick="document.getElementById('ocrModal{{ $doc->id }}').classList.add('hidden')"
                    class="text-gray-500 hover:text-gray-700 text-2xl">
                    ×
                </button>

            </div>

            <div class="bg-gray-100 rounded-lg p-4 max-h-[600px] overflow-y-auto">

                <pre class="whitespace-pre-wrap text-sm text-gray-800">{{ $doc->ocr_text }}</pre>

            </div>

        </div>

    </div>

    @endif

@endforeach

@endsection