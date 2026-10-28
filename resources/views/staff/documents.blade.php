@extends('layouts.staff')

@section('content')

@php
    // ====== SESUAIKAN NAMA ROUTE DI SINI KALAU BERBEDA DI routes/web.php ======
    $routeShow     = 'documents.show';      // halaman detail / hasil OCR
    $routeDownload = 'documents.download';  // download file
    $routeOcr      = 'documents.ocr';       // proses OCR (POST)
    // ===========================================================================

    $categoryLabels = [
        'invoice'   => 'Invoice',
        'loading'   => 'Loading',
        'unloading' => 'Bongkar',
        'bongkar'   => 'Bongkar',
    ];

    $statusStyle = [
        'draft'    => 'bg-gray-100 text-gray-600',
        'pending'  => 'bg-yellow-100 text-yellow-700',
        'approved' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700',
    ];
@endphp

<div class="flex items-start justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Dokumen Saya</h1>
        <p class="text-gray-500 mt-2">Upload, proses OCR, dan pantau status dokumen per kapal.</p>
    </div>
    <a href="{{ route('documents.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition">
        + Upload Dokumen
    </a>
</div>

{{-- Statistik --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
    <div class="bg-white p-5 rounded-2xl shadow">
        <p class="text-gray-500 text-sm">Total</p>
        <h2 class="text-2xl font-bold text-blue-600">{{ $total }}</h2>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow">
        <p class="text-gray-500 text-sm">Draft</p>
        <h2 class="text-2xl font-bold text-gray-500">{{ $draft }}</h2>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow">
        <p class="text-gray-500 text-sm">Pending</p>
        <h2 class="text-2xl font-bold text-yellow-500">{{ $pending }}</h2>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow">
        <p class="text-gray-500 text-sm">Approved</p>
        <h2 class="text-2xl font-bold text-green-500">{{ $approved }}</h2>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow">
        <p class="text-gray-500 text-sm">Rejected</p>
        <h2 class="text-2xl font-bold text-red-500">{{ $rejected }}</h2>
    </div>
</div>

{{-- Menunggu Proses OCR --}}
@if($waitingOcr->isNotEmpty())
    <div class="bg-white rounded-xl shadow border-l-4 border-yellow-400 p-6 mb-8">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">⏳ Menunggu Proses OCR</h2>
                <p class="text-xs text-gray-500 mt-1">
                    Setelah OCR selesai, dokumen otomatis pindah ke kartu kapalnya.
                </p>
            </div>
            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">
                {{ $waitingOcr->count() }} Dokumen
            </span>
        </div>

        <div class="mt-4 space-y-2">
            @foreach($waitingOcr as $doc)
                <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 rounded-lg bg-gray-50">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-700 truncate">{{ $doc->file_name }}</p>
                        <p class="text-xs text-gray-400">{{ $doc->created_at->format('d M Y H:i') }}</p>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                           class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-md text-xs font-semibold">Preview</a>
                        <a href="{{ route($routeDownload, $doc->id) }}"
                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md text-xs font-semibold">Download</a>
                        <form action="{{ route($routeOcr, $doc->id) }}" method="POST"
                              onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').innerText = 'Memproses...';">
                            @csrf
                            <button type="submit"
                                    class="bg-purple-600 hover:bg-purple-700 disabled:opacity-60 text-white px-3 py-1.5 rounded-md text-xs font-semibold">
                                Proses OCR
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Search + filter --}}
<div class="bg-white rounded-xl shadow p-5 mb-8 space-y-4">
    <input
        type="text"
        id="searchShip"
        placeholder="Cari nama kapal atau nama file..."
        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">

    <div class="flex flex-wrap gap-2">
        <button type="button" data-filter="all"
                class="filter-btn px-4 py-1.5 rounded-full text-sm font-medium bg-blue-600 text-white">Semua</button>
        <button type="button" data-filter="draft"
                class="filter-btn px-4 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-700">Ada draft</button>
        <button type="button" data-filter="incomplete"
                class="filter-btn px-4 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-700">Belum selesai</button>
        <button type="button" data-filter="complete"
                class="filter-btn px-4 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-700">Semua approved</button>
    </div>
</div>

{{-- Card per Kapal --}}
<div id="shipContainer" class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">

    @forelse($ships as $ship)

        <div class="ship-card bg-white rounded-xl shadow hover:shadow-lg transition p-6"
             data-complete="{{ $ship->all_approved ? '1' : '0' }}"
             data-draft="{{ $ship->draft > 0 ? '1' : '0' }}"
             data-files="{{ strtolower($ship->documents->pluck('file_name')->implode(' ')) }}">

            <div class="flex justify-between items-center mb-4">
                <div class="text-4xl">🚢</div>
                <div class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">
                    {{ $ship->total_documents }} Dokumen
                </div>
            </div>

            <h2 class="text-xl font-bold text-gray-800 ship-name">{{ $ship->vessel_name }}</h2>

            <div class="mt-1 space-y-0.5">
                @forelse($ship->voyages as $voyage)
                    <p class="text-sm text-gray-400">{{ $voyage }}</p>
                @empty
                    <p class="text-sm text-gray-400">{{ $ship->detected ? '-' : 'Voyage tidak terdeteksi' }}</p>
                @endforelse
            </div>

            @if($ship->categories->isNotEmpty())
                <p class="text-gray-500 mt-2 text-sm">{{ $ship->categories->implode(' • ') }}</p>
            @endif

            {{-- Status keseluruhan kapal --}}
            <div class="mt-4">
                @if($ship->all_approved)
                    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 rounded-lg px-3 py-2 text-sm font-semibold">
                        <span>✓</span><span>Semua dokumen sudah di-approve</span>
                    </div>
                @elseif($ship->rejected > 0)
                    <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 rounded-lg px-3 py-2 text-sm font-semibold">
                        <span>!</span><span>{{ $ship->approved }}/{{ $ship->total_documents }} approved · ada yang ditolak</span>
                    </div>
                @elseif($ship->draft > 0)
                    <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 text-gray-600 rounded-lg px-3 py-2 text-sm font-semibold">
                        <span>✎</span><span>{{ $ship->draft }} dokumen draft belum disubmit ke admin</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg px-3 py-2 text-sm font-semibold">
                        <span>⏳</span><span>{{ $ship->approved }}/{{ $ship->total_documents }} approved · menunggu verifikasi admin</span>
                    </div>
                @endif

                <div class="mt-2 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $ship->all_approved ? 'bg-green-500' : 'bg-yellow-400' }}"
                         style="width: {{ $ship->progress }}%"></div>
                </div>
            </div>

            {{-- Ringkasan status --}}
            <div class="flex flex-wrap gap-2 mt-3">
                @if($ship->pending > 0)
                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs">{{ $ship->pending }} Pending</span>
                @endif
                @if($ship->approved > 0)
                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">{{ $ship->approved }} Approved</span>
                @endif
                @if($ship->rejected > 0)
                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">{{ $ship->rejected }} Rejected</span>
                @endif
                @if($ship->draft > 0)
                    <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs">{{ $ship->draft }} Draft</span>
                @endif
            </div>

            {{-- Daftar dokumen + aksi --}}
            <div class="mt-4 pt-4 border-t space-y-2">
                @foreach($ship->documents as $doc)
                    <div class="px-3 py-3 rounded-lg bg-gray-50">

                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-700 truncate">{{ $doc->file_name }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $doc->created_at->format('d M Y H:i') }}
                                    @if($doc->category)
                                        · {{ $categoryLabels[$doc->category] ?? ucfirst($doc->category) }}
                                    @endif
                                </p>
                            </div>
                            <span class="shrink-0 px-2 py-1 rounded-full text-xs {{ $statusStyle[$doc->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($doc->status) }}
                            </span>
                        </div>

                        {{-- Alasan penolakan dari admin --}}
                        @if($doc->status === 'rejected' && $doc->admin_note)
                            <p class="mt-2 text-xs text-red-600 bg-red-50 border border-red-100 rounded-md px-2 py-1.5">
                                Catatan admin: {{ $doc->admin_note }}
                            </p>
                        @endif

                        <div class="flex flex-wrap gap-2 mt-2">
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                               class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-xs font-semibold">Preview</a>
                            <a href="{{ route($routeDownload, $doc->id) }}"
                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-xs font-semibold">Download</a>
                            <a href="{{ route($routeShow, $doc->id) }}"
                               class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-md text-xs font-semibold">
                                {{ $doc->status === 'draft' ? 'Lihat Hasil OCR & Submit' : 'Lihat Hasil OCR' }}
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>

        </div>

    @empty

        @if($waitingOcr->isEmpty())
            <div class="col-span-full bg-white rounded-xl shadow p-10 text-center">
                <div class="text-5xl mb-3">📂</div>
                <h2 class="text-xl font-semibold text-gray-700">Belum Ada Dokumen</h2>
                <p class="text-gray-500 mt-2">Upload dokumen pertama kamu untuk mulai proses OCR.</p>
            </div>
        @endif

    @endforelse

    <div id="noResult" class="hidden col-span-full bg-white rounded-xl shadow p-10 text-center">
        <div class="text-5xl mb-3">🔍</div>
        <h2 class="text-xl font-semibold text-gray-700">Kapal tidak ditemukan</h2>
        <p class="text-gray-500 mt-2">Coba kata kunci atau filter yang lain.</p>
    </div>

</div>

<script>
(function () {
    const searchInput = document.getElementById('searchShip');
    const cards       = document.querySelectorAll('.ship-card');
    const noResult    = document.getElementById('noResult');
    const buttons     = document.querySelectorAll('.filter-btn');
    let activeFilter  = 'all';

    function applyFilters() {
        const keyword = searchInput.value.toLowerCase().trim();
        let visible = 0;

        cards.forEach(function (card) {
            const name       = card.querySelector('.ship-name').innerText.toLowerCase();
            const files      = card.dataset.files || '';
            const isComplete = card.dataset.complete === '1';
            const hasDraft   = card.dataset.draft === '1';

            const matchSearch = name.includes(keyword) || files.includes(keyword);
            const matchFilter =
                activeFilter === 'all' ||
                (activeFilter === 'complete'   && isComplete) ||
                (activeFilter === 'incomplete' && !isComplete) ||
                (activeFilter === 'draft'      && hasDraft);

            const show = matchSearch && matchFilter;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (cards.length > 0) {
            noResult.classList.toggle('hidden', visible > 0);
        }
    }

    searchInput.addEventListener('input', applyFilters);

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            activeFilter = btn.dataset.filter;

            buttons.forEach(function (b) {
                const active = b === btn;
                b.classList.toggle('bg-blue-600', active);
                b.classList.toggle('text-white', active);
                b.classList.toggle('bg-gray-100', !active);
                b.classList.toggle('text-gray-700', !active);
            });

            applyFilters();
        });
    });
})();
</script>

@endsection