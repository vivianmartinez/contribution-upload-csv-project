<?php

namespace App\Http\Controllers;

use App\Http\Requests\CsvRequest; //Gestionamos la validacion aqui
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use App\Services\CsvService;
use SplFileObject;
use Illuminate\Http\File;

/**
* Controlador encargado de gestionar el flujo completo de los archivos CSV.
* 
* Se encarga de la recepción, validacion, almacenamiento, visualizacion,
* filtrado y eliminacion segura de los documentos en el servidor.
* @author Ana Maria De la Cruz
*/
class CsvController extends Controller{

    protected $csvService;

    public function __construct(CsvService $csvService) {
        $this->csvService = $csvService;
    }

    /**
    * Recoge el archivo CSV y lo almacena en el servidor.
    *
    * @param  \App\Http\Requests\CsvRequest  $request Peticion con las reglas de validacion del archivo.
    * @return \Illuminate\Http\RedirectResponse Redirige a la ruta de visualización pasando la ruta del archivo.
    */
    public function leerCsv(CsvRequest $request){
   
        $archivo= $request->file('anadirArchivo'); //Accedemos al archivo 
        $archivoAlmacenado =  $this->csvService->preprocesarCsv($archivo);

        return redirect()->route('mostrar.csv',
        [
            'archivo' => $archivoAlmacenado,
        ]);
    }

    /**
    * Coordina la comprobacion, lectura, filtrado y paginacion de un archivo CSV para su visualizacion.
    *
    * @param  \Illuminate\Http\Request  $request Contiene los parametros de busqueda y numero de filas por página.
    * @param  string  $archivo Ruta relativa del archivo guardado en el almacenamiento local.
    * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View Vista con los datos procesados o redireccion por error.
    */
    public function mostrarCsv(Request $request, $archivo){

        if (!Storage::exists($archivo)) { //Si el archivo no existe devuelve error al usuario en el inicio
            return redirect()->route('index')->withErrors('Archivo no encontrado');
        }

        $nombreArchivo= $this->csvService->obtenerNombreArchivo($archivo);

        $paginador = $this->csvService->procesarCsv($archivo, $request);
        if ($paginador->isEmpty() && !request('inputBuscar')) {
            return redirect()->route('index')->withErrors('El archivo está vacío o es inválido.');
        }

        return view('visualizacionCsv', [//Enviamos la informacion a la vista donde se muestra
            'datos'          => $paginador,
            'archivo'        => $archivo,
            'nombreArchivo'  => $nombreArchivo,
            'totalFilas'     => $paginador->total(),
            'filasPorPagina' => $paginador->perPage(),
        ]);
    }


    /**
    * Elimina un archivo CSV del almacenamiento si existe.
    *
    * @param  \Illuminate\Http\Request  $request Objeto de la peticion HTTP.
    * @param  string  $archivo Ruta del archivo que se desea eliminar.
    * @return \Illuminate\Http\RedirectResponse Redireccion a la ruta raiz 'index'.
    */
    public function eliminarCsv(Request $request,$archivo){

        if (Storage::exists($archivo)) {
            Storage::disk('local')->delete($archivo);
        }
       
        return redirect()->route('index');
    }

}