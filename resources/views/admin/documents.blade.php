@extends('layouts.app')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">

        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Verifikasi Dokumen
            </h2>

            <p class="text-gray-500 text-sm">
                Approve atau reject dokumen yang diupload staff
            </p>
        </div>

    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 p-4 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
        <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded-lg mb-4">
            {{ session('error') }}
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
                        Preview
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

                    <!-- Status -->
                    <td class="p-4 border-b">

                        @if($doc->status == 'pending')

                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm">
                                Pending
                            </span>

                        @elseif($doc->status == 'approved')

                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                                Approved
                            </span>

                        @elseif($doc->status == 'rejected')

                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm">
                                Rejected
                            </span>

                        @endif

                    </td>

                    <!-- Upload Time -->
                    <td class="p-4 border-b text-gray-500">
                        {{ $doc->created_at->format('d M Y H:i') }}
                    </td>

                    <!-- Preview -->
                    <td class="p-4 border-b text-center">

                        <a href="{{ asset('storage/' . $doc->file_path) }}"
                           target="_blank"
                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg transition">
                            Preview
                        </a>

                    </td>

                    <!-- Actions -->
                    <td class="p-4 border-b">

                        @if($doc->status == 'pending')

                            <div class="flex justify-center gap-2">

                                <!-- Approve -->
                                <a href="{{ route('documents.approve', $doc->id) }}"
                                   onclick="return confirm('Approve dokumen ini?')"
                                   class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg transition">

                                    Approve
                                </a>

                                <!-- Reject -->
                                <a href="{{ route('documents.reject', $doc->id) }}"
                                   onclick="return confirm('Reject dokumen ini?')"
                                   class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg transition">

                                    Reject
                                </a>

                            </div>

                        @else

                            <div class="text-center text-gray-400 text-sm">
                                Tidak ada aksi
                            </div>

                        @endif

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="5" class="text-center p-6 text-gray-500">
                        Belum ada dokumen
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection