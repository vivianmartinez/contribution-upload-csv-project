@if ($paginator->hasPages())
    <div class="paginacion" role="navigation" aria-label="Paginación">
        <div class="botonesPaginacion">

            <a class="botonPrimera {{ $paginator->onFirstPage() ? 'desactivado' : '' }}" 
                href="{{ $paginator->onFirstPage() ? '#' : $paginator->url(1) }}" 
                aria-label="Ir a la primera página">
                <x-icono flechas="primera" />
            </a>

            <a class="botonIzquierda {{ $paginator->onFirstPage() ? 'desactivado' : '' }}" 
                href="{{ $paginator->onFirstPage() ? '#' : $paginator->previousPageUrl() }}" 
                aria-label="Página anterior">
                <x-icono flechas="izquierda" />
            </a>

                <div class="numerosPaginacion">
                    @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $pagina => $url)
                        <a href="{{ $url }}" class="numero {{ $pagina == $paginator->currentPage() ? 'pagina-activa' : '' }}">
                            {{ $pagina }}
                        </a>
                    @endforeach
                </div>

            <a class="botonDerecha {{ !$paginator->hasMorePages() ? 'desactivado' : '' }}" 
                href="{{ !$paginator->hasMorePages() ? '#' : $paginator->nextPageUrl() }}" 
                aria-label="Página siguiente">
               <x-icono flechas="derecha" />
            </a>

            <a class="botonUltima {{ !$paginator->hasMorePages() ? 'desactivado' : '' }}" 
                href="{{ !$paginator->hasMorePages() ? '#' : $paginator->url($paginator->lastPage()) }}" 
                aria-label="Ir a la última página">
                <x-icono flechas="ultima" />
            </a>

        </div>

        <p class="contadorPaginas">
            Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
        </p>
    </div>
@endif
