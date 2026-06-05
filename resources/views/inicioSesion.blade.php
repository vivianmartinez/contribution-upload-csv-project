<x-guest-layout >
   

    <div class="max-w-xl text-center px-4 mx-auto my-12">
        <h1 class="text-4xl font-bold text-[#0b5e6e] dark:text-white mb-6">Bienvenido</h1>
        
        @if (Route::has('login'))
            <div class="space-x-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-blue-100 text-[#0b5e6e] rounded hover:bg-blue-200 transition">Panel de Control</a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 bg-blue-300 text-[#0b5e6e] rounded hover:bg-blue-200 transition">Iniciar Sesión</a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-300 text-[#0b5e6e] rounded hover:bg-blue-200 transition dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white">Registrarse</a>
                    @endif
                @endauth
            </div>
        @endif
    </div>
</x-guest-layout>
