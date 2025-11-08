<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImageRequest extends FormRequest
{
    /**
     * Otorisasi penuh akan dilakukan di Controller (untuk akses Supabase REST)
     */
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Aturan validasi untuk proses update
     */
    public function rules(): array
    {
        return [
            // Semua field wajib, kecuali file gambar
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer'], 
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // file gambar optional (nullable)
        ];
    }
}