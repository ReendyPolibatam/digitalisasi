@extends('layouts.admin')

@section('content')

<div class="mb-8">

    <h1 class="text-3xl font-bold text-gray-800">
        Library Dokumen
    </h1>

    <p class="text-gray-500 mt-2">
        Kumpulan dokumen shipping berdasarkan kapal.
    </p>

</div>

<!-- Search -->
<div class="bg-white rounded-xl shadow p-5 mb-8">

    <div class="relative">

        <input
            type="text"
            id="searchShip"
            placeholder="Cari nama kapal..."
            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">

    </div>

</div>

<!-- List Kapal -->
<div
    id="shipContainer"
    class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

    @forelse($ships as $ship)

        <a
            href="#"
            class="ship-card bg-white rounded-xl shadow hover:shadow-lg transition p-6 block">

            <div class="flex justify-between items-center mb-4">

                <div class="text-4xl">
                    🚢
                </div>

                <div class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">

                    3 Dokumen

                </div>

            </div>

            <h2 class="text-xl font-bold text-gray-800 ship-name">

                {{ $ship->ship_name }}

            </h2>

            <p class="text-gray-500 mt-2">

                Invoice • Loading • Bongkar

            </p>

        </a>

    @empty

        <div
            class="col-span-3 bg-white rounded-xl shadow p-10 text-center">

            <div class="text-5xl mb-3">
                📂
            </div>

            <h2 class="text-xl font-semibold text-gray-700">
                Belum Ada Data Kapal
            </h2>

            <p class="text-gray-500 mt-2">
                Dokumen yang sudah diproses akan muncul di sini.
            </p>

        </div>

    @endforelse

</div>

<script>

document.getElementById('searchShip').addEventListener('keyup', function() {

    let keyword = this.value.toLowerCase();

    document.querySelectorAll('.ship-card').forEach(function(card){

        let shipName = card.querySelector('.ship-name')
            .innerText
            .toLowerCase();

        if(shipName.includes(keyword)){
            card.style.display = '';
        }else{
            card.style.display = 'none';
        }

    });

});

</script>

@endsection