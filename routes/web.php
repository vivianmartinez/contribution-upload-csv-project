<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CsvController;

Route::get('/', [AuthenticatedSessionController::class, 'create']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Ruta GET: Carga la pantalla de bienvenida 
    Route::get('/inicio', [CsvController::class, 'index'])->name('index');

    //Ruta POST: Recibe el archivo en bruto desde el formulario y delega su almacenamiento al controlador
    Route::post('/archivo', [CsvController::class, 'leerCsv'])->name('leer.csv'); 

    //Ruta GET: Muestra la tabla interactiva con los datos del CSV filtrados y paginados
    Route::get('/archivo/{archivo}', [CsvController::class, 'mostrarCsv'])->name('mostrar.csv')->where('archivo', '.*');

    //Ruta GET: Elimina el archivo fisico del almacenamiento y redirige de vuelta a la pantalla de inicio
    Route::get('/eliminar/{archivo}', [CsvController::class, 'eliminarCsv'])->name('eliminar.csv')->where('archivo', '.*');
});

require __DIR__.'/auth.php';





