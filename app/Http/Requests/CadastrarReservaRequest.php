<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CadastrarReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unidade_id' => 'required|integer|exists:unidade,id',
            'cliente_nome' => 'required|string|max:100',
            'cliente_email' => 'required|email|max:100',
        ];
    }
}
