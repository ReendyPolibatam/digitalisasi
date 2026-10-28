<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="bg-[#eef3f5] text-gray-800">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#17607a] text-white fixed inset-y-0 left-0 flex flex-col">

        <!-- Logo -->
        <div class="px-6 py-6 border-b border-white/10">
            <h1 class="text-lg font-bold leading-tight">
                Digitalisasi Dokumen
            </h1>
            <p class="text-xs text-white/70 mt-1">
                Admin Panel
            </p>
        </div>

        <!-- Menu -->
        <nav class="flex-1 px-3 py-4 space-y-1">

            @php
                $menus = [
                    ['label' => 'Dashboard',           'route' => 'admin.dashboard',  'icon' => '📊'],
                    ['label' => 'Verifikasi Dokumen',  'route' => 'admin.documents*', 'icon' => '📄', 'href' => 'admin.documents'],
                    ['label' => 'Library Dokumen',     'route' => 'admin.library',    'icon' => '📚'],
                    ['label' => 'Monitoring',          'route' => 'admin.monitoring', 'icon' => '📋'],
                ];
            @endphp

            @foreach ($menus as $menu)
                @php $active = request()->routeIs($menu['route']); @endphp

                <a href="{{ route($menu['href'] ?? $menu['route']) }}"
                   class="flex items-center gap-3 h-11 px-4 rounded-xl text-sm font-medium transition
                          focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70
                          {{ $active
                              ? 'bg-[#d2dee3] text-[#17607a] shadow-sm'
                              : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                    <span class="w-5 text-center text-base">{{ $menu['icon'] }}</span>
                    <span>{{ $menu['label'] }}</span>
                </a>
            @endforeach

        </nav>

        <!-- Logout -->
        <div class="p-4 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit"
                        class="w-full h-11 rounded-xl border border-white/30 text-sm font-semibold transition
                               hover:bg-white hover:text-[#17607a]
                               focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70">
                    Logout
                </button>
            </form>
        </div>

    </aside>

    <!-- MAIN -->
    <main class="flex-1 ml-64 flex flex-col min-h-screen">

        <!-- TOPBAR -->
        <header class="bg-white border-b border-gray-200 px-8 h-16 flex justify-between items-center">

            <!-- Search -->
            <div class="w-96 relative">
                <input
                    type="text"
                    placeholder="Cari dokumen..."
                    class="w-full h-10 border border-gray-300 rounded-xl px-4 pr-10 text-sm
                           focus:outline-none focus:ring-2 focus:ring-[#17607a] focus:border-[#17607a]">

                <span class="absolute right-3 top-2.5 text-gray-400">
                    🔍
                </span>
            </div>

            <!-- User -->
            <div class="flex items-center gap-3">

                <div class="text-right leading-tight">
                    <h3 class="text-sm font-semibold text-gray-800">
                        {{ Auth::user()->name }}
                    </h3>
                    <p class="text-xs text-gray-500">
                        Administrator
                    </p>
                </div>

                <div class="w-10 h-10 rounded-full bg-[#17607a] text-white flex items-center justify-center font-bold">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>

            </div>

        </header>

        <!-- Alert Success -->
        @if(session('success'))
            <div
                id="successAlert"
                class="fixed top-5 right-5 bg-green-500 text-white px-6 py-4 rounded-xl shadow-xl z-50 transition-all duration-500">
                ✅ {{ session('success') }}
            </div>
        @endif

        <!-- Alert Error -->
        @if(session('error'))
            <div
                id="errorAlert"
                class="fixed top-5 right-5 bg-red-500 text-white px-6 py-4 rounded-xl shadow-xl z-50 transition-all duration-500">
                ❌ {{ session('error') }}
            </div>
        @endif

        <!-- Content -->
        <section class="flex-1 p-8">
            @yield('content')
        </section>

    </main>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    setTimeout(function () {

        const success = document.getElementById('successAlert');
        const error = document.getElementById('errorAlert');

        if (success) {
            success.style.opacity = '0';
            success.style.transform = 'translateX(30px)';
            setTimeout(() => success.remove(), 500);
        }

        if (error) {
            error.style.opacity = '0';
            error.style.transform = 'translateX(30px)';
            setTimeout(() => error.remove(), 500);
        }

    }, 3000);

});

</script>

</body>
</html>