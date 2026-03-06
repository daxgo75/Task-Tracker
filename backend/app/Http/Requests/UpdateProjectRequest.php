<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status'      => ['sometimes', 'in:active,archived'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max'        => 'Nama project maksimal 255 karakter.',
            'description.max' => 'Deskripsi project maksimal 2000 karakter.',
            'status.in'       => 'Status project harus berupa active atau archived.',
        ];
    }
}
