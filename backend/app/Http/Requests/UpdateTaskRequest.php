<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date'    => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'title.max'          => 'Judul task maksimal 255 karakter.',
            'description.max'    => 'Deskripsi task maksimal 5000 karakter.',
            'due_date.date'      => 'Format tanggal due date tidak valid.',
        ];
    }
}
