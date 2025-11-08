<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        // PENTING: Karena kita menggunakan Supabase REST, kita akan 
        // melakukan otorisasi penuh di Controller, dan me-return true di sini
        // untuk menghindari error. Logika keamanan utamanya ada di Controller.
        return true; 
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'category_id' => ['required'], // Asumsi ID category digunakan
            'image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif', 'max:5000'], // max 5MB
        ];
    }
}