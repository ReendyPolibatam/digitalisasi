@extends('layouts.admin')

@section('content')

<div class="mb-8">

    <h1 class="text-3xl font-bold text-gray-800">
        Verifikasi Dokumen
    </h1>

    <p class="text-gray-500 mt-2">
        Kelola dan verifikasi dokumen yang diupload staff
    </p>

</div>

<div class="bg-white rounded-xl shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-50">

            <tr>

                <th class="text-left px-6 py-4">
                    Nama File
                </th>

                <th class="text-left px-6 py-4">
                    Status
                </th>

                <th class="text-left px-6 py-4">
                    Tanggal Upload
                </th>

                <th class="text-center px-6 py-4">
                    Aksi
                </th>

            </tr>

        </thead>

        <tbody>

        @forelse($documents as $doc)

            <tr class="border-t hover:bg-gray-50">

                <td class="px-6 py-4">

                    {{ $doc->file_name }}

                </td>

                <td class="px-6 py-4">

                    @if($doc->status == 'approved')

                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
                            Approved
                        </span>

                    @elseif($doc->status == 'rejected')

                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-semibold">
                            Rejected
                        </span>

                    @else

                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-semibold">
                            Pending
                        </span>

                    @endif

                </td>

                <td class="px-6 py-4">

                    {{ $doc->created_at->format('d M Y') }}

                </td>

                <td class="px-6 py-4">

                    <div class="flex justify-center items-center gap-2 flex-wrap">

                        <a href="{{ route('admin.documents.show', $doc->id) }}"
                           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                            Detail
                        </a>

                        @if($doc->status == 'pending')

                            <a href="{{ route('documents.approve', $doc->id) }}"
                               class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                                Approve
                            </a>

                            <a href="{{ route('documents.reject', $doc->id) }}"
                               class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                                Reject
                            </a>

                        @else

                            <span class="text-gray-400 text-sm">
                                Sudah diproses
                            </span>

                        @endif

                    </div>

                </td>

            </tr>

        @empty

            <tr>

                <td colspan="4"
                    class="text-center py-10 text-gray-500">

                    Belum ada dokumen

                </td>

            </tr>

        @endforelse

        </tbody>

    </table>

</div>

<div class="mt-6">

    {{ $documents->links() }}

</div>

@endsection