<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digitalisasi Dokumen</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

    <!-- NAVBAR -->
    <nav class="bg-white shadow-md border-b">

        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

            <!-- LOGO -->
            @auth
                @if(Auth::user()->role == 'admin')
                    <a href="{{ route('admin.dashboard') }}">
                @else
                    <a href="{{ route('staff.dashboard') }}">
                @endif
            @else
                <a href="/">
            @endauth

                <h1 class="text-2xl font-bold text-blue-600 hover:text-blue-700 transition">
                    Digitalisasi Dokumen
                </h1>

                <p class="text-sm text-gray-500">
                    Sistem Manajemen Dokumen Shipping
                </p>

            </a>


            <!-- MENU -->
            <div class="flex items-center gap-6">

                @auth

                    <!-- STAFF MENU -->
                    @if(Auth::user()->role == 'staff')

                        <a href="{{ route('staff.dashboard') }}"
                           class="text-gray-700 hover:text-blue-600 font-medium">
                            Dashboard
                        </a>

                        <a href="{{ route('documents.index') }}"
                           class="text-gray-700 hover:text-blue-600 font-medium">
                            Dokumen
                        </a>

                    @endif


                    <!-- ADMIN MENU -->
                    @if(Auth::user()->role == 'admin')

                        <a href="{{ route('admin.dashboard') }}"
                           class="text-gray-700 hover:text-blue-600 font-medium">
                            Dashboard
                        </a>

                        <a href="{{ route('admin.documents') }}"
                           class="text-gray-700 hover:text-blue-600 font-medium">
                            Verifikasi
                        </a>

                        <a href="{{ route('admin.monitoring') }}"
                           class="text-gray-700 hover:text-blue-600 font-medium">
                            Monitoring
                        </a>

                    @endif

                @endauth


                <!-- USER INFO -->
                @auth

                <div class="flex items-center gap-4 border-l pl-4">

                    <div class="text-right">
                        <p class="font-semibold text-gray-800">
                            {{ Auth::user()->name }}
                        </p>

                        <p class="text-sm text-gray-500 capitalize">
                            {{ Auth::user()->role }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition shadow">
                            Logout
                        </button>
                    </form>

                </div>

                @endauth

            </div>

        </div>

    </nav>

    <!-- CONTENT -->
    <main class="max-w-7xl mx-auto p-6">
        @yield('content')
    </main>

</body>
</html>