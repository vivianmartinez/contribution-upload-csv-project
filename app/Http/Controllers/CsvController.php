<?php

namespace App\Http\Controllers;

use App\Http\Requests\CsvRequest; //Gestionamos la validacion aqui
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use App\Services\CsvService;
use SplFileObject;
use Illuminate\Http\File;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Requests\BuscarCsvRequest;
use Throwable;

/**
* Controlador encargado de gestionar el flujo completo de los archivos CSV.
* 
* Se encarga de la recepción, validacion, almacenamiento, visualizacion,
* filtrado y eliminacion segura de los documentos en el servidor.
* @author Ana Maria De la Cruz
*/
class CsvController extends Controller{

    protected $csvService;

    public function __construct(CsvService $csvService)
    {
        $this->csvService = $csvService;
    }

    /**
    * Recoge el archivo CSV y lo almacena en el servidor.
    *
    * @param  \App\Http\Requests\CsvRequest  $request Peticion con las reglas de validacion del archivo.
    * @return \Illuminate\Http\RedirectResponse Redirige a la ruta de visualización pasando la ruta del archivo.
    */
    public function leerCsv(CsvRequest $request) : RedirectResponse
    {
        try {
            $archivo= $request->file('anadirArchivo'); //Accedemos al archivo 
            $archivoAlmacenado =  $this->csvService->preprocesarCsv($archivo);

            return redirect()->route('mostrar.csv',
            [
                'archivo' => $archivoAlmacenado,
            ]);
            
         } catch (Throwable $e) {
            return redirect()->route('index')->withErrors($e->getMessage());
        }
    }

    /**
    * Coordina la comprobacion, lectura, filtrado y paginacion de un archivo CSV para su visualizacion.
    *
    * @param  \Illuminate\Http\BuscarCsvRequest  $request Contiene los parametros de busqueda y numero de filas por pagina.
    * @param  string  $archivo Ruta relativa del archivo guardado en el almacenamiento local.
    * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View Vista con los datos procesados o redireccion por error.
    */
    public function mostrarCsv(BuscarCsvRequest $request, string $archivo) : RedirectResponse|View
    {

        if (!Storage::exists($archivo)) { //Si el archivo no existe devuelve error al usuario en el inicio
            return redirect()->route('index')->withErrors('Archivo no encontrado');
        }

        $nombreArchivo= $this->csvService->obtenerNombreArchivo($archivo);

        try {
            $paginador = $this->csvService->procesarCsv($archivo, $request);
            if ($paginador->isEmpty() && !request('inputBuscar')) {
                return redirect()->route('index')->withErrors('El archivo está vacío o es inválido.');
            }
            $paginador->withQueryString();

            return view('visualizacionCsv', [//Enviamos la informacion a la vista donde se muestra
                'datos'          => $paginador,
                'archivo'        => $archivo,
                'nombreArchivo'  => $nombreArchivo,
            ]);
        } catch (Throwable $e) {
            return redirect()->route('index')->withErrors($e->getMessage());
        }
    }

    /**
    * Elimina un archivo CSV del almacenamiento si existe.
    *
    * @param  \Illuminate\Http\Request  $request Objeto de la peticion HTTP.
    * @param  string  $archivo Ruta del archivo que se desea eliminar.
    * @return \Illuminate\Http\RedirectResponse Redireccion a la ruta raiz 'index'.
    */
    public function eliminarCsv(Request $request, string $archivo) : RedirectResponse
    {

        if (Storage::exists($archivo)) {
            Storage::disk('local')->delete($archivo);
        }
       
        return redirect()->route('index');
    }

}