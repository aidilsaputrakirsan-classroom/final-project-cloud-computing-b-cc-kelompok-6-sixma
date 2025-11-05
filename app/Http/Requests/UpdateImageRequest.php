<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImageRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berwenang untuk membuat permintaan ini.
     * Logic ini memastikan hanya pemilik karya atau Admin yang bisa update.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // 1. Dapatkan model Image dari route binding
        // Laravel secara otomatis mencari {image} di URL
        $image = $this->route('image');

        // Pastikan model image ditemukan dan user saat ini terautentikasi (login)
        if (!$image || !$this->user()) {
            return false;
        }

        // 2. Cek Otorisasi (Kepemilikan atau Role Admin)
        // Otorisasi dibatasi untuk pemilik karya dan admin
        return (
            $this->user()->id === $image->user_id || // User adalah pemilik gambar
            $this->user()->role === 'admin'           // User memiliki role Admin
        );
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Aturan validasi untuk data gambar yang diperbarui
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:255'],

            // Kategori harus sesuai dengan Enum yang ditetapkan
            'category' => ['required', 'in:Gunung,Pantai,Hutan,Langit,Perkotaan'],

            // image_file (Optional/Nullable saat update, karena tidak selalu ganti file)
            'image_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif', 'max:5000'], // max 5MB
        ];
    }

    /**
     * Pesan kesalahan khusus untuk aturan validasi.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'category.in' => 'Kategori harus salah satu dari: Gunung, Pantai, Hutan, Langit, atau Perkotaan.',
            'image_file.max' => 'Ukuran file gambar maksimal 5 MB.',
        ];
    }
}
