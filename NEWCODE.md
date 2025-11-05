# NEWCODE.md - Kode Lengkap File yang Diubah/Ditambahkan

## 1. app/Http/Controllers/DeleteController.php

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class DeleteController extends Controller
{
    public function destroy($id)
    {
        // 1. Ambil data gambar dari Supabase untuk verifikasi kepemilikan
        $imageResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=id,user_id,image_path');

        $image = $imageResponse->json()[0] ?? null;

        if (!$image) {
            return back()->with('error', 'Gambar tidak ditemukan.');
        }

        // 2. Otorisasi: Hanya pemilik atau admin yang bisa hapus
        if (Auth::id() !== $image['user_id']) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menghapus karya ini.');
        }

        // 3. Hapus file dari Supabase Storage
        $deleteFile = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->delete(env('SUPABASE_STORAGE_URL') . '/object/public/images/' . $image['image_path']);

        // 4. Hapus record dari Supabase Database
        $deleteRecord = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->delete(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id);

        if ($deleteRecord->successful()) {
            return redirect()->route('images.index')->with('success', '✅ Karya berhasil dihapus!');
        } else {
            return back()->with('error', 'Gagal menghapus karya dari database.');
        }
    }
}
```

## 2. routes/web.php

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DeleteController;
// ❌ Hapus: use App\Http\Controllers\ReadController;
// ✅ Tambahkan: use App\Http\Controllers\Auth\AuthenticatedSessionController; (jika belum merge)

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [ImageController::class, 'index'])->name('gallery.index'); // ✅ Gunakan Galeri sebagai Homepage

// ========================================================
// 🖼️ ROUTES CRUD GAMBAR (Satu Entitas)
// Gunakan resource routing atau penamaan konsisten
// ========================================================

Route::middleware(['auth'])->group(function () {
    // CREATE GAMBAR
    Route::get('/images/create', [ImageController::class, 'create'])->name('images.create');
    Route::post('/images', [ImageController::class, 'store'])->name('images.store');

    // UPDATE GAMBAR (Anda) - Gunakan Route Model Binding
    // {image} adalah ID gambar yang dikirim ke controller
    Route::get('/images/{image}/edit', [ImageController::class, 'edit'])->name('images.edit');
    Route::patch('/images/{image}', [ImageController::class, 'update'])->name('images.update');

    // DELETE GAMBAR (Daffa)
    Route::delete('/images/{image}', [DeleteController::class, 'destroy'])->name('images.destroy');
});


// 📖 READ GAMBAR (Publik)
// Karena Read hanya menampilkan, tidak perlu middleware 'auth'
Route::get('/images', [ImageController::class, 'index'])->name('images.index');
Route::get('/images/{id}', [ImageController::class, 'show'])->name('images.show');
// ❌ Hapus routes lama: /read dan /read/{id}

// --------------------------------------------------------
// Jika sudah ada Routes Login/Register, biarkan di sini
// --------------------------------------------------------
```

## 3. tests/Feature/DeleteImageTest.php

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class DeleteImageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test delete image functionality.
     */
    public function test_user_can_delete_own_image(): void
    {
        // Buat user dummy
        $user = User::factory()->create();

        // Mock Supabase API responses
        Http::fake([
            env('SUPABASE_REST_URL') . '/images?id=eq.1&select=id,user_id,image_path' => Http::response([
                ['id' => 1, 'user_id' => $user->id, 'image_path' => 'test_image.jpg']
            ], 200),
            env('SUPABASE_STORAGE_URL') . '/object/public/images/test_image.jpg' => Http::response([], 200),
            env('SUPABASE_REST_URL') . '/images?id=eq.1' => Http::response([], 200),
        ]);

        // Login sebagai user
        $this->actingAs($user);

        // Kirim request DELETE ke route destroy
        $response = $this->delete(route('images.destroy', 1));

        // Assert redirect ke images.index dengan success message
        $response->assertRedirect(route('images.index'));
        $response->assertSessionHas('success', '✅ Karya berhasil dihapus!');
    }

    /**
     * Test user cannot delete other user's image.
     */
    public function test_user_cannot_delete_other_user_image(): void
    {
        // Buat dua user dummy
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Mock Supabase API responses - image milik user2
        Http::fake([
            env('SUPABASE_REST_URL') . '/images?id=eq.1&select=id,user_id,image_path' => Http::response([
                ['id' => 1, 'user_id' => $user2->id, 'image_path' => 'test_image.jpg']
            ], 200),
        ]);

        // Login sebagai user1
        $this->actingAs($user1);

        // Kirim request DELETE
        $response = $this->delete(route('images.destroy', 1));

        // Assert redirect back dengan error message
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda tidak memiliki izin untuk menghapus karya ini.');
    }

    /**
     * Test delete non-existent image.
     */
    public function test_delete_non_existent_image(): void
    {
        $user = User::factory()->create();

        // Mock Supabase API responses - image tidak ditemukan
        Http::fake([
            env('SUPABASE_REST_URL') . '/images?id=eq.999&select=id,user_id,image_path' => Http::response([], 200),
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('images.destroy', 999));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Gambar tidak ditemukan.');
    }
}
```

## 4. resources/views/images/index.blade.php

```blade
@extends('layouts.app') 

@section('content')

<div class="container mx-auto p-6">
    
    <div class="mb-8 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">Galeri Foto Alam</h1>
        
        @auth
            <a href="{{ route('images.create') }}" 
               class="px-4 py-2 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-600 transition duration-200">
                + Unggah Karya Baru
            </a>
        @endauth
        
        {{-- <form action="{{ route('images.index') }}" method="GET"> ... </form> --}}
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif


    @if (empty($images))
        <p class="text-center text-gray-500 mt-10">Belum ada karya yang diunggah. Jadilah yang pertama!</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            
            @foreach ($images as $image)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden transition transform hover:scale-[1.02]">
                
                <a href="{{ route('images.show', $image['id']) }}">
                    <img src="{{ env('SUPABASE_URL') }}/storage/v1/object/public/images/{{ $image['image_path'] }}" 
                         alt="{{ $image['title'] }}" 
                         class="w-full h-auto object-cover"
                         style="max-height: 400px;">
                </a>

                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1 truncate">{{ $image['title'] }}</h3>
                    
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        📍 {{ $image['location'] ?? 'Lokasi Tidak Diketahui' }} | 
                        #{{ $image['category'] ?? 'General' }}
                    </p>
                    
                    @auth
                        @if (Auth::id() == $image['user_id'])
                        <div class="mt-3 flex space-x-2">
                            <a href="{{ route('images.edit', $image['id']) }}" class="text-blue-500 hover:text-blue-600 text-sm">Edit</a>
                            
                            <form action="{{ route('images.destroy', $image['id']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-600 text-sm" onclick="return confirm('Yakin ingin menghapus karya ini?')">Hapus</button>
                            </form>
                        </div>
                        @endif
                    @endauth
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
