@extends('layouts.admin')

@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">
        Verifikasi Dokumen
    </h1>

    <p class="text-gray-500 mt-2">
        Kelola dan verifikasi dokumen yang diupload staff
    </p>
</div>

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 text-green-700 rounded-lg">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg">
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-50">
            <tr>
                <th class="text-left px-6 py-4">Nama File</th>
                <th class="text-left px-6 py-4">Voyage</th>
                <th class="text-left px-6 py-4">Status</th>
                <th class="text-left px-6 py-4">Tanggal Upload</th>
                <th class="text-center px-6 py-4">Aksi</th>
            </tr>
        </thead>

        <tbody>

        @forelse($documents as $doc)

            <tr class="border-t hover:bg-gray-50">

                <td class="px-6 py-4">
                    {{ $doc->file_name }}
                </td>

                <td class="px-6 py-4 text-sm">
                    @if(!empty($doc->voyage))
                        <span class="text-gray-700">{{ $doc->voyage }}</span>
                    @else
                        <span class="text-gray-400 italic">Voyage tidak terdeteksi</span>
                    @endif
                </td>

                <td class="px-6 py-4">
                    @if($doc->status == 'approved')
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
                            Approved
                        </span>
                    @elseif($doc->status == 'rejected')
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-semibold">
                            Rejected
                        </span>
                    @else
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-semibold">
                            Pending
                        </span>
                    @endif
                </td>

                <td class="px-6 py-4">
                    {{ $doc->created_at->format('d M Y') }}
                </td>

                <td class="px-6 py-4">

                    <div class="flex justify-center items-center gap-2">

                        <a href="{{ route('admin.documents.show', $doc->id) }}"
                           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                            Detail
                        </a>

                        @if($doc->status == 'pending')

                            <button type="button"
                                    class="menu-toggle bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg">
                                Verifikasi ▾
                            </button>

                            <div class="action-menu hidden fixed z-50 w-40 bg-white rounded-lg shadow-lg border border-gray-100 py-1">

                                <form action="{{ route('documents.approve', $doc->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="block w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50">
                                        ✓ Approve
                                    </button>
                                </form>

                                <form action="{{ route('documents.reject', $doc->id) }}" method="POST"
                                      onsubmit="return confirm('Yakin ingin menolak dokumen ini?')">
                                    @csrf
                                    <button type="submit"
                                            class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        ✕ Reject
                                    </button>
                                </form>

                            </div>

                        @else

                            <span class="text-gray-400 text-sm">
                                Sudah diproses
                            </span>

                        @endif

                    </div>

                </td>

            </tr>

        @empty

            <tr>
                <td colspan="5" class="text-center py-10 text-gray-500">
                    Belum ada dokumen
                </td>
            </tr>

        @endforelse

        </tbody>

    </table>

</div>

<div class="mt-6">
    {{ $documents->links() }}
</div>

<script>
    function closeMenus() {
        document.querySelectorAll('.action-menu').forEach(function (m) {
            m.classList.add('hidden');
        });
    }

    document.querySelectorAll('.menu-toggle').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();

            const menu   = btn.nextElementSibling;
            const isOpen = !menu.classList.contains('hidden');

            closeMenus();
            if (isOpen) return;

            // posisi fixed supaya tidak terpotong overflow-hidden milik tabel
            const rect = btn.getBoundingClientRect();
            menu.classList.remove('hidden');
            menu.style.top  = (rect.bottom + 4) + 'px';
            menu.style.left = (rect.right - menu.offsetWidth) + 'px';
        });
    });

    document.addEventListener('click', closeMenus);
    window.addEventListener('scroll', closeMenus, true);
    window.addEventListener('resize', closeMenus);
</script>

@endsection