@if ($paginator->hasPages()) <!--Aqui gestionamos la barra de navegacion para que se pueda usar en todo la aplicacion -->
    <div class="paginacion">
            <div class="botonesPaginacion">
                @if (!$paginator->onFirstPage())
                    <a class="botonIzquierda" href="{{ $paginator->previousPageUrl() }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="30" height="30"><path d="M320 576C461.4 576 576 461.4 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 461.4 178.6 576 320 576zM199 303L279 223C288.4 213.6 303.6 213.6 312.9 223C322.2 232.4 322.3 247.6 312.9 256.9L273.9 295.9L424 295.9C437.3 295.9 448 306.6 448 319.9C448 333.2 437.3 343.9 424 343.9L273.9 343.9L312.9 382.9C322.3 392.3 322.3 407.5 312.9 416.8C303.5 426.1 288.3 426.2 279 416.8L199 336.8C189.6 327.4 189.6 312.2 199 302.9z"/></svg>
                    </a>
                @endif

                <div class="numerosPaginacion">
                    @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $pagina => $url)
                        <a href="{{ $url }}" class="numero {{ $pagina == $paginator->currentPage() ? 'pagina-activa' : '' }}">
                        {{ $pagina }}
                        </a>
                    @endforeach
                </div>

                @if ($paginator->hasMorePages())
                    <a class="botonDerecha" href="{{ $paginator->nextPageUrl() }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"width="30" height="30"><path d="M320 576C461.4 576 576 461.4 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 461.4 178.6 576 320 576zM361 417C351.6 426.4 336.4 426.4 327.1 417C317.8 407.6 317.7 392.4 327.1 383.1L366.1 344.1L216 344.1C202.7 344.1 192 333.4 192 320.1C192 306.8 202.7 296.1 216 296.1L366.1 296.1L327.1 257.1C317.7 247.7 317.7 232.5 327.1 223.2C336.5 213.9 351.7 213.8 361 223.2L441 303.2C450.4 312.6 450.4 327.8 441 337.1L361 417.1z"/></svg>  
                    </a>
                @endif
            </div>

            <p class="contadorPaginas">
                Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
            </p>
            
        </div>
@endif
