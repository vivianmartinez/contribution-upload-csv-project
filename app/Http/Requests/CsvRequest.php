<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request encargado de la validacion de la subida de archivos CSV.
 * 
 * Centraliza las reglas de seguridad, tipos de archivo permitidos y los
 * mensajes de error personalizados para el formulario de carga.
 */
class CsvRequest extends FormRequest
{
    /**
    * Determina si el usuario actual esta autorizado a realizar esta peticion.
    *
    * @return bool Devuelve true si la peticion esta autorizada.
    */
    public function authorize(): bool
    {
        return true;
    }

    /**
    * Define las reglas de validacion que se aplicaran a los campos del formulario.
    *
    * @return array< string, ValidationRule|array<mixed>|string> Matriz con los campos y sus reglas de validacion.
    */
    public function rules(): array
    {
        return [
           'anadirArchivo' => 'required|file|mimes:csv,txt'
        ];
    }

     /**
     * Define los mensajes de error personalizados cuando una regla de validacion falla.
     *
     * @return array<string, string> Matriz asociativa con los errores.
     */
    public function messages(): array
    {
        return [
            'anadirArchivo.required' => 'Debes subir un archivo.',
            'anadirArchivo.file' => 'Debes subir un archivo válido.',
            'anadirArchivo.mimes' => 'El archivo debe ser tipo csv o txt.'
        ];
    }
}
