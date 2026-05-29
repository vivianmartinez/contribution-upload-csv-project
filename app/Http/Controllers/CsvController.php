<?php

namespace App\Http\Controllers;

use App\Http\Requests\CsvRequest; //Gestionamos la validacion aqui
use App\Http\Requests\BuscarCsvRequest; //Gestionamos la validacion aqui
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\CsvService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use InvalidArgumentException;
use LengthException;
use RuntimeException;
use Throwable;
use OutOfBoundsException;


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
    * Muestra la pantalla inicial de la aplicacion (Subir CSV).
    * @return \Illuminate\View\View
    */
    public function index() : View
    {
        return view('index');
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
            $archivoAlmacenado =  $this->csvService->preProcesarCsv($archivo);

            return redirect()->route('mostrar.csv',
            [
                'archivo' => $archivoAlmacenado,
            ]);

        } catch (RuntimeException $e) {// Captura fallos del servidor y escribe en logs, envia mensaje al usuario
            Log::error('Fallo al subir CSV: ' . $e->getMessage());
            return redirect()->route('index')->withErrors(['error_general' => 'Hubo un problema interno en el servidor al guardar el archivo.']);

        }catch (Throwable $e) { // Capturar otra excepcion no controlada 
            Log::critical('Fallo imprevisto y crítico al subir el CSV: ' . $e->getMessage());
            return redirect()->route('index')->withErrors(['error_general' => 'Ocurrió un error inesperado en el sistema. Por favor, inténtelo de nuevo más tarde.']);
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
        try {
            $nombreArchivo= $this->csvService->obtenerNombreArchivo($archivo);

            $paginador = $this->csvService->procesarCsv($archivo, $request);
            if ($paginador->isEmpty() && !$request->get('inputBuscar')) {
                return redirect()->route('index')->withErrors(['error_general' => 'El archivo está vacío o es inválido.']);
            }
            $paginador->withQueryString();

            return view('visualizacionCsv', [//Enviamos la informacion a la vista donde se muestra
                'datos'          => $paginador,
                'archivo'        => $archivo,
                'nombreArchivo'  => $nombreArchivo,
            ]);

        } catch (FileNotFoundException $e) {  // El archivo no existe o ha expirado
            return redirect()->route('index')->withErrors(['error_general' => $e->getMessage()]);

        } catch ( InvalidArgumentException | LengthException | OutOfBoundsException $e) {  // Los errores internos del CSV (columnas duplicadas, sin filas, separador inválido)
            return back()->withInput()->withErrors(['error_general' => $e->getMessage()]);

        } catch (RuntimeException $e) {   // Captura fallos del servidor y escribe en logs, envia mensaje al usuario
            Log::error('Fallo crítico al leer CSV: ' . $e->getMessage());
            return redirect()->route('index')->withErrors(['error_general' => 'No se pudo procesar el archivo debido a un error interno del servidor.']);

        }catch (Throwable $e) {    // Capturar otra excepcion no controlada 
            Log::critical('Error imprevisto e imprevisto al mostrar el CSV: ' . $e->getMessage());
            return redirect()->route('index')->withErrors(['error_general' => 'Ocurrió un error inesperado al procesar los datos.']);
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
        if (Storage::disk('local')->exists($archivo)) {
            Storage::disk('local')->delete($archivo);
        }
        return redirect()->route('index');
    }

}