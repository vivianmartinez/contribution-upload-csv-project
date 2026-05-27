<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CsvController;

/**
 * Aqui se registran todas las rutas encargadas de la interfaz de usuario y 
 * el flujo de control de los archivos CSV (subida, renderizado y borrado).
 */

//Ruta GET: Carga la pantalla de bienvenida 
Route::get('/inicio', [CsvController::class, 'index'])->name('index');

//Ruta POST: Recibe el archivo en bruto desde el formulario y delega su almacenamiento al controlador
Route::post('/archivo', [CsvController::class, 'leerCsv'])->name('leer.csv'); 

//Ruta GET: Muestra la tabla interactiva con los datos del CSV filtrados y paginados
Route::get('/archivo/{archivo}', [CsvController::class, 'mostrarCsv'])->name('mostrar.csv')->where('archivo', '.*');

//Ruta GET: Elimina el archivo fisico del almacenamiento y redirige de vuelta a la pantalla de inicio
Route::get('/eliminar/{archivo}', [CsvController::class, 'eliminarCsv'])->name('eliminar.csv')->where('archivo', '.*');

