@extends('layouts.app')

@section('content')

<div>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            Monitoring Dokumen
        </h1>

        <p class="text-gray-500 mt-2">
            Pantau seluruh dokumen yang masuk ke sistem.
        </p>
    </div>

    <!-- Statistik -->
    <div class="grid md:grid-cols-4 gap-6 mb-8">

        <div class="bg-white p-6 rounded-2xl shadow">
            <p class="text-gray-500">Total Dokumen</p>
            <h2 class="text-3xl font-bold text-blue-600">
                {{ $documents->count() }}
            </h2>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow">
            <p class="text-gray-500">Pending</p>
            <h2 class="text-3xl font-bold text-yellow-500">
                {{ $documents->where('status','pending')->count() }}
            </h2>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow">
            <p class="text-gray-500">Approved</p>
            <h2 class="text-3xl font-bold text-green-500">
                {{ $documents->where('status','approved')->count() }}
            </h2>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow">
            <p class="text-gray-500">Rejected</p>
            <h2 class="text-3xl font-bold text-red-500">
                {{ $documents->where('status','rejected')->count() }}
            </h2>
        </div>

    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow overflow-hidden">

        <div class="p-6 border-b">
            <h2 class="text-xl font-bold">
                Daftar Dokumen
            </h2>
        </div>

        <table class="w-full">

            <thead class="bg-gray-100">
                <tr>
                    <th class="p-4 text-left">Nama File</th>
                    <th class="p-4 text-left">Uploader</th>
                    <th class="p-4 text-left">Status</th>
                    <th class="p-4 text-left">Tanggal Upload</th>
                </tr>
            </thead>

            <tbody>

                @forelse($documents as $doc)

                <tr class="border-b hover:bg-gray-50">

                    <td class="p-4">
                        {{ $doc->file_name }}
                    </td>

                    <td class="p-4">
                        {{ $doc->user->name ?? '-' }}
                    </td>

                    <td class="p-4">

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

                    <td class="p-4">
                        {{ $doc->created_at->format('d M Y H:i') }}
                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="4" class="text-center py-8 text-gray-500">
                        Belum ada dokumen.
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection