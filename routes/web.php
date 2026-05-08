<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CsvController;

Route::get('/', function () {return view('index');})->name('index');

Route::post('/archivo', [CsvController::class, 'leerCsv'])->name('leer.csv'); 
Route::get('/archivo/{archivo}', [CsvController::class, 'mostrarCsv'])->name('mostrar.csv')->where('archivo', '.*');
Route::post('/eliminar/{archivo}', [CsvController::class, 'eliminarCsv'])->name('eliminar.csv')->where('archivo', '.*');

