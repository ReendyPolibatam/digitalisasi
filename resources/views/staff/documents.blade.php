@extends('layouts.app')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Daftar Dokumen
            </h2>

            <p class="text-gray-500 text-sm">
                Kelola dan lihat status dokumen anda
            </p>
        </div>

        <a href="{{ route('documents.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg transition">
            + Upload Dokumen
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 p-4 rounded-lg mb-5">
            {{ session('success') }}
        </div>
    @endif

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200 rounded-lg overflow-hidden">

            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="p-4 text-left border-b">
                        Nama File
                    </th>

                    <th class="p-4 text-left border-b">
                        Status
                    </th>

                    <th class="p-4 text-left border-b">
                        Upload
                    </th>

                    <th class="p-4 text-center border-b">
                        Aksi
                    </th>
                </tr>
            </thead>

            <tbody>

                @forelse($documents as $doc)

                <tr class="hover:bg-gray-50 transition">

                    <!-- File Name -->
                    <td class="p-4 border-b">
                        {{ $doc->file_name }}
                    </td>

                    <!-- Status Badge -->
                    <td class="p-4 border-b">

                        @if($doc->status == 'pending')

                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm">
                                Pending
                            </span>

                        @elseif($doc->status == 'approved')

                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                                Approved
                            </span>

                        @else

                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm">
                                Rejected
                            </span>

                        @endif

                    </td>

                    <!-- Upload Time -->
                    <td class="p-4 border-b text-gray-500">
                        {{ $doc->created_at->format('d M Y H:i') }}
                    </td>

                    <!-- Actions -->
                    <td class="p-4 border-b">

                        <div class="flex justify-center gap-2">

                            <!-- Preview -->
                            <a href="{{ asset('storage/' . $doc->file_path) }}"
                               target="_blank"
                               class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg transition">
                                Preview
                            </a>

                            <!-- Download -->
                            <a href="{{ route('documents.download', $doc->id) }}"
                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg transition">
                                Download
                            </a>

                        </div>

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="4" class="text-center p-6 text-gray-500">
                        Belum ada dokumen diupload.
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>
    </div>

</div>

@endsection