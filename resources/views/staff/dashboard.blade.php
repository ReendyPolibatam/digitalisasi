@extends('layouts.app')

@section('content')

<div>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            Dashboard Staff
        </h1>

        <p class="text-gray-500 mt-2">
            Kelola dokumen shipping dan upload dokumen dengan mudah.
        </p>
    </div>

    <!-- Welcome Card -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl shadow-lg p-8 text-white mb-8">

        <div class="flex items-center justify-between">

            <div>
                <h2 class="text-3xl font-bold mb-2">
                    Selamat Datang, {{ Auth::user()->name }} 👋
                </h2>

                <p class="text-blue-100">
                    Sistem digitalisasi dokumen shipping berbasis OCR.
                </p>
            </div>

            <div class="hidden md:block text-6xl">
                📄
            </div>

        </div>

    </div>

    <!-- Menu Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Upload Card -->
        <a href="{{ route('documents.create') }}"
           class="bg-white rounded-2xl p-6 shadow hover:shadow-2xl transition duration-300 border border-gray-100 hover:border-blue-500 group">

            <div class="flex items-center justify-between mb-4">

                <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-2xl flex items-center justify-center text-3xl group-hover:scale-110 transition">
                    📤
                </div>

                <span class="text-blue-500 font-semibold">
                    Upload
                </span>

            </div>

            <h3 class="text-2xl font-bold text-gray-800 mb-2">
                Upload Dokumen
            </h3>

            <p class="text-gray-500">
                Upload dokumen shipping untuk diproses OCR dan disimpan ke sistem.
            </p>

        </a>

        <!-- Documents Card -->
        <a href="{{ route('documents.index') }}"
           class="bg-white rounded-2xl p-6 shadow hover:shadow-2xl transition duration-300 border border-gray-100 hover:border-green-500 group">

            <div class="flex items-center justify-between mb-4">

                <div class="bg-green-100 text-green-600 w-16 h-16 rounded-2xl flex items-center justify-center text-3xl group-hover:scale-110 transition">
                    📁
                </div>

                <span class="text-green-500 font-semibold">
                    Dokumen
                </span>

            </div>

            <h3 class="text-2xl font-bold text-gray-800 mb-2">
                Dokumen Saya
            </h3>

            <p class="text-gray-500">
                Lihat daftar dokumen yang telah diupload beserta status proses OCR.
            </p>

        </a>

    </div>

</div>

@endsection