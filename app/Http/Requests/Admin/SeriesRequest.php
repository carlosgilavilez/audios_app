<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeriesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow all authenticated users for now
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],
            'comentario' => [
                'nullable',
                'string',
            ],
        ];

        if ($this->method() === 'POST') { // Store
            $rules['nombre'][] = Rule::unique('series', 'nombre');
        } elseif ($this->method() === 'PATCH' || $this->method() === 'PUT') { // Update
            $rules['nombre'][] = Rule::unique('series', 'nombre')->ignore($this->route('serie'));
        }

        return $rules;
    }
}
