@extends('layouts.admin')

@section('content')

<div class="mb-8">

    <h1 class="text-3xl font-bold text-gray-800">
        Dashboard Admin
    </h1>

    <p class="text-gray-500 mt-2">
        Selamat datang, {{ Auth::user()->name }}
    </p>

</div>

<!-- Statistik -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500">Total Dokumen</p>
        <h2 class="text-3xl font-bold text-blue-600">
            {{ $total ?? 0 }}
        </h2>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500">Pending</p>
        <h2 class="text-3xl font-bold text-yellow-500">
            {{ $pending ?? 0 }}
        </h2>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500">Approved</p>
        <h2 class="text-3xl font-bold text-green-500">
            {{ $approved ?? 0 }}
        </h2>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500">Rejected</p>
        <h2 class="text-3xl font-bold text-red-500">
            {{ $rejected ?? 0 }}
        </h2>
    </div>

</div>

<!-- Grafik -->
<div class="bg-white rounded-xl shadow p-6 mb-8">

    <div class="mb-4">
        <h2 class="text-xl font-bold">
            Aktivitas Dokumen
        </h2>

        <p class="text-gray-500 text-sm">
            Statistik upload dokumen per bulan
        </p>
    </div>

    <canvas id="documentChart" height="100"></canvas>

</div>

<!-- Dokumen Terbaru -->
<div class="bg-white rounded-xl shadow p-6">

    <h2 class="text-xl font-bold mb-4">
        Dokumen Terbaru
    </h2>

    <table class="w-full">

        <thead>
            <tr class="border-b">
                <th class="text-left py-3">Nama File</th>
                <th class="text-left py-3">Status</th>
                <th class="text-left py-3">Tanggal</th>
            </tr>
        </thead>

        <tbody>

            @forelse($documents as $doc)

            <tr class="border-b">

                <td class="py-3">
                    {{ $doc->file_name }}
                </td>

                <td class="py-3">

                    @if($doc->status == 'approved')
                        <span class="text-green-600 font-semibold">
                            Approved
                        </span>
                    @elseif($doc->status == 'rejected')
                        <span class="text-red-600 font-semibold">
                            Rejected
                        </span>
                    @else
                        <span class="text-yellow-500 font-semibold">
                            Pending
                        </span>
                    @endif

                </td>

                <td class="py-3">
                    {{ $doc->created_at->format('d M Y') }}
                </td>

            </tr>

            @empty

            <tr>
                <td colspan="3" class="py-4 text-center text-gray-500">
                    Belum ada dokumen
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>

</div>

<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('documentChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            'Jan','Feb','Mar','Apr','Mei','Jun',
            'Jul','Agu','Sep','Okt','Nov','Des'
        ],

        datasets: [{
            label: 'Upload Dokumen',
            data: @json($monthlyData),
            borderWidth: 3,
            tension: 0.4,
            fill: true
        }]
    }
});
</script>

@endsection