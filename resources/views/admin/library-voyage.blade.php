@extends('layouts.admin')

@section('content')

@php
    $statusBadge = [
        'approved' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700',
        'pending'  => 'bg-yellow-100 text-yellow-700',
    ];
@endphp

<div class="mb-8">
    <a href="{{ route('admin.library.vessel', $vesselKey) }}" class="text-sm text-blue-600 hover:underline">
        ← Kembali ke {{ $vesselName }}
    </a>
    <h1 class="text-3xl font-bold text-gray-800 mt-3 break-words">📁 {{ $voyageName }}</h1>
    <p class="text-gray-500 mt-2">{{ $vesselName }}</p>
</div>

<div class="space-y-6">

    @foreach($sections as $section)

        <div class="bg-white rounded-xl shadow p-6">

            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">{{ $section->label }}</h2>
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">
                    {{ $section->documents->count() }} Dokumen
                </span>
            </div>

            @forelse($section->documents as $doc)

                <div class="flex flex-wrap justify-between items-center gap-3 bg-gray-50 rounded-lg px-4 py-3 mb-2">

                    <div class="min-w-0">
                        <p class="font-medium text-gray-800 truncate">{{ $doc->file_name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $doc->user->name ?? '-' }} · {{ $doc->created_at->format('d M Y H:i') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusBadge[$doc->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($doc->status) }}
                        </span>

                        <a href="{{ route('admin.documents.show', $doc->id) }}"
                           class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-3 py-1.5 rounded-lg">
                            Detail
                        </a>

                        <a href="{{ route('documents.download', $doc->id) }}"
                           class="bg-gray-700 hover:bg-gray-800 text-white text-sm px-3 py-1.5 rounded-lg">
                            Download
                        </a>
                    </div>

                </div>

            @empty

                <p class="text-gray-400 text-sm py-3">Belum ada {{ strtolower($section->label) }} untuk voyage ini.</p>

            @endforelse

        </div>

    @endforeach

    @if($uncategorized->isNotEmpty())

        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-gray-300">

            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Lainnya</h2>
                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm font-semibold">
                    {{ $uncategorized->count() }} Dokumen
                </span>
            </div>

            @foreach($uncategorized as $doc)

                <div class="flex flex-wrap justify-between items-center gap-3 bg-gray-50 rounded-lg px-4 py-3 mb-2">

                    <div class="min-w-0">
                        <p class="font-medium text-gray-800 truncate">{{ $doc->file_name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $doc->user->name ?? '-' }} · {{ $doc->created_at->format('d M Y H:i') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusBadge[$doc->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($doc->status) }}
                        </span>

                        <a href="{{ route('admin.documents.show', $doc->id) }}"
                           class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-3 py-1.5 rounded-lg">
                            Detail
                        </a>
                    </div>

                </div>

            @endforeach

        </div>

    @endif

</div>

@endsection