@extends('layouts.admin')

@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Library Dokumen</h1>
    <p class="text-gray-500 mt-2">Kumpulan dokumen shipping berdasarkan kapal.</p>
</div>

<div class="bg-white rounded-xl shadow p-5 mb-8">
    <input
        type="text"
        id="searchShip"
        placeholder="Cari nama kapal..."
        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
</div>

<div id="shipContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

    @forelse($ships as $ship)

        <a href="{{ route('admin.library.vessel', $ship->key) }}"
           class="ship-card block bg-white rounded-xl shadow hover:shadow-lg hover:ring-2 hover:ring-blue-200 transition p-6">

            <div class="flex justify-between items-center mb-4">

                <div class="text-4xl">🚢</div>

                <div class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">
                    {{ $ship->total_voyages }} Folder
                </div>

            </div>

            <h2 class="text-xl font-bold text-gray-800 ship-name">
                {{ $ship->vessel_name }}
            </h2>

            <p class="text-sm text-blue-600 mt-6 pt-4 border-t">
                Buka folder →
            </p>

        </a>

    @empty

        <div class="col-span-full bg-white rounded-xl shadow p-10 text-center">
            <div class="text-5xl mb-3">📂</div>
            <h2 class="text-xl font-semibold text-gray-700">Belum Ada Data Kapal</h2>
            <p class="text-gray-500 mt-2">Dokumen yang sudah disubmit staff akan muncul di sini.</p>
        </div>

    @endforelse

    <p id="noResult" class="hidden col-span-full text-center text-gray-500 py-6">
        Kapal tidak ditemukan.
    </p>

</div>

<script>
    const searchInput = document.getElementById('searchShip');
    const shipCards   = document.querySelectorAll('.ship-card');
    const noResult    = document.getElementById('noResult');

    searchInput.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();
        let visible = 0;

        shipCards.forEach(function (card) {
            const shipName = card.querySelector('.ship-name').innerText.toLowerCase();
            const match = shipName.includes(keyword);

            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        noResult.classList.toggle('hidden', visible !== 0 || shipCards.length === 0);
    });
</script>

@endsection