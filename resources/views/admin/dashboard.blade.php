@extends('layouts.app')

@section('content')

<div>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            Dashboard Admin
        </h1>

        <p class="text-gray-500 mt-2">
            Verifikasi dokumen OCR dan monitoring dokumen shipping.
        </p>
    </div>

    <!-- Welcome -->
    <div class="bg-gradient-to-r from-indigo-600 to-blue-500 rounded-2xl shadow-lg p-8 text-white mb-8">

        <div class="flex justify-between items-center">

            <div>
                <h2 class="text-3xl font-bold mb-2">
                    Selamat Datang, {{ Auth::user()->name }} 👋
                </h2>

                <p class="text-blue-100">
                    Dashboard administrator sistem digitalisasi dokumen.
                </p>
            </div>

            <div class="hidden md:block text-6xl">
                🛡️
            </div>

        </div>

    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

        <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-blue-500">
            <p class="text-gray-500 mb-2">Total Dokumen</p>
            <h2 class="text-4xl font-bold text-blue-500">
                {{ $total }}
            </h2>
        </div>

        <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-yellow-400">
            <p class="text-gray-500 mb-2">Pending</p>
            <h2 class="text-4xl font-bold text-yellow-500">
                {{ $pending }}
            </h2>
        </div>

        <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-green-500">
            <p class="text-gray-500 mb-2">Approved</p>
            <h2 class="text-4xl font-bold text-green-500">
                {{ $approved }}
            </h2>
        </div>

        <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-red-500">
            <p class="text-gray-500 mb-2">Rejected</p>
            <h2 class="text-4xl font-bold text-red-500">
                {{ $rejected }}
            </h2>
        </div>

    </div>

    <!-- Menu -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Verifikasi -->
        <a href="{{ route('admin.documents') }}"
           class="bg-white rounded-2xl p-6 shadow hover:shadow-xl transition border hover:border-blue-500">

            <div class="text-5xl mb-4">
                ✅
            </div>

            <h3 class="text-2xl font-bold text-gray-800 mb-2">
                Verifikasi Dokumen
            </h3>

            <p class="text-gray-500">
                Approve atau reject dokumen yang diupload staff.
            </p>

        </a>

        <!-- Monitoring -->
        <a href="{{ route('admin.monitoring') }}"
           class="bg-white rounded-2xl p-6 shadow hover:shadow-xl transition border hover:border-indigo-500">

            <div class="text-5xl mb-4">
                📊
            </div>

            <h3 class="text-2xl font-bold text-gray-800 mb-2">
                Monitoring Dokumen
            </h3>

            <p class="text-gray-500">
                Pantau seluruh aktivitas dokumen dalam sistem.
            </p>

        </a>

    </div>

</div>

@endsection