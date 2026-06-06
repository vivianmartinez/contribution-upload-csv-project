<x-guest-layout>

    <div class="flex flex-col items-center justify-start px-4 py-10">

        <div class="w-full max-w-md bg-white shadow-xl rounded-2xl p-8 relative">

            <!-- Icono de usuario -->
            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto -mt-16 shadow-lg"
                style="background-color: #F9E79F;">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5.121 17.804A9 9 0 1118.88 17.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>

            <div>
                <!-- Tabs -->
                <!-- <div x-data="{ tab: 'login' }" class="mt-6"> -->
                <div x-data="{ tab: '{{ old('tab', 'login') }}' }" class="mt-6">


                    <div class="flex justify-center mb-6">
                        <button
                            @click="tab = 'login'"
                            :class="tab === 'login' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500'"
                            class="px-4 py-2 border-b-2 font-semibold tabs" data-tab="login">
                            Iniciar Sesión
                        </button>

                        <button
                            @click="tab = 'register'"
                            :class="tab === 'register' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500'"
                            class="px-4 py-2 border-b-2 font-semibold ml-4 tabs" data-tab="register">
                            Registrarse
                        </button>
                    </div>

                    <!-- FORMULARIO ÚNICO -->
                    <form
                        method="POST"
                        :action="tab === 'login' ? '{{ route('login') }}' : '{{ route('register') }}'">

                        @csrf

                        <!-- CONTENIDO FIJO -->
                        <div class="min-h-[200px]">

                            <!-- LOGIN -->
                            <template x-if="tab === 'login'">
                                <div>

                                    <!-- Email -->
                                    <div class=" mt-4">
                                        <div class="relative h-[40px] flex flex-col justify-center">
                                            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input
                                                type="email"
                                                name="email"
                                                placeholder="Correo electrónico"
                                                value="{{ old('email') && old('tab') === 'login' ? old('email') : '' }}"
                                                required
                                                class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 focus:ring-yellow-300 focus:border-yellow-400" />
                                        </div>
                                        <div data-error>
                                            <x-input-error :messages="$errors->get('email')" class="text-red-500 text-xs mt-1" />
                                        </div>
                                    </div>
                                    <!-- Password -->
                                    <div class=" mt-4">
                                        <div class="relative h-[40px] flex flex-col justify-center">
                                            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input
                                                type="password"
                                                name="password"
                                                placeholder="Contraseña"
                                                required
                                                class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 focus:ring-yellow-300 focus:border-yellow-400" />
                                        </div>
                                        <div data-error>
                                            <x-input-error :messages="$errors->get('password')" class="text-red-500 text-xs mt-1" />
                                        </div>
                                    </div>

                                    <!-- Recordar -->
                                    <div class="mt-4 h-[40px] flex flex-col justify-center">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-300">
                                            <span class="ml-2 text-sm text-gray-600">Recordar contraseña</span>
                                        </label>
                                    </div>

                                    <!-- Olvidaste -->
                                    <div class="mt-4 h-[40px] flex flex-col justify-center">
                                        @if (Route::has('password.request'))
                                        <div class="mt-2">
                                            <a href="{{ route('password.request') }}" class="text-sm text-yellow-600 hover:underline">
                                                ¿Has olvidado tu contraseña?
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </template>


                            <!-- REGISTRO -->
                            <!-- <div x-show="tab === 'register'"> -->
                            <template x-if="tab === 'register'">
                                <div>
                                    <!-- Nombre -->
                                    <div class=" mt-4">
                                        <div class="relative h-[40px] flex flex-col justify-center">
                                            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input
                                                type="text"
                                                name="name"
                                                placeholder="Nombre completo"
                                                value="{{ old('name') }}"
                                                required
                                                class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 focus:ring-yellow-300 focus:border-yellow-400" />
                                        </div>
                                        <x-input-error :messages="$errors->get('name')" class="text-red-500 text-xs mt-1" />
                                    </div>
                                    <!-- Email -->
                                    <div class=" mt-4">
                                        <div class="relative h-[40px] flex flex-col justify-center">
                                            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input
                                                type="email"
                                                name="email"
                                                placeholder="Correo electrónico"
                                                value="{{ old('email') && old('tab') === 'register' ? old('email') : '' }}"
                                                required
                                                class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 focus:ring-yellow-300 focus:border-yellow-400" />
                                        </div>
                                        <div data-error>
                                            <x-input-error :messages="$errors->get('email')" class="text-red-500 text-xs mt-1" />
                                        </div>
                                    </div>

                                    <!-- Password -->
                                    <div class=" mt-4">
                                        <div class="relative h-[40px] flex flex-col justify-center">
                                            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input
                                                type="password"
                                                name="password"
                                                placeholder="Contraseña"
                                                required
                                                class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 focus:ring-yellow-300 focus:border-yellow-400" />
                                        </div>
                                        <div data-error>
                                            <x-input-error :messages="$errors->get('password')" class="text-red-500 text-xs mt-1" />
                                        </div>
                                    </div>
                                    <!-- Confirm Password -->
                                    <div class=" mt-4">
                                        <div class="relative h-[40px] flex flex-col justify-center">
                                            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input
                                                type="password"
                                                name="password_confirmation"
                                                placeholder="Confirmar contraseña"
                                                required
                                                class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 focus:ring-yellow-300 focus:border-yellow-400" />
                                        </div>
                                        <div data-error>
                                            <x-input-error :messages="$errors->get('password_confirmation')" class="text-red-500 text-xs mt-1" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <input type="hidden" name="tab" :value="tab">

                        <!-- BOTÓN ÚNICO -->
                        <!-- <div x-data="{ loading: false }">
                            <button
                                type="submit"
                                @click=""
                                :disabled="loading"
                                class="w-full mt-6 text-gray-800 py-2 rounded-lg font-semibold flex items-center justify-center gap-2 transition disabled:opacity-70"
                                style="background-color: #F9E79F;">
                                <span x-show="!loading" x-text="tab === 'login' ? 'Iniciar Sesión' : 'Registrarse'"></span>

                                <span x-show="loading" x-cloak>
                                    <x-spinner />
                                </span>
                            </button>
                        </div> -->

                        <button
                            type="submit"
                            class="w-full mt-6 text-gray-800 py-2 rounded-lg font-semibold flex items-center justify-center gap-2 transition"
                            style="background-color: #F9E79F;">
                            <span x-text="tab === 'login' ? 'Iniciar Sesión' : 'Registrarse'"></span>
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>