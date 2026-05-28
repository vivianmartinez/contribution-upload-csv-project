<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile; 
use SplFileObject;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator; 
use RuntimeException;
use InvalidArgumentException;
use LengthException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use OutOfBoundsException;



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
    * @param  UploadedFile  $archivo Archivo subido desde el formulario.
    * @return string Ruta relativa donde se ha guardado el nuevo archivo tratado.
    */
    public function preProcesarCsv(UploadedFile $archivo) : string
    {
        $archivoInput =  $archivo->getRealPath(); //Seleccionamos la ruta real del archivo
        if (!$archivoInput) {
            throw new InvalidArgumentException( "No se pudo acceder a la ruta temporal del archivo.");
        }

        $separador = $this->detectarSeparador($archivoInput); //Detectamos el separador del archivo 
        //Creamos el objeto de lectura
        $objetoLectura = new SplFileObject($archivoInput); 
        $objetoLectura->setFlags(SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE); 
        $objetoLectura->setCsvControl($separador); //explicamos que separador usa el archivo

        $stream = $this->normalizarCsv($objetoLectura);

        $archivoPreprocesado = 'csv/' . uniqid() . '_' . $archivo->getClientOriginalName();
        
        $guardado = Storage::writeStream($archivoPreprocesado, $stream);
        if (!$guardado){
            fclose($stream);
             throw new RuntimeException("No se pudo guardar el archivo preprocesado.");
        }
        
        fclose($stream);

        return $archivoPreprocesado; 
    }

    /**
    * Recorre el lector de CSV para generar un flujo de datos con la cabecera 
    * normalizada y las celdas aseguradas en codificacion UTF-8.
    *
    * @param  SplFileObject  $objetoLectura Puntero de lectura del archivo CSV original.
    * @return resource Puntero del stream temporal con los datos procesados.
    */
    public function normalizarCsv(SplFileObject $objetoLectura) : mixed
    {
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new RuntimeException("No se pudo asignar memoria para la lectura del archivo en la normalización.");
        }

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
    * @param  Request  $request Objeto de la petición HTTP con los filtros de busqueda.
    * @return LengthAwarePaginator Instancia de paginacion con los registros correspondientes.
    */
    public function procesarCsv(string $archivoPreprocesado,Request $request) : LengthAwarePaginator
    {
        if (!Storage::exists($archivoPreprocesado)) {
            throw new FileNotFoundException("El archivo solicitado no existe o ha expirado.");
        }

        $datosCsv = $this->convertirCsvEnArray($archivoPreprocesado);
        if (empty($datosCsv)) {
            throw new LengthException("El archivo CSV no contiene registros de datos para mostrar.");
        }

        $paginador = $this->paginarCsv($datosCsv, $request);

        return $paginador;
    }


    /**
    * Filtra una matriz de datos y construye el objeto de paginacion nativo de Laravel.
    *
    * @param  array  $datosCsv Matriz de datos asociativos del archivo CSV.
    * @param  Request  $request Peticion con el numero de filas e indice de pagina.
    * @return LengthAwarePaginator Paginador configurado con las URLs de navegacion.
    */
    public function paginarCsv(array $datosCsv,Request $request): LengthAwarePaginator
    {
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
    public function convertirCsvEnArray(string $archivoPreprocesado) : array
    {
        //Recibimos la ruta del archivo y creamos el objeto de lectura para poder recorrer la informacion
        $archivoRuta = Storage::path($archivoPreprocesado);
        $objetoLectura = new SplFileObject($archivoRuta);
        $objetoLectura->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $objetoLectura->setCsvControl(';'); 

        $columnas = $objetoLectura->fgetcsv();//lee la primera fila del archivo para obtener la cabecera
        if (!$columnas || empty(array_filter($columnas))) {
            throw new InvalidArgumentException("El archivo CSV no contiene una estructura de cabeceras válida."); 
        }

        if (count($columnas) !== count(array_unique($columnas))) {
            throw new OutOfBoundsException("El archivo CSV contiene nombres de columnas duplicados en la cabecera.");
        }

        $todasLasFilas = [];

        while (!$objetoLectura->eof()) {
            $fila = $objetoLectura->fgetcsv();

            if ($fila === [null] || $fila === false || empty(array_filter($fila))) {
                continue;
            }   
            if (count($fila) !== count($columnas)) {
                throw new LengthException("El archivo contiene líneas corruptas.");
            }

            $todasLasFilas[] = array_combine($columnas, $fila);
            
        }
        return  $todasLasFilas;
    }

    /**
    * Extrae y devuelve el nombre original del archivo eliminando el prefijo ID unico y su directorio.
    *
    * @param  string  $archivoPreprocesado Ruta del archivo almacenado 
    * @return string Nombre original limpio del archivo 
    */
    public function obtenerNombreArchivo(string $archivoPreprocesado) : string
    {
        //Sacamos el nombre del archivo de la ruta para tenerlo siempre en la vista
        $nombreRuta= basename($archivoPreprocesado); 
        $nombreArchivoFiltrado = substr($nombreRuta, strpos($nombreRuta, '_') + 1);
        return $nombreArchivoFiltrado;
    }
    
    /**
    * Filtra el array de filas basandose en un termino de busqueda y una columna seleccionada.
    *
    * @param  array  $datosCsv Matriz asociativa con las filas del documento.
    * @param  Request  $request Peticion con el campo a buscar y la columna filtro.
    * @return array Matriz filtrada unicamente con los registros coincidentes.
    */
    public function filtrarDatos(array $datosCsv,Request $request) : array
    {
        $textoBuscar = $request->get('inputBuscar');
        $columnaFiltro = $request->get('opcionesBuscar');
    
        if (empty($textoBuscar)){
            return $datosCsv;
        }  

        $columnas = array_keys(current($datosCsv) ?: []);
        if (!in_array($columnaFiltro, $columnas)) {
            throw new OutOfBoundsException("La columna seleccionada para filtrar no existe en el archivo."); 
        }
        
        $buscar = $this->quitarAcentos(mb_strtolower($textoBuscar, 'UTF-8'));

        $datosFiltrados = array_filter($datosCsv, function ($fila) use ($columnaFiltro, $buscar) {  
            $valor = $this->quitarAcentos(mb_strtolower($fila[$columnaFiltro], 'UTF-8'));
            return str_contains($valor, $buscar);
        });

        return array_values($datosFiltrados);
    }

    /**
    * Determina el separador de las columna (',' o ';') analizando la cabecera de la tabla.
    *
    * @param string $rutaAbsoluta Ruta completa hacia el archivo.
    * @return string Devuelve el caracter separador detectado.
    */
    public function detectarSeparador(string $rutaAbsoluta) : string
    {  
        //Verificamos que simbolo se repite mas veces en el encabezado de la tabla para saber cual es el separador del archivo
        $objetoLectura = new SplFileObject($rutaAbsoluta);
        $encabezado = $objetoLectura->fgets();
        if ($encabezado === false || trim($encabezado) === '') {
            throw new InvalidArgumentException("El archivo CSV está vacío o su primera línea es ilegible.");
        }

        $comas = substr_count($encabezado, ',');
        $puntoComas = substr_count($encabezado, ';');
        if ($comas === 0 && $puntoComas === 0) {
            throw new InvalidArgumentException("No se ha podido detectar un separador válido.");
        }

        $separador=($puntoComas > $comas) ? ';' : ',';
        return $separador;
    }

    /**
    * Limpia y formatea un texto para estandarizar su apariencia.
    *
    * @param string $texto El texto original a procesar.
    * @return string Devuelve el texto normalizado y formateado.
    */
    public function normalizarTexto(string $texto) : string
    {
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
    public function quitarAcentos(string $texto) : string
    {
        $buscar  = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'u', 'A', 'E', 'I', 'O', 'U', 'U'];
        return str_replace($buscar, $reemplazar, $texto);
    }
}
