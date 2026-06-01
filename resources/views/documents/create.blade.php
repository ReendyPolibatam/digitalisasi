@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto">

    <!-- Header -->
    <div class="mb-8">

        <h1 class="text-3xl font-bold text-gray-800">
            Upload Dokumen
        </h1>

        <p class="text-gray-500 mt-2">
            Upload dokumen shipping untuk diproses OCR dan diverifikasi admin.
        </p>

    </div>


    <!-- Alert Error -->
    @if ($errors->any())

        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-xl mb-6">

            <ul class="list-disc list-inside">

                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>

    @endif


    <!-- Upload Card -->
    <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">

        <form action="{{ route('documents.store') }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf

            <!-- Upload Area -->
            <div class="border-2 border-dashed border-blue-300 rounded-2xl p-10 text-center hover:border-blue-500 transition">

                <div class="text-6xl mb-4">
                    📄
                </div>

                <h2 class="text-2xl font-bold text-gray-700 mb-2">
                    Pilih Dokumen
                </h2>

                <p class="text-gray-500 mb-6">
                    Format yang didukung: PDF, JPG, JPEG, PNG
                </p>

                <input type="file"
                       name="file"
                       class="block w-full text-sm text-gray-600
                              file:mr-4 file:py-3 file:px-6
                              file:rounded-xl file:border-0
                              file:text-sm file:font-semibold
                              file:bg-blue-600 file:text-white
                              hover:file:bg-blue-700
                              cursor-pointer">

            </div>


            <!-- Button -->
            <div class="mt-8 flex justify-end">

                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transition">

                    Upload Dokumen

                </button>

            </div>

        </form>

    </div>

</div>

@endsection