<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'SubirCsv'))</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://bunny.net">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-omgYk..." crossorigin="anonymous" referrerpolicy="no-referrer" />


    <!--  Vite(CSS y JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

</head>

<body class="bg-blue-50 min-h-screen flex flex-col m-0 p-0">
    <nav class="w-full border-b border-gray-200 px-6 py-3 flex justify-between items-center shadow-sm"
        style="background-color: #F9E79F;">

        {{-- LOGO - INICIO --}}
        <a href="{{ route('index') }}" class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="white">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 3h6l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z" />
                <text x="7" y="17" font-size="6" fill="white" font-weight="bold">CSV</text>
            </svg>

            <span class="text-secondary font-semibold text-lg">
                Inicio
            </span>
        </a>


        <div x-data="{ open: false }" class="relative">
            <button
                @click="open = !open"
                class="flex items-center gap-3 focus:outline-none">
                <div class="w-9 h-9 rounded-full bg-white shadow flex items-center justify-center text-gray-700 font-bold">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>

                <span class="text-gray-800 font-medium">
                    {{ Auth::user()->name }}
                </span>

                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {{-- MENU DESPLEGABLE --}}
            <div
                x-show="open"
                @click.away="open = false"
                x-transition
                class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg py-2 z-50">
                <a href="{{ route('profile.edit') }}"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                    Mi perfil
                </a>


                <a href="#"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                    Configuración
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </nav>


    <main class="contenido flex-grow">
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>