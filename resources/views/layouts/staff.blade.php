<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Panel</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col fixed h-screen">

        <!-- Logo -->
        <div class="p-6 border-b border-slate-700">

            <h1 class="text-2xl font-bold">
                Digitalisasi Dokumen
            </h1>

            <p class="text-sm text-slate-400">
                Staff Panel
            </p>

        </div>

        <!-- MENU -->
        <nav class="flex-1 p-4 space-y-2">

            <a href="{{ route('staff.dashboard') }}"
               class="block px-4 py-3 rounded-lg hover:bg-slate-800 transition">

                📊 Dashboard

            </a>

            <a href="{{ route('documents.create') }}"
               class="block px-4 py-3 rounded-lg hover:bg-slate-800 transition">

                ⬆️ Upload Dokumen

            </a>

            <a href="{{ route('documents.index') }}"
               class="block px-4 py-3 rounded-lg hover:bg-slate-800 transition">

                📄 Dokumen Saya

            </a>

        </nav>

        <!-- LOGOUT -->
        <div class="p-4 border-t border-slate-700">

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    class="w-full bg-red-500 hover:bg-red-600 py-3 rounded-lg transition">

                    Logout

                </button>

            </form>

        </div>

    </aside>

    <!-- MAIN -->
    <main class="flex-1 flex flex-col ml-64">

        <!-- TOPBAR -->
        <header class="bg-white shadow-sm px-8 py-4 flex justify-between items-center">

            <!-- SEARCH -->
            <div class="w-96 relative">

                <input
                    type="text"
                    placeholder="Search document..."
                    class="w-full border rounded-lg px-4 py-2 pr-10 focus:ring-2 focus:ring-blue-500 focus:outline-none">

                <span class="absolute right-3 top-2.5 text-gray-400">
                    🔍
                </span>

            </div>

            <!-- USER -->
            <div class="flex items-center gap-4">

                <div class="text-right">

                    <h3 class="font-semibold">
                        {{ Auth::user()->name }}
                    </h3>

                    <p class="text-sm text-gray-500">
                        Staff
                    </p>

                </div>

                <div
                    class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">

                    {{ strtoupper(substr(Auth::user()->name,0,1)) }}

                </div>

            </div>

        </header>

        <!-- ALERT SUCCESS -->
        @if(session('success'))

        <div
            id="successAlert"
            class="fixed top-5 right-5 bg-green-500 text-white px-6 py-4 rounded-xl shadow-xl z-50">

            ✅ {{ session('success') }}

        </div>

        @endif

        <!-- ALERT ERROR -->
        @if(session('error'))

        <div
            id="errorAlert"
            class="fixed top-5 right-5 bg-red-500 text-white px-6 py-4 rounded-xl shadow-xl z-50">

            ❌ {{ session('error') }}

        </div>

        @endif

        <!-- CONTENT -->
        <div class="p-8">

            @yield('content')

        </div>

    </main>

</div>

<script>

setTimeout(() => {

    let success = document.getElementById('successAlert');
    let error = document.getElementById('errorAlert');

    if(success){
        success.style.opacity = "0";
        setTimeout(() => success.remove(), 500);
    }

    if(error){
        error.style.opacity = "0";
        setTimeout(() => error.remove(), 500);
    }

}, 3000);

</script>

</body>
</html>