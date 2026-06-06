<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 text-white fixed h-screen flex flex-col">

        <!-- Logo -->
        <div class="p-6 border-b border-slate-700">

            <h1 class="text-2xl font-bold">
                Digitalisasi Dokumen
            </h1>

            <p class="text-sm text-slate-400 mt-1">
                Admin Panel
            </p>

        </div>

        <!-- MENU -->
        <nav class="flex-1 p-4 space-y-2">

            <a href="{{ route('admin.dashboard') }}"
               class="block px-4 py-3 rounded-lg transition hover:bg-slate-800
               {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600' : '' }}">

                📊 Dashboard

            </a>

            <a href="{{ route('admin.documents') }}"
               class="block px-4 py-3 rounded-lg transition hover:bg-slate-800
               {{ request()->routeIs('admin.documents') ? 'bg-blue-600' : '' }}">

                📄 Verifikasi Dokumen

            </a>

            <a href="{{ route('admin.library') }}"
            class="block px-4 py-3 rounded-lg transition hover:bg-slate-800
            {{ request()->routeIs('admin.library') ? 'bg-blue-600' : '' }}">

                📚 Library Dokumen

            </a>

            <a href="{{ route('admin.monitoring') }}"
               class="block px-4 py-3 rounded-lg transition hover:bg-slate-800
               {{ request()->routeIs('admin.monitoring') ? 'bg-blue-600' : '' }}">

                📋 Monitoring

            </a>

        </nav>

        <!-- LOGOUT -->
        <div class="p-4 border-t border-slate-700">

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    type="submit"
                    class="w-full bg-red-500 hover:bg-red-600 py-3 rounded-lg transition">

                    Logout

                </button>

            </form>

        </div>

    </aside>

    <!-- MAIN -->
    <main class="flex-1 ml-64 flex flex-col min-h-screen">

        <!-- TOPBAR -->
        <header class="bg-white shadow-sm px-8 py-4 flex justify-between items-center">

            <!-- SEARCH -->
            <div class="w-96 relative">

                <input
                    type="text"
                    placeholder="Search document..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 pr-10
                           focus:outline-none focus:ring-2 focus:ring-blue-500">

                <span class="absolute right-3 top-2.5 text-gray-400">
                    🔍
                </span>

            </div>

            <!-- USER INFO -->
            <div class="flex items-center gap-4">

                <div class="text-right">

                    <h3 class="font-semibold text-gray-800">
                        {{ Auth::user()->name }}
                    </h3>

                    <p class="text-sm text-gray-500">
                        Administrator
                    </p>

                </div>

                <div
                    class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">

                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}

                </div>

            </div>

        </header>

        <!-- SUCCESS ALERT -->
        @if(session('success'))

            <div
                id="successAlert"
                class="fixed top-5 right-5 bg-green-500 text-white px-6 py-4 rounded-xl shadow-xl z-50 transition-all duration-500">

                ✅ {{ session('success') }}

            </div>

        @endif

        <!-- ERROR ALERT -->
        @if(session('error'))

            <div
                id="errorAlert"
                class="fixed top-5 right-5 bg-red-500 text-white px-6 py-4 rounded-xl shadow-xl z-50 transition-all duration-500">

                ❌ {{ session('error') }}

            </div>

        @endif

        <!-- PAGE CONTENT -->
        <section class="flex-1 p-8">

            @yield('content')

        </section>

    </main>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    setTimeout(() => {

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