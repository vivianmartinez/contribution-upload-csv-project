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
    <nav class="w-full bg-slate-50 border-b border-gray-200 py-3 px-6 flex justify-between items-center shadow-sm shrink-0 z-50">
        <span class="text-[#1F8BA0] font-medium">
            Usuario: <strong>{{ Auth::user()->name }}</strong>
        </span>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 text-sm bg-blue-100 text-[#1F8BA0] hover:bg-blue-200 px-3 py-1.5 rounded font-semibold transition">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-4 h-4 fill-current">
                    <path d="M569 337C578.4 327.6 578.4 312.4 569 303.1L425 159C418.1 152.1 407.8 150.1 398.8 153.8C389.8 157.5 384 166.3 384 176L384 256L272 256C245.5 256 224 277.5 224 304L224 336C224 362.5 245.5 384 272 384L384 384L384 464C384 473.7 389.8 482.5 398.8 486.2C407.8 489.9 418.1 487.9 425 481L569 337zM224 160C241.7 160 256 145.7 256 128C256 110.3 241.7 96 224 96L160 96C107 96 64 139 64 192L64 448C64 501 107 544 160 544L224 544C241.7 544 256 529.7 256 512C256 494.3 241.7 480 224 480L160 480C142.3 480 128 465.7 128 448L128 192C128 174.3 142.3 160 160 160L224 160z" />
                </svg>
                <span>Cerrar Sesión</span>
            </button>
        </form>
    </nav>

    <main class="contenido flex-grow">
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>