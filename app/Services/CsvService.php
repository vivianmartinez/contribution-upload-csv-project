<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use SplFileObject;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;


/**
 * Servicio CsvService
 * 
 * Proporciona funcionalidades para la manipulación de archivos CSV
 * @author Ana Maria De la Cruz
 * @package App\Services
 */
class CsvService {


    /**
     * Procesa un archivo CSV introducido por el usuario: normaliza su contenido y establece punto y coma(;) como su delimitador,
     * para finalmente crear un archivo con el que trabajar en el resto del programa.
     *
     * @param string $archivo Ruta relativa del archivo.
     * @return void
     */
    public function preprocesarCsv($archivo){

        $archivoInput =  $archivo->getRealPath(); //Seleccionamos la ruta real del archivo
        $separador = $this->detectarSeparador($archivoInput); //Detectamos el separador del archivo 

        //Creamos el objeto de lectura
        $objetoLectura = new \SplFileObject($archivoInput); 
        $objetoLectura->setFlags(SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE); 
        $objetoLectura->setCsvControl($separador); //explicamos que separador usa el archivo

        $stream = fopen('php://temp', 'r+');
    
        //Recorre el archivo de lectura fila por fila, normalizando el texto,y guarda en el nuevo archivo temporal cada fila con su separador(;)
        foreach ($objetoLectura as $fila) {
            if (!isset($fila[0])) continue; //Si no hay nada en la primera columna de la fila salta a la siguiente
            $filaPreprocesada = array_map([$this, 'normalizarTexto'], $fila);
            fputcsv($stream, $filaPreprocesada, ';');
        }
        rewind($stream);

        $archivoAlmacenado = 'csv/' . uniqid() . '_' . $archivo->getClientOriginalName();
        Storage::put($archivoAlmacenado, $stream);
        
        fclose($stream);
        $objetoLectura = null; 

        return $archivoAlmacenado; 
    }


    /**
     * Procesa un archivo CSV ya tratado y lo convierte en un array asociativo.
     *
     * @param string $archivoProcesado Ruta relativa del archivo dentro del Storage.
     * @return array Un array con las cabeceras y los datos mapeados.
     */
    public function procesarCsv($archivoPreprocesado,$busqueda = null, $filtrosColumnas = null, $porPagina = 10) {
        //Recibimos la ruta del archivo y creamos el objeto de lectura para poder recorrer la informacion
        $archivoRuta = Storage::path($archivoPreprocesado);
        $objetoLectura = new \SplFileObject($archivoRuta);
        $objetoLectura->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $objetoLectura->setCsvControl(';'); 

        $columnas = $objetoLectura->fgetcsv();//lee la primera fila del archivo para obtener la cabecera
        $todasLasFilas = [];

        foreach ($objetoLectura as $indice =>$fila) { 
            if ($indice === 0) continue;
            if (is_array($fila) && count($fila) === count($columnas)) {
                $todasLasFilas[] = array_combine($columnas, $fila); //guarda en un array asociativo los datos de las filas con sus cabeceras, asi podremos buscar
            }
        }

        //Pasamos los datos por el filtro de busqueda, si no hay busqueda muestra todos los datos del archivo
        $filasFiltradas = $this->filtrarFilas(
            $todasLasFilas,
            $busqueda,
            $filtrosColumnas
        );

        $paginador = $this->paginarCsv($filasFiltradas, $porPagina, request()); //Creamos el paginador
        
        //Devolvemos la informacion de las filas y la cabecera de la tabla
        return [
            'columnas' => $columnas, 
            'paginador'  => $paginador,
            'totalFilas' => count($filasFiltradas)
        ];
    }

    
    /**
     * Filtra el array de filas basandose en un termino de busqueda y una columna seleccionada.
     *
     * @param array $todasLasFilas Array asociativo con los datos del archivo.
     * @param string|null $textoBuscar El texto que el usuario desea encontrar.
     * @param string $columnaFiltro El nombre de la columna donde se realizara la busqueda.
     * @return array El array con las filas que coinciden con la busqueda.
     */
    public function filtrarFilas($todasLasFilas, $textoBuscar, $columnaFiltro) {
        if (empty($textoBuscar)){//Si el usuario no busca se muestra el archivo con toda la informacion
            return $todasLasFilas;
        }  
        $busqueda = mb_strtolower($textoBuscar, 'UTF-8');//Normalizamos el texto introducido en el buscador      
        
        //array_filter recorre cada fila. Si la función retorna true, la fila se guarda en $filasFiltradas
        $filasFiltradas = array_filter($todasLasFilas, function($fila) use ($busqueda, $columnaFiltro) {
            //Verificamos si la columna existe en la fila, si existe pasamos su contenido a minusculas y sino dejamos un texto vacio
            $valorFila = isset($fila[$columnaFiltro]) ? mb_strtolower($fila[$columnaFiltro], 'UTF-8') : '';
            return str_contains($valorFila, $busqueda); //Comprobamos si el texto de busqueda esta en la fila. Retorna true (se queda la fila) o false (se elimina).
        });

        return $filasFiltradas;// array_values elimina los huecos vacios del array para que no falle la paginacion, reordena.
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
        $texto = mb_convert_encoding($texto, 'UTF-8', mb_detect_encoding($texto, 'UTF-8, ISO-8859-1, Windows-1252', true));//Aseguramos que el texto sea UTF-8
        $texto = trim($texto); //limpiamos espacios en blanco por delante y detras
        $texto = str_replace(['-', '_'], ' ', $texto); // cambio los guiones por espacios
        $texto = preg_replace('/[^A-Za-z0-9 áéíóúÁÉÍÓÚüÜñÑ@.€]/u', '', $texto); // solo permite letras , numero y espacios
        $texto = ucwords(mb_strtolower($texto, 'UTF-8')); //todo el texto en minuscula, menos la primera letra en mayusculas
        return $texto;
    }


    /**
     * Convierte un array de datos en un objeto de paginacion de Laravel.
     * 
     * @param array $filas El conjunto total de filas a paginar.
     * @param int $porPagina Cantidad de registros que se mostraran en cada pagina.
     * @param \Illuminate\Http\Request $request La peticion actual para mantener los parametros de busqueda.
     * @return \Illuminate\Pagination\LengthAwarePaginator El objeto que genera la navegacion en la vista.
     */
    public function paginarCsv($filas, $porPagina, $request) {
   
        $paginaActual = LengthAwarePaginator::resolveCurrentPage();//Detectamos en que pagina esta el usuario
        
        $inicio = ($paginaActual - 1) * $porPagina; //Calcula donde empieza a mostrar los datos
        $datosPaginados = array_slice($filas, $inicio, $porPagina); // Extrae una parte de los datos usando el inicio calculado y la cantidad de datos que hay por pagina
        
        //Crea el objeto de Laravel para paginar
        $paginador = new LengthAwarePaginator(
            $datosPaginados, 
            count($filas), 
            $porPagina, 
            $paginaActual, 
            [
                'path' => $request->url(),
                'query' => $request->query(), // Mantiene los filtros de busqueda al cambiar de pagina
            ]
        );
        //Configura que se muestren 2 numeros de pagina a cada lado de la pagina actual en la barra de navegacion
        return $paginador->onEachSide(2);
    }

}
