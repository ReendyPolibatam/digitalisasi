<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Digitalisasi Dokumen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#17607a] flex items-center justify-center p-6">

    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden grid grid-cols-1 md:grid-cols-2">

        {{-- Kiri: gambar pelayaran --}}
        <div class="hidden md:block relative min-h-[480px] bg-[#17607a]">
            <img src="{{ asset('images/pelayaran.jpg') }}"
                 alt="Kapal kontainer di pelabuhan"
                 class="absolute inset-0 w-full h-full object-cover object-[35%_center]">
            <div class="absolute inset-0 bg-[#17607a]/20"></div>
        </div>

        {{-- Kanan: form login --}}
        <div class="bg-[#d2dee3] p-8 md:p-10 flex flex-col justify-center">

            <!-- Title -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-[#17607a]">
                    Digitalisasi Dokumen
                </h1>
                <p class="text-gray-600 mt-1 text-sm">
                    Silakan login untuk melanjutkan
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-5">
                    <label for="email" class="block mb-2 text-sm font-semibold text-gray-700">
                        Email
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#17607a] focus:border-[#17607a] focus:outline-none"
                        placeholder="Masukkan email"
                    >

                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-5">
                    <label for="password" class="block mb-2 text-sm font-semibold text-gray-700">
                        Password
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#17607a] focus:border-[#17607a] focus:outline-none"
                        placeholder="Masukkan password"
                    >

                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Button -->
                <button
                    type="submit"
                    class="w-full bg-[#17607a] hover:bg-[#124c61] text-white font-semibold py-3 rounded-xl transition duration-200"
                >
                    Login
                </button>

                <!-- Register -->
                <div class="flex items-center justify-end text-sm mt-5">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="text-[#17607a] hover:underline">
                            Register
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

</body>
</html>