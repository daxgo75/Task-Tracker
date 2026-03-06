<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date'    => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'    => 'Kategori task wajib dipilih.',
            'category_id.exists'      => 'Kategori yang dipilih tidak ditemukan.',
            'title.required'          => 'Judul task wajib diisi.',
            'title.max'               => 'Judul task maksimal 255 karakter.',
            'description.max'         => 'Deskripsi task maksimal 5000 karakter.',
            'due_date.date'           => 'Format tanggal due date tidak valid.',
            'due_date.after_or_equal' => 'Due date tidak boleh sebelum hari ini.',
        ];
    }
}
