@extends('layouts.admin')

@section('content')

<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Kelola Pengguna</h1>
        <p class="text-gray-500 mt-2">Tambah, ubah, dan hapus akun Admin maupun Staff.</p>
    </div>

    <a href="{{ route('admin.users.create') }}"
       class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-[#17607a] text-white text-sm font-semibold shadow hover:bg-[#124d62] transition">
        <span class="text-lg leading-none">+</span>
        Tambah Pengguna
    </a>
</div>

{{-- Statistik --}}
<div class="grid md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl shadow">
        <p class="text-gray-500">Total Pengguna</p>
        <h2 class="text-3xl font-bold text-blue-600">{{ $totalUsers }}</h2>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow">
        <p class="text-gray-500">Admin</p>
        <h2 class="text-3xl font-bold text-[#17607a]">{{ $totalAdmin }}</h2>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow">
        <p class="text-gray-500">Staff</p>
        <h2 class="text-3xl font-bold text-green-500">{{ $totalStaff }}</h2>
    </div>
</div>

{{-- Search + filter --}}
<form method="GET" action="{{ route('admin.users.index') }}"
      class="bg-white rounded-xl shadow p-5 mb-8 flex flex-wrap gap-3">
    <input type="text" name="q" value="{{ $search }}"
           placeholder="Cari nama atau email..."
           class="flex-1 min-w-[220px] border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">

    <select name="role"
            class="border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Semua role</option>
        <option value="admin" @selected($role === 'admin')>Admin</option>
        <option value="staff" @selected($role === 'staff')>Staff</option>
    </select>

    <button type="submit"
            class="px-5 py-3 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition">
        Cari
    </button>

    @if($search || $role)
        <a href="{{ route('admin.users.index') }}"
           class="px-5 py-3 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition">
            Reset
        </a>
    @endif
</form>

{{-- Tabel pengguna --}}
<div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-6 py-4 font-semibold">Nama</th>
                    <th class="px-6 py-4 font-semibold">Email</th>
                    <th class="px-6 py-4 font-semibold">Role</th>
                    <th class="px-6 py-4 font-semibold text-center">Dokumen</th>
                    <th class="px-6 py-4 font-semibold">Terdaftar</th>
                    <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-[#17607a] text-white flex items-center justify-center font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $user->name }}</p>
                                    @if($user->is(auth()->user()))
                                        <p class="text-xs text-gray-400">Akun Anda</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @if($user->role === 'admin')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#d2dee3] text-[#17607a]">Admin</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Staff</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-gray-600">{{ $user->documents_count }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $user->created_at?->format('d M Y') ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="px-3 py-1.5 rounded-lg bg-yellow-100 text-yellow-700 text-xs font-semibold hover:bg-yellow-200 transition">
                                    Ubah
                                </a>

                                @unless($user->is(auth()->user()))
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          onsubmit="return confirm('Hapus pengguna {{ addslashes($user->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg bg-red-100 text-red-700 text-xs font-semibold hover:bg-red-200 transition">
                                            Hapus
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                            Tidak ada pengguna yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    @endif
</div>

@endsection
