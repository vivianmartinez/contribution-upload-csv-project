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