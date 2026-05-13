<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use SplFileObject;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;



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
        Storage::writeStream($archivoAlmacenado, $stream);
        
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
    public function procesarCsv($archivoPreprocesado,Request $request) {
        //Sacamos el nombre del archivo de la ruta para tenerlo siempre en la vista
        $nombreRuta= basename($archivoPreprocesado); 
        $nombreArchivoFiltrado = substr($nombreRuta, strpos($nombreRuta, '_') + 1);

        //Obtenermos los parametros 
        $textoBuscar = $request->get('inputBuscar');
        $columnaFiltro = $request->get('opcionesBuscar');
        $porPagina =(int) $request->get('opcionesVista', 10);
        $paginaActual = (int) LengthAwarePaginator::resolveCurrentPage();

         $textoBuscarNormalizado = !empty($textoBuscar) ? mb_strtolower($textoBuscar, 'UTF-8') : ''; //Normalizamos el texto introducido en el buscador     

        //Recibimos la ruta del archivo y creamos el objeto de lectura para poder recorrer la informacion
        $archivoRuta = Storage::path($archivoPreprocesado);
        $objetoLectura = new \SplFileObject($archivoRuta);
        $objetoLectura->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $objetoLectura->setCsvControl(';'); 

        //Contar todos los registros del archivo
        $columnas = $objetoLectura->fgetcsv();//lee la primera fila del archivo para obtener la cabecera
        $totalFilasFiltradas = 0;

        foreach ($objetoLectura as $indice => $fila) {
            if ($indice === 0) continue;
            if (is_array($fila) && count($fila) === count($columnas)) {
                $filaAsociativa = array_combine($columnas, $fila);
                if ($this->filtrarFilas($filaAsociativa, $textoBuscarNormalizado, $columnaFiltro)) {
                    $totalFilasFiltradas++;
                }
            }
        }

        //Gestionar la visualizacion de solo los datos necesarios en la vista
        $inicio = ($paginaActual - 1) * $porPagina;

        $filasFiltradas = [];
        $coincidencias = 0;

        $objetoLectura->rewind(); //Reiniciamos el objeto de lectura

        foreach ($objetoLectura as $indice => $fila) {
            if ($indice === 0) continue;
            if (is_array($fila) && count($fila) === count($columnas)) {
                $filaAsociativa = array_combine($columnas, $fila);//guarda en un array asociativo los datos de las filas con sus cabeceras, asi podremos buscar
                
                if ($this->filtrarFilas($filaAsociativa, $textoBuscarNormalizado, $columnaFiltro)) {

                    if ($coincidencias >= $inicio && count($filasFiltradas) < $porPagina) {
                        $filasFiltradas[] = $filaAsociativa;
                    }

                    $coincidencias++;
                    if (count($filasFiltradas) === $porPagina) {
                        break; 
                    }
                }

            }

        }

        $objetoLectura = null;
        $paginador = $this->paginarCsv($filasFiltradas, $totalFilasFiltradas, $porPagina, $paginaActual, $request); //Creamos el paginador
        
        //Devolvemos la informacion de las filas y la cabecera de la tabla
        return [
            'columnas' => $columnas, 
            'paginador'  => $paginador,
            'totalFilas' => $totalFilasFiltradas,
            'nombreOriginal' => $nombreArchivoFiltrado
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
    public function filtrarFilas($filaAsociativa, $textoBuscarNormalizado, $columnaFiltro) {
        if (empty($textoBuscarNormalizado)){//Si el usuario no busca devuelve true
            return true;
        }  

        $valorFila = isset($filaAsociativa[$columnaFiltro]) ? mb_strtolower($filaAsociativa[$columnaFiltro], 'UTF-8') : '';//Verificamos si la columna existe en la fila, si existe pasamos su contenido a minusculas y sino dejamos un texto vacio

        return str_contains($valorFila, $textoBuscarNormalizado); //Comprobamos si el texto de busqueda esta en la fila. Retorna true (se queda la fila) o false (se elimina).
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
    public function paginarCsv($datosPaginaActual, $totalFilas, $porPagina, $paginaActual, $request) {
   
        $paginador = new LengthAwarePaginator(
            $datosPaginaActual, 
            $totalFilas, 
            $porPagina, 
            $paginaActual, 
                [
                    'path' => $request->url(),
                    'query' => $request->query(), 
                ]
        );

        //Configura que se muestren 2 numeros de pagina a cada lado de la pagina actual en la barra de navegacion
        return $paginador->onEachSide(2);
    }

}
