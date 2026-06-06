@extends('layouts.app')

@section('title', config('app.name', 'SubirCsv'))

@push('styles')
<!--  CSS  -->
<link rel="stylesheet" href="{{ asset('css/pag_index.css') }}">
@endpush

@section('content')
<div class="contenedor_inicio">
    <form action="{{ route('leer.csv') }}" method="POST" enctype="multipart/form-data">
        @csrf <!--laravel bloquea la peticion si no verifica que el formulario es legitimo-->
        <!--
            <p> Selecciona un archivo CSV para poder verlo</p>
                <input id="entrada_archivo" class="entrada_archivo" type="file" name="anadirArchivo" accept=".csv" required>
                        <label for="entrada_archivo" class="icono_subida" id="mi_label">
                            <span id="icono_subir">
                                <svg xmlns="http://w3.org" viewBox="0 0 640 640" width="25" height="25" fill="white"><path d="M128 64C92.7 64 64 92.7 64 128L64 512C64 547.3 92.7 576 128 576L308 576C285.3 544.5 272 505.8 272 464C272 363.4 349.4 280.8 448 272.7L448 234.6C448 217.6 441.3 201.3 429.3 189.3L322.7 82.7C310.7 70.7 294.5 64 277.5 64L128 64zM389.5 240L296 240C282.7 240 272 229.3 272 216L272 122.5L389.5 240zM464 608C543.5 608 608 543.5 608 464C608 384.5 543.5 320 464 320C384.5 320 320 384.5 320 464C320 543.5 384.5 608 464 608zM480 400L480 448L528 448C536.8 448 544 455.2 544 464C544 472.8 536.8 480 528 480L480 480L480 528C480 536.8 472.8 544 464 544C455.2 544 448 536.8 448 528L448 480L400 480C391.2 480 384 472.8 384 464C384 455.2 391.2 448 400 448L448 448L448 400C448 391.2 455.2 384 464 384C472.8 384 480 391.2 480 400z"/></svg>
                            </span>
                        </label>
                <button class="boton_subir" id= "boton_subir" type="submit" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
        <path d="M560.3 110.5L420.5 110.5C372.4 110.5 330.6 143.8 320.1 190.8C309.5 143.8 267.8 110.5 219.7 110.5L80 110.5C53.5 110.5 32 132 32 158.5L32 404.3C32 430.8 53.5 452.3 80 452.3L169.7 452.3C271.9 452.3 302.4 476.7 317 527.3C317.7 530.1 322.2 530.1 323 527.3C337.7 476.7 368.2 452.3 470.3 452.3L560 452.3C586.5 452.3 608 430.8 608 404.3L608 158.6C608 132.2 586.7 110.7 560.3 110.5zM274 375.9C274 377.8 272.5 379.4 270.5 379.4L110.2 379.4C108.3 379.4 106.7 377.9 106.7 375.9L106.7 353C106.7 351.1 108.2 349.5 110.2 349.5L270.6 349.5C272.5 349.5 274.1 351 274.1 353L274.1 375.9L274 375.9zM274 315C274 316.9 272.5 318.5 270.5 318.5L110.2 318.5C108.3 318.5 106.7 317 106.7 315L106.7 292.1C106.7 290.2 108.2 288.6 110.2 288.6L270.6 288.6C272.5 288.6 274.1 290.1 274.1 292.1L274.1 315L274 315zM274 254.1C274 256 272.5 257.6 270.5 257.6L110.2 257.6C108.3 257.6 106.7 256.1 106.7 254.1L106.7 231.2C106.7 229.3 108.2 227.7 110.2 227.7L270.6 227.7C272.5 227.7 274.1 229.2 274.1 231.2L274.1 254.1L274 254.1zM533.3 375.8C533.3 377.7 531.8 379.3 529.8 379.3L369.5 379.3C367.6 379.3 366 377.8 366 375.8L366 352.9C366 351 367.5 349.4 369.5 349.4L529.9 349.4C531.8 349.4 533.4 350.9 533.4 352.9L533.4 375.8L533.3 375.8zM533.3 314.9C533.3 316.8 531.8 318.4 529.8 318.4L369.5 318.4C367.6 318.4 366 316.9 366 314.9L366 292C366 290.1 367.5 288.5 369.5 288.5L529.9 288.5C531.8 288.5 533.4 290 533.4 292L533.4 314.9L533.3 314.9zM533.3 254C533.3 255.9 531.8 257.5 529.8 257.5L369.5 257.5C367.6 257.5 366 256 366 254L366 231.2C366 229.3 367.5 227.7 369.5 227.7L529.9 227.7C531.8 227.7 533.4 229.2 533.4 231.2L533.4 254L533.3 254z" /></svg>
        <!--     <span>Mostrar</span>
                </button> 
-->



        <p>Selecciona un archivo CSV para poder verlo</p>

        <input id="entrada_archivo" class="entrada_archivo" type="file" name="anadirArchivo" accept=".csv" required>

        <button id="boton_dinamico" class="boton-dinamico" type="button" title="Subir archivo">
            <span id="icono_boton">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                    viewBox="0 0 640 640" fill="#333">
                    <path d="M128 64C92.7 64 64 92.7 64 128L64 512C64 547.3 92.7 576 128 576L308 576C285.3 544.5 272 505.8 272 464C272 363.4 349.4 280.8 448 272.7L448 234.6C448 217.6 441.3 201.3 429.3 189.3L322.7 82.7C310.7 70.7 294.5 64 277.5 64L128 64z" />
                </svg>
            </span>
            <span id="texto_boton">Subir</span>
        </button>



        @if ($errors->any())
        <div class="alerta-error">
            @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

    </form>
</div>

@endsection
@push('scripts')
<script src="{{ asset('js/boton_subida.js') }}"></script>
@endpush