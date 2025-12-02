<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImageRequest extends FormRequest
{
    // Otorisasi penuh akan dilakukan di Controller (untuk akses Supabase REST)
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer'], 
            'image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif', 'max:5000'], // max 5MB
        ];
    }
}