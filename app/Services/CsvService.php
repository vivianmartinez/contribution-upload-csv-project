<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use SplFileObject;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str; 



/**
* Servicio CsvService
* 
* Proporciona funcionalidades para la manipulacion de archivos CSV
* @author Ana Maria De la Cruz
* @package App\Services
*/
class CsvService {


    /**
    * Preprocesa un archivo CSV subido: gestiona su lectura, detecta el separador y
    * delega la normalizacion para guardarlo finalmente en el almacenamiento.
    *
    * @param  \Illuminate\Http\UploadedFile  $archivo Archivo subido desde el formulario.
    * @return string Ruta relativa donde se ha guardado el nuevo archivo tratado.
    */
    public function preProcesarCsv($archivo){

        $archivoInput =  $archivo->getRealPath(); //Seleccionamos la ruta real del archivo
        $separador = $this->detectarSeparador($archivoInput); //Detectamos el separador del archivo 

        //Creamos el objeto de lectura
        $objetoLectura = new \SplFileObject($archivoInput); 
        $objetoLectura->setFlags(SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE); 
        $objetoLectura->setCsvControl($separador); //explicamos que separador usa el archivo

        $stream = $this->normalizarCsv($objetoLectura);

        $archivoAlmacenado = 'csv/' . uniqid() . '_' . $archivo->getClientOriginalName();
        Storage::writeStream($archivoAlmacenado, $stream);
        
        fclose($stream);
        $objetoLectura = null; 

        return $archivoAlmacenado; 
    }

    /**
    * Recorre el lector de CSV para generar un flujo de datos con la cabecera 
    * normalizada y las celdas aseguradas en codificacion UTF-8.
    *
    * @param  \SplFileObject  $objetoLectura Puntero de lectura del archivo CSV original.
    * @return resource Puntero del stream temporal con los datos procesados.
    */
    public function normalizarCsv($objetoLectura){

        $stream = fopen('php://temp', 'r+');

        foreach ($objetoLectura as $indice => $fila) {
       
            if (is_array($fila) && array_filter($fila) !== []) {
                
                $filaProcesada = array_map(function($texto) {
                    return mb_convert_encoding($texto, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
                }, $fila);

                if ($indice === 0) {
                    $filaProcesada = array_map([$this, 'normalizarTexto'], $filaProcesada);
                }

                fputcsv($stream, $filaProcesada, ';');
            }
        }

        rewind($stream);
        return $stream;
    }

    /**
    * Procesa un archivo CSV ya preprocesado transformandolo en un array asociativo paginado.
    *
    * @param  string  $archivoPreprocesado Ruta relativa del archivo guardado en el servidor.
    * @param  \Illuminate\Http\Request  $request Objeto de la petición HTTP con los filtros de busqueda.
    * @return \Illuminate\Pagination\LengthAwarePaginator Instancia de paginacion con los registros correspondientes.
    */
    public function procesarCsv($archivoPreprocesado,Request $request) {

        $datosCsv = $this->convertirCsvEnArray($archivoPreprocesado);
        $paginador = $this->paginarCsv($datosCsv, $request);

        return $paginador;
    }


    /**
    * Filtra una matriz de datos y construye el objeto de paginacion nativo de Laravel.
    *
    * @param  array  $datosCsv Matriz de datos asociativos del archivo CSV.
    * @param  \Illuminate\Http\Request  $request Peticion con el numero de filas e indice de pagina.
    * @return \Illuminate\Pagination\LengthAwarePaginator Paginador configurado con las URLs de navegacion.
    */
    public function paginarCsv($datosCsv,Request $request){

        $cabecera = !empty($datosCsv) ? array_keys(current($datosCsv)) : [];
        $datosFiltrados = $this->filtrarDatos($datosCsv, $request);

        $porPagina =(int) $request->get('opcionesVista', 10);
        $paginaActual = (int) LengthAwarePaginator::resolveCurrentPage();

        $inicio = ($paginaActual - 1) * $porPagina;
        $datosPaginados = array_slice($datosFiltrados, $inicio, $porPagina);
     
        //Crea el objeto de Laravel para paginar
        $paginador = new LengthAwarePaginator(
            $datosPaginados, 
            count($datosFiltrados), 
            $porPagina, 
            $paginaActual, 
            [
                'path' => $request->url(),
                'query' => $request->query(), 
            ]
        );
        $paginador->cabecera = $cabecera;

        return $paginador->onEachSide(2);
    }

    /**
     * Lee un archivo CSV del almacenamiento y combina su cabecera con cada fila para armar un array asociativo.
     *
     * @param  string  $archivoPreprocesado Ruta del archivo dentro de la estructura de Storage.
     * @return array Matriz asociativa donde cada clave es el nombre de su respectiva columna.
     */
    public function convertirCsvEnArray($archivoPreprocesado){

        //Recibimos la ruta del archivo y creamos el objeto de lectura para poder recorrer la informacion
        $archivoRuta = Storage::path($archivoPreprocesado);
        $objetoLectura = new \SplFileObject($archivoRuta);
        $objetoLectura->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $objetoLectura->setCsvControl(';'); 

        $columnas = $objetoLectura->fgetcsv();//lee la primera fila del archivo para obtener la cabecera
        if (!$columnas || empty($columnas)) {
            return []; 
        }

        $todasLasFilas = [];

        while (!$objetoLectura->eof()) {
            $fila = $objetoLectura->fgetcsv();
            if (is_array($fila) && count($fila) === count($columnas)) {
                $todasLasFilas[] = array_combine($columnas, $fila);
            }
        }

        return  $todasLasFilas;
    }

    /**
    * Extrae y devuelve el nombre original del archivo eliminando el prefijo ID unico y su directorio.
    *
    * @param  string  $archivoPreprocesado Ruta del archivo almacenado 
    * @return string Nombre original limpio del archivo 
    */
    public function obtenerNombreArchivo($archivoPreprocesado){
        //Sacamos el nombre del archivo de la ruta para tenerlo siempre en la vista
        $nombreRuta= basename($archivoPreprocesado); 
        $nombreArchivoFiltrado = substr($nombreRuta, strpos($nombreRuta, '_') + 1);
        return $nombreArchivoFiltrado;
    }
    
    /**
    * Filtra el array de filas basandose en un termino de busqueda y una columna seleccionada.
    *
    * @param  array  $datosCsv Matriz asociativa con las filas del documento.
    * @param  \Illuminate\Http\Request  $request Peticion con el campo a buscar y la columna filtro.
    * @return array Matriz filtrada unicamente con los registros coincidentes.
    */
    public function filtrarDatos($datosCsv,Request $request) {
       
        $textoBuscar = $request->get('inputBuscar');
        $columnaFiltro = $request->get('opcionesBuscar');
    
        if (empty($textoBuscar)){
                return $datosCsv;
            }  

        $buscar = $this->quitarAcentos(mb_strtolower($textoBuscar, 'UTF-8'));

        $datosFiltrados = array_filter($datosCsv, function ($fila) use ($columnaFiltro, $buscar) {
            if (!isset($fila[$columnaFiltro])) {
                return false;
            }
            
            $valor = $this->quitarAcentos(mb_strtolower($fila[$columnaFiltro], 'UTF-8'));
            return str_contains($valor, $buscar);
        });

        return $datosFiltrados;
    }

    /**
    * Determina el separador de las columna (',' o ';') analizando la cabecera de la tabla.
    *
    * @param string $rutaAbsoluta Ruta completa hacia el archivo.
    * @return string Devuelve el caracter separador detectado.
    */
    public function detectarSeparador($rutaAbsoluta){  
        //Verificamos que simbolo se repite mas veces en el encabezado de la tabla para saber cual es el separador del archivo
        $objetoLectura = new SplFileObject($rutaAbsoluta);
        $encabezado = $objetoLectura->fgets();

        $comas = substr_count($encabezado, ',');
        $puntoComas = substr_count($encabezado, ';');
        $separador=($puntoComas > $comas) ? ';' : ',';
        return $separador;
    }

    /**
    * Limpia y formatea un texto para estandarizar su apariencia.
    *
    * @param string $texto El texto original a procesar.
    * @return string Devuelve el texto normalizado y formateado.
    */
    public function normalizarTexto($texto){
        $texto = trim($texto); //limpiamos espacios en blanco por delante y detras
        $texto = str_replace(['-', '_'], ' ', $texto); // cambio los guiones por espacios
        $texto = preg_replace('/[^A-Za-z0-9 áéíóúÁÉÍÓÚüÜñÑ@.€]/u', '', $texto); // solo permite letras , numero y espacios
        $texto = mb_convert_case(mb_strtolower($texto, 'UTF-8'), MB_CASE_TITLE, "UTF-8");  //todo el texto en minuscula, menos la primera letra en mayusculas
        return $texto;
    }

     /**
    * Reemplaza las vocales con tildes o dieresis por sus equivalentes simples en caracteres planos.
    *
    * @param  string  $texto Texto original con acentos.
    * @return string Texto limpio mapeado solo con caracteres planos.
    */
    public function quitarAcentos($texto) {
        
        $buscar  = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'u', 'A', 'E', 'I', 'O', 'U', 'U'];
        return str_replace($buscar, $reemplazar, $texto);
    }
}
