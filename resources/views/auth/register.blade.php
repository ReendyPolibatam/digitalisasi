<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Digitalisasi Dokumen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#17607a] flex items-center justify-center p-6">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">

        {{-- Atas: banner gambar (headline) --}}
        <div class="relative h-28">
            <img src="{{ asset('images/pelayaran.jpg') }}"
                 alt="Kapal kontainer di pelabuhan"
                 class="absolute inset-0 w-full h-full object-cover object-[35%_55%]">
            <div class="absolute inset-0 bg-[#17607a]/30"></div>
        </div>

        {{-- Bawah: form --}}
        <div class="bg-[#d2dee3] px-8 py-8">

            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-[#17607a]">Register</h1>
                <p class="text-gray-600 mt-1 text-sm">Buat akun Digitalisasi Dokumen</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Nama -->
                <div class="mb-5">
                    <label for="name" class="block mb-2 text-sm font-semibold text-gray-700">Nama</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}"
                           required autofocus autocomplete="name"
                           placeholder="Masukkan nama"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#17607a] focus:border-[#17607a] focus:outline-none">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-5">
                    <label for="email" class="block mb-2 text-sm font-semibold text-gray-700">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           required autocomplete="username"
                           placeholder="Masukkan email"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#17607a] focus:border-[#17607a] focus:outline-none">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-5">
                    <label for="password" class="block mb-2 text-sm font-semibold text-gray-700">Password</label>
                    <input id="password" type="password" name="password"
                           required autocomplete="new-password"
                           placeholder="Masukkan password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#17607a] focus:border-[#17607a] focus:outline-none">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konfirmasi password -->
                <div class="mb-5">
                    <label for="password_confirmation" class="block mb-2 text-sm font-semibold text-gray-700">Konfirmasi password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           required autocomplete="new-password"
                           placeholder="Ulangi password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#17607a] focus:border-[#17607a] focus:outline-none">
                    @error('password_confirmation')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div class="mb-6">
                    <label for="role" class="block mb-2 text-sm font-semibold text-gray-700">Role</label>
                    <select id="role" name="role" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-[#17607a] focus:border-[#17607a] focus:outline-none">
                        <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih role</option>
                        <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Button -->
                <button type="submit"
                        class="w-full bg-[#17607a] hover:bg-[#124c61] text-white font-semibold py-3 rounded-xl transition duration-200">
                    Register
                </button>

                <p class="text-sm text-center text-gray-600 mt-5">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-[#17607a] hover:underline">Login</a>
                </p>
            </form>
        </div>
    </div>

</body>
</html>