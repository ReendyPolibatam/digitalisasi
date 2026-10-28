@extends('layouts.admin')

@section('content')

<div class="mb-8">
    <a href="{{ route('admin.library') }}" class="text-sm text-blue-600 hover:underline">
        ← Kembali ke Library
    </a>
    <h1 class="text-3xl font-bold text-gray-800 mt-3">🚢 {{ $vesselName }}</h1>
    <p class="text-gray-500 mt-2">Pilih voyage untuk melihat Invoice, Dokumen Bongkar, dan Dokumen Loading.</p>
</div>

<div class="bg-white rounded-xl shadow p-5 mb-8">
    <input
        type="text"
        id="searchVoyage"
        placeholder="Cari nomor voyage..."
        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

    @forelse($voyages as $v)

        <a href="{{ route('admin.library.voyage', [$vesselKey, $v->key]) }}"
           class="voyage-card block bg-white rounded-xl shadow hover:shadow-lg hover:ring-2 hover:ring-blue-200 transition p-6">

            <div class="flex justify-between items-center mb-4">
                <div class="text-4xl">📁</div>
                <div class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">
                    {{ $v->total_documents }} Dokumen
                </div>
            </div>

            <h2 class="text-lg font-bold text-gray-800 voyage-name break-words">
                @if($v->key === 'NOVOYAGE')
                    {{ $v->voyage }}
                @else
                    Voyage {{ $v->voyage }}
                @endif
            </h2>

            @if($v->categories->isNotEmpty())
                <div class="flex flex-wrap gap-2 mt-3">
                    @foreach($v->categories as $cat)
                        <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs">{{ $cat }}</span>
                    @endforeach
                </div>
            @endif

            @if($v->pending > 0)
                <p class="text-xs text-yellow-600 mt-3">{{ $v->pending }} menunggu verifikasi</p>
            @endif

            <p class="text-sm text-blue-600 mt-4 pt-4 border-t">
                Buka folder →
            </p>

        </a>

    @empty

        <div class="col-span-full bg-white rounded-xl shadow p-10 text-center">
            <div class="text-5xl mb-3">📂</div>
            <h2 class="text-xl font-semibold text-gray-700">Belum Ada Voyage</h2>
            <p class="text-gray-500 mt-2">Belum ada dokumen untuk kapal ini.</p>
        </div>

    @endforelse

    <p id="noResult" class="hidden col-span-full text-center text-gray-500 py-6">
        Voyage tidak ditemukan.
    </p>

</div>

<script>
    const searchInput = document.getElementById('searchVoyage');
    const cards       = document.querySelectorAll('.voyage-card');
    const noResult    = document.getElementById('noResult');

    searchInput.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();
        let visible = 0;

        cards.forEach(function (card) {
            const name  = card.querySelector('.voyage-name').innerText.toLowerCase();
            const match = name.includes(keyword);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        noResult.classList.toggle('hidden', visible !== 0 || cards.length === 0);
    });
</script>

@endsection