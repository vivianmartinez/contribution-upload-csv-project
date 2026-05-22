<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BuscarCsvRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    
    /**
    * Define las reglas de validacion que se aplican a la solicitud de busqueda.
    *
    * @return array<string, string> Reglas de validacion aplicables o un arreglo vacio si no se busca.
    */
    public function rules(): array
    {
        if ($this->query->has('botonBuscar')) {
            return [
                'inputBuscar'    => 'required',
                'opcionesBuscar' => 'required'
            ];
        }
        return [];
    }

    /**
     * Define los mensajes de error personalizados cuando una regla de validacion falla.
     *
     * @return array<string, string> Matriz asociativa con los errores.
     */
    public function messages(): array
    {
        return [
            'inputBuscar.required'    => 'Debes escribir un término para buscar.',
            'opcionesBuscar.required' => 'Selecciona un campo para realizar la búsqueda.'
        ];
    }
}
