@extends('layouts.app')

@section('content')
<div class="p-6">

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            Dashboard
        </h1>

        <p class="text-gray-500 mt-1">
            Selamat datang di sistem digitalisasi dokumen shipping
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="bg-white rounded-xl shadow p-5 border">
            <h2 class="text-gray-500 text-sm">
                Total Dokumen
            </h2>

            <p class="text-3xl font-bold mt-2 text-blue-600">
                120
            </p>
        </div>

        <div class="bg-white rounded-xl shadow p-5 border">
            <h2 class="text-gray-500 text-sm">
                Dokumen Diproses
            </h2>

            <p class="text-3xl font-bold mt-2 text-yellow-500">
                15
            </p>
        </div>

        <div class="bg-white rounded-xl shadow p-5 border">
            <h2 class="text-gray-500 text-sm">
                Dokumen Selesai
            </h2>

            <p class="text-3xl font-bold mt-2 text-green-600">
                105
            </p>
        </div>

    </div>

    <div class="mt-8 bg-white rounded-xl shadow border p-6">
        <h2 class="text-xl font-semibold mb-4">
            Aktivitas Terbaru
        </h2>

        <div class="space-y-3">

            <div class="flex justify-between border-b pb-2">
                <span>Invoice_001.pdf diupload</span>
                <span class="text-sm text-gray-500">2 menit lalu</span>
            </div>

            <div class="flex justify-between border-b pb-2">
                <span>OCR berhasil diproses</span>
                <span class="text-sm text-gray-500">10 menit lalu</span>
            </div>

            <div class="flex justify-between">
                <span>Dokumen BL selesai diverifikasi</span>
                <span class="text-sm text-gray-500">30 menit lalu</span>
            </div>

        </div>
    </div>

</div>
@endsection