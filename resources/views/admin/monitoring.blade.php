@extends('layouts.admin')

@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Monitoring Dokumen</h1>
    <p class="text-gray-500 mt-2">Pantau seluruh dokumen yang masuk ke sistem.</p>
</div>

{{-- Statistik --}}
<div class="grid md:grid-cols-4 gap-6 mb-8">

    <div class="bg-white p-6 rounded-2xl shadow">
        <p class="text-gray-500">Total Dokumen</p>
        <h2 class="text-3xl font-bold text-blue-600">{{ $total }}</h2>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow">
        <p class="text-gray-500">Pending</p>
        <h2 class="text-3xl font-bold text-yellow-500">{{ $pending }}</h2>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow">
        <p class="text-gray-500">Approved</p>
        <h2 class="text-3xl font-bold text-green-500">{{ $approved }}</h2>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow">
        <p class="text-gray-500">Rejected</p>
        <h2 class="text-3xl font-bold text-red-500">{{ $rejected }}</h2>
    </div>

</div>

{{-- Search + filter --}}
<div class="bg-white rounded-xl shadow p-5 mb-8 space-y-4">
    <input
        type="text"
        id="searchShip"
        placeholder="Cari nama kapal..."
        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">

    <div class="flex flex-wrap gap-2" id="filterButtons">
        <button type="button" data-filter="all"
                class="filter-btn px-4 py-1.5 rounded-full text-sm font-medium bg-blue-600 text-white">
            Semua
        </button>
        <button type="button" data-filter="incomplete"
                class="filter-btn px-4 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
            Belum selesai
        </button>
        <button type="button" data-filter="complete"
                class="filter-btn px-4 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
            Semua approved
        </button>
    </div>
</div>

{{-- Card per Kapal --}}
<div id="shipContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 items-start">

    @forelse($ships as $ship)

        <div class="ship-card bg-white rounded-xl shadow hover:shadow-lg transition p-6"
             data-complete="{{ $ship->all_approved ? '1' : '0' }}">

            <div class="flex justify-between items-center mb-4">
                <div class="text-4xl">🚢</div>
                <div class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">
                    {{ $ship->total_documents }} Dokumen
                </div>
            </div>

            <h2 class="text-xl font-bold text-gray-800 ship-name">
                {{ $ship->vessel_name }}
            </h2>

            {{-- Voyage: bisa lebih dari satu per kapal --}}
            <div class="mt-1 space-y-0.5">
                @forelse($ship->voyages as $voyage)
                    <p class="text-sm text-gray-400">{{ $voyage }}</p>
                @empty
                    <p class="text-sm text-gray-400">-</p>
                @endforelse
            </div>

            <p class="text-gray-500 mt-2 text-sm">
                @if($ship->categories->isNotEmpty())
                    {{ $ship->categories->implode(' • ') }}
                @else
                    <span class="italic">Belum ada kategori</span>
                @endif
            </p>

            {{-- Status keseluruhan kapal --}}
            <div class="mt-4">
                @if($ship->all_approved)
                    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 rounded-lg px-3 py-2 text-sm font-semibold">
                        <span>✓</span>
                        <span>Semua dokumen sudah di-approve</span>
                    </div>
                @elseif($ship->rejected > 0)
                    <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 rounded-lg px-3 py-2 text-sm font-semibold">
                        <span>!</span>
                        <span>{{ $ship->approved }}/{{ $ship->total_documents }} approved · ada yang ditolak</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg px-3 py-2 text-sm font-semibold">
                        <span>⏳</span>
                        <span>{{ $ship->approved }}/{{ $ship->total_documents }} approved · menunggu verifikasi</span>
                    </div>
                @endif

                {{-- Progress bar --}}
                <div class="mt-2 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $ship->all_approved ? 'bg-green-500' : 'bg-yellow-400' }}"
                         style="width: {{ $ship->progress }}%"></div>
                </div>
            </div>

            {{-- Status summary --}}
            <div class="flex flex-wrap gap-2 mt-3">
                @if($ship->pending > 0)
                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs">
                        {{ $ship->pending }} Pending
                    </span>
                @endif
                @if($ship->approved > 0)
                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">
                        {{ $ship->approved }} Approved
                    </span>
                @endif
                @if($ship->rejected > 0)
                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">
                        {{ $ship->rejected }} Rejected
                    </span>
                @endif
            </div>

            {{-- Daftar dokumen --}}
            <div class="mt-4 pt-4 border-t space-y-2">
                @foreach($ship->documents as $doc)
                    <a href="{{ route('admin.documents.show', $doc->id) }}"
                       class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg bg-gray-50 hover:bg-blue-50 transition">

                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-700 truncate">{{ $doc->file_name }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $doc->uploader }} · {{ $doc->created_at->format('d M Y H:i') }}
                            </p>
                        </div>

                        @if($doc->status == 'pending')
                            <span class="shrink-0 bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs">Pending</span>
                        @elseif($doc->status == 'approved')
                            <span class="shrink-0 bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">Approved</span>
                        @else
                            <span class="shrink-0 bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">Rejected</span>
                        @endif

                    </a>
                @endforeach
            </div>

        </div>

    @empty

        <div class="col-span-full bg-white rounded-xl shadow p-10 text-center">
            <div class="text-5xl mb-3">📂</div>
            <h2 class="text-xl font-semibold text-gray-700">Belum Ada Data</h2>
            <p class="text-gray-500 mt-2">Dokumen yang sudah disubmit akan muncul di sini.</p>
        </div>

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
            const isComplete = card.dataset.complete === '1';

            const matchSearch = name.includes(keyword);
            const matchFilter =
                activeFilter === 'all' ||
                (activeFilter === 'complete' && isComplete) ||
                (activeFilter === 'incomplete' && !isComplete);

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