@extends('layouts.app')

@section('title',  $nombreArchivo)

@section('content')
<!--BOTON VOLVER AL INICIO -->
    <a href="{{ route('eliminar.csv', ['archivo' => $archivo]) }}" class="botonVolver">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M268.2 82.4C280.2 87.4 288 99 288 112L288 192L400 192C497.2 192 576 270.8 576 368C576 481.3 494.5 531.9 475.8 542.1C473.3 543.5 470.5 544 467.7 544C456.8 544 448 535.1 448 524.3C448 516.8 452.3 509.9 457.8 504.8C467.2 496 480 478.4 480 448.1C480 395.1 437 352.1 384 352.1L288 352.1L288 432.1C288 445 280.2 456.7 268.2 461.7C256.2 466.7 242.5 463.9 233.3 454.8L73.3 294.8C60.8 282.3 60.8 262 73.3 249.5L233.3 89.5C242.5 80.3 256.2 77.6 268.2 82.6z"/></svg>
    </a>

<!--TITULOS -->
        <h4>Datos del Archivo:</h4>
        <h2>{{ $nombreArchivo  }}</h2>

<!--BARRA SUPERIOR: TIPO DE VISTA Y BUSQUEDA -->
    <div class="barraSuperior">
        <form method="GET" action="{{ route('mostrar.csv', ['archivo' => $archivo]) }}" class="buscador">
            <div class="controlVista">
                <label for="opcionesVista">Mostrar:</label>
                    <select name="opcionesVista" id="opcionesVista" class="opcionesVista" onchange="this.form.submit()">
                        @foreach([5, 10, 20, 50] as $vistas)
                            <option value="{{ $vistas }}" {{ $datos->perPage() == $vistas ? 'selected' : '' }}>
                                {{ $vistas }}
                            </option>
                        @endforeach
                    </select>
            </div>

            <div class="busquedaInputs">
             
                <input type="text" class="inputBuscar" name="inputBuscar" value="{{ request('inputBuscar') }}" placeholder="¿Qué quieres buscar?">    
          
                <select class="opcionesBuscar" name="opcionesBuscar" style="margin: 0;">
                    <option value="" {{ request('opcionesBuscar') == '' ? 'selected' : '' }}>
                        Seleccione una opción
                    </option>
                    @foreach($datos->cabecera as $nombreColumna)
                        <option value="{{ $nombreColumna }}" {{ request('opcionesBuscar') == $nombreColumna ? 'selected' : '' }}>
                            {{ $nombreColumna }}
                        </option>
                    @endforeach
                </select>
                   
                <button type="submit" name="botonBuscar">Buscar</button>
                
                {{-- 💡 CORREGIDO: Cambiada la clase 'refrescar' por 'btn-refrescar' para vincularse a tu CSS --}}
                <a href="{{ route('mostrar.csv', ['archivo' => $archivo]) }}" class="btn-refrescar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M3 21v-5h5"></path></svg>
                </a>

                 <div class="contenedorErrores">
                    @error('inputBuscar')
                        <span class="errorBuscadorTexto">{{ $message }}</span>
                    @enderror

                    @error('opcionesBuscar')
                        <span class="errorBuscadorTexto">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </form>
    </div>

<!--TABLA -->
    <table class="tabla">
        <thead>
            <tr>
                @foreach ($datos->cabecera as $col)
                    <th>{{ $col }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse ($datos as $row)
                <tr>
                    @foreach ($row as $valor)
                        <td>{{ $valor }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                   <td colspan="{{ count($datos->cabecera) }}" style="text-align: center; padding: 20px;">
                    No se encontraron registros.
                   </td>
                </tr>
            @endforelse
        </tbody>
    </table>

<!--BARRA INFERIOR:CONTADOR Y PAGINACION -->
    <div class="barraInferior"> 
        <p class="contadorFilas">
            {{ $datos->total() }} registros
        </p>

        <div class="paginacion">
          {{ $datos->links('partials.paginacion') }}
        </div>
    </div>
@endsection
