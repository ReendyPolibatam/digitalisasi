@extends('layouts.app')

@section('content')

<div class="bg-white p-6 rounded-lg shadow">

    <div class="flex justify-between mb-6">
        <h2 class="text-2xl font-bold">
            Daftar Dokumen
        </h2>

        <a href="{{ route('documents.create') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded">
            Upload Dokumen
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full border">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-3 border">Nama File</th>
                <th class="p-3 border">Status</th>
                <th class="p-3 border">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($documents as $doc)
            <tr>
                <td class="p-3 border">
                    {{ $doc->file_name }}
                </td>

                <td class="p-3 border">
                    {{ $doc->status }}
                </td>

                <td class="p-3 border flex gap-2">

                    <a href="{{ asset('storage/' . $doc->file_path) }}"
                       target="_blank"
                       class="bg-green-500 text-white px-3 py-1 rounded">
                        Preview
                    </a>

                    <a href="{{ route('documents.download', $doc->id) }}"
                       class="bg-blue-500 text-white px-3 py-1 rounded">
                        Download
                    </a>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection