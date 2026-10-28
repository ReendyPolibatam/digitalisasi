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

{{-- Search --}}
<div class="bg-white rounded-xl shadow p-5 mb-8">
    <input
        type="text"
        id="searchShip"
        placeholder="Cari nama kapal..."
        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
</div>

{{-- Card per Kapal --}}
<div id="shipContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

    @forelse($ships as $ship)

        <div class="ship-card bg-white rounded-xl shadow hover:shadow-lg transition p-6">

            <div class="flex justify-between items-center mb-4">
                <div class="text-4xl">🚢</div>
                <div class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">
                    {{ $ship->total_documents }} Dokumen
                </div>
            </div>

            <h2 class="text-xl font-bold text-gray-800 ship-name">
                {{ $ship->ship_name }}
            </h2>

            <p class="text-sm text-gray-400 mt-1">{{ $ship->voyage }}</p>

            <p class="text-gray-500 mt-2 text-sm">
                @if($ship->categories->isNotEmpty())
                    {{ $ship->categories->implode(' • ') }}
                @else
                    <span class="italic">Belum ada kategori</span>
                @endif
            </p>

            {{-- Status summary --}}
            <div class="flex gap-2 mt-3">
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
                       class="flex items-center justify-between px-3 py-2 rounded-lg bg-gray-50 hover:bg-blue-50 transition">

                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $doc->file_name }}</p>
                            <p class="text-xs text-gray-400">{{ $doc->uploader }} · {{ $doc->created_at->format('d M Y H:i') }}</p>
                        </div>

                        @if($doc->status == 'pending')
                            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs">Pending</span>
                        @elseif($doc->status == 'approved')
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">Approved</span>
                        @else
                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">Rejected</span>
                        @endif

                    </a>
                @endforeach
            </div>

        </div>

    @empty

        <div class="col-span-3 bg-white rounded-xl shadow p-10 text-center">
            <div class="text-5xl mb-3">📂</div>
            <h2 class="text-xl font-semibold text-gray-700">Belum Ada Data</h2>
            <p class="text-gray-500 mt-2">Dokumen yang sudah disubmit akan muncul di sini.</p>
        </div>

    @endforelse

</div>

<script>
document.getElementById('searchShip').addEventListener('keyup', function() {
    let keyword = this.value.toLowerCase();
    document.querySelectorAll('.ship-card').forEach(function(card) {
        let shipName = card.querySelector('.ship-name').innerText.toLowerCase();
        card.style.display = shipName.includes(keyword) ? '' : 'none';
    });
});
</script>

@endsection