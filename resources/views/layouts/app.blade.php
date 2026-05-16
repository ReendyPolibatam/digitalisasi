<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digitalisasi Dokumen</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white shadow-md border-b">

        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

            <!-- Logo -->
            <div>
                <h1 class="text-2xl font-bold text-blue-600">
                    Digitalisasi Dokumen
                </h1>

                <p class="text-sm text-gray-500">
                    Sistem Manajemen Dokumen Shipping
                </p>
            </div>

            <!-- Menu -->
            <div class="flex items-center gap-6">

                <a href="/dashboard"
                   class="text-gray-700 hover:text-blue-600 transition font-medium">
                    Dashboard
                </a>

                <a href="/documents"
                   class="text-gray-700 hover:text-blue-600 transition font-medium">
                    Dokumen
                </a>

                @auth

                <div class="flex items-center gap-4">

                    <div class="text-right">
                        <p class="font-semibold text-gray-800">
                            {{ Auth::user()->name }}
                        </p>

                        <p class="text-sm text-gray-500">
                            Staff
                        </p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition shadow">
                            Logout
                        </button>
                    </form>

                </div>

                @endauth

            </div>

        </div>

    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto p-6">

        @yield('content')

    </main>

</body>
</html>