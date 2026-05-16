@extends('layouts.app')

@section('content')

<div>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            Dashboard Admin
        </h1>

        <p class="text-gray-500 mt-2">
            Verifikasi dokumen OCR dan kelola data dokumen shipping.
        </p>
    </div>

    <!-- Welcome Card -->
    <div class="bg-gradient-to-r from-indigo-600 to-blue-500 rounded-2xl shadow-lg p-8 text-white mb-8">

        <div class="flex items-center justify-between">

            <div>
                <h2 class="text-3xl font-bold mb-2">
                    Selamat Datang, {{ Auth::user()->name }} 👋
                </h2>

                <p class="text-blue-100">
                    Dashboard approver untuk verifikasi dokumen hasil OCR.
                </p>
            </div>

            <div class="hidden md:block text-6xl">
                🛡️
            </div>

        </div>

    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <!-- Pending -->
        <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-yellow-400">

            <p class="text-gray-500 mb-2">
                Pending Verifikasi
            </p>

            <h2 class="text-4xl font-bold text-yellow-500">
                12
            </h2>

        </div>

        <!-- Approved -->
        <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-green-500">

            <p class="text-gray-500 mb-2">
                Dokumen Approved
            </p>

            <h2 class="text-4xl font-bold text-green-500">
                48
            </h2>

        </div>

        <!-- Rejected -->
        <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-red-500">

            <p class="text-gray-500 mb-2">
                Dokumen Rejected
            </p>

            <h2 class="text-4xl font-bold text-red-500">
                5
            </h2>

        </div>

    </div>

    <!-- Menu -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Verifikasi -->
        <a href="{{ route('documents.index') }}"
           class="bg-white rounded-2xl p-6 shadow hover:shadow-2xl transition duration-300 border border-gray-100 hover:border-blue-500 group">

            <div class="flex items-center justify-between mb-4">

                <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-2xl flex items-center justify-center text-3xl group-hover:scale-110 transition">
                    ✅
                </div>

                <span class="text-blue-500 font-semibold">
                    Verifikasi
                </span>

            </div>

            <h3 class="text-2xl font-bold text-gray-800 mb-2">
                Verifikasi Dokumen
            </h3>

            <p class="text-gray-500">
                Lihat dan verifikasi hasil OCR dokumen shipping staff.
            </p>

        </a>

        <!-- Monitoring -->
        <a href="#"
           class="bg-white rounded-2xl p-6 shadow hover:shadow-2xl transition duration-300 border border-gray-100 hover:border-indigo-500 group">

            <div class="flex items-center justify-between mb-4">

                <div class="bg-indigo-100 text-indigo-600 w-16 h-16 rounded-2xl flex items-center justify-center text-3xl group-hover:scale-110 transition">
                    📊
                </div>

                <span class="text-indigo-500 font-semibold">
                    Monitoring
                </span>

            </div>

            <h3 class="text-2xl font-bold text-gray-800 mb-2">
                Monitoring Dokumen
            </h3>

            <p class="text-gray-500">
                Pantau status seluruh dokumen dalam sistem digitalisasi.
            </p>

        </a>

    </div>

</div>

@endsection