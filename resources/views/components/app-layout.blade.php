<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Consentidos & Traviesos') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-orange-800 tracking-tight">Consentidos<span class="text-amber-600">&</span>Traviesos</a>
                    <div class="hidden md:ml-10 md:flex space-x-8">
                        <a href="/" class="text-gray-600 hover:text-orange-700 transition duration-150 font-medium">Catálogo</a>
                        <a href="/categories" class="text-gray-600 hover:text-orange-700 transition duration-150 font-medium">Categorías</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <livewire:cart-counter />
                    @auth
                        <a href="/admin" class="text-gray-600 hover:text-orange-700 font-medium">Administrar</a>
                    @else
                        <a href="/admin" class="text-gray-600 hover:text-orange-700 font-medium">Ingresar</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="py-8">
        {{ $slot }}
    </main>

    <footer class="bg-white border-t mt-12 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-500">
            <p>&copy; {{ date('Y') }} Consentidos & Traviesos. Todo lo que tu mejor amigo merece.</p>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
