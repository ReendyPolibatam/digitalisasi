@extends('layouts.admin')

@section('content')

@php $editing = $user->exists; @endphp

<div class="mb-8">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-[#17607a] hover:underline">
        ← Kembali ke daftar pengguna
    </a>
    <h1 class="text-3xl font-bold text-gray-800 mt-3">
        {{ $editing ? 'Ubah Pengguna' : 'Tambah Pengguna' }}
    </h1>
    <p class="text-gray-500 mt-2">
        {{ $editing ? 'Perbarui data akun ' . $user->name . '.' : 'Buat akun baru untuk Admin atau Staff.' }}
    </p>
</div>

<form method="POST"
      action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}"
      class="bg-white rounded-2xl shadow p-8 max-w-2xl space-y-6">
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    <div>
        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nama</label>
        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus
               class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500
                      {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }}">
        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
               class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500
                      {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }}">
        @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
        <select id="role" name="role" required
                class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500
                       {{ $errors->has('role') ? 'border-red-400' : 'border-gray-300' }}">
            <option value="staff" @selected(old('role', $user->role) === 'staff')>Staff</option>
            <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
        </select>
        @error('role') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
            <input id="password" type="password" name="password" autocomplete="new-password" @required(!$editing)
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500
                          {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }}">
            @if($editing)
                <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ingin mengganti password.</p>
            @endif
            @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" @required(!$editing)
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    <div class="flex gap-3 pt-2">
        <button type="submit"
                class="h-11 px-6 rounded-xl bg-[#17607a] text-white text-sm font-semibold shadow hover:bg-[#124d62] transition">
            {{ $editing ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
        </button>
        <a href="{{ route('admin.users.index') }}"
           class="h-11 px-6 rounded-xl bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition inline-flex items-center">
            Batal
        </a>
    </div>
</form>

@endsection
