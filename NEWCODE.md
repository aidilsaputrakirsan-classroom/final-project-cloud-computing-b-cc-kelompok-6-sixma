# KODE FILE BARU DAN YANG BERUBAH

## 1. database/migrations/2025_11_15_140546_create_likes_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('image_id')->constrained('images')->onDelete('cascade');
            $table->timestamps();

            // Pastikan satu user hanya bisa like satu gambar sekali
            $table->unique(['user_id', 'image_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
```

## 2. app/Models/Like.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['user_id', 'image_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
```

## 3. app/Http/Controllers/LikeController.php
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    private function getSupabaseHeaders() {
        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
        ];
    }

    /**
     * Toggle like/unlike untuk gambar
     */
    public function toggle(Request $request, $imageId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id();

        try {
            // Cek apakah sudah like
            $existingLike = Http::withHeaders($this->getSupabaseHeaders())
                ->get(env('SUPABASE_REST_URL') . '/likes?user_id=eq.' . $userId . '&image_id=eq.' . $imageId)
                ->json();

            if (!empty($existingLike)) {
                // Unlike: hapus like
                Http::withHeaders($this->getSupabaseHeaders())
                    ->delete(env('SUPABASE_REST_URL') . '/likes?user_id=eq.' . $userId . '&image_id=eq.' . $imageId);

                return response()->json(['liked' => false, 'message' => 'Unlike berhasil']);
            } else {
                // Like: tambah like
                $data = [
                    'user_id' => $userId,
                    'image_id' => $imageId,
                    'created_at' => now()->toIso8601String()
                ];

                Http::withHeaders(array_merge($this->getSupabaseHeaders(), [
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=minimal'
                ]))->post(env('SUPABASE_REST_URL') . '/likes', $data);

                return response()->json(['liked' => true, 'message' => 'Like berhasil']);
            }

        } catch (\Exception $e) {
            Log::error('Like toggle error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }

    /**
     * Cek status like untuk gambar
     */
    public function check($imageId)
    {
        if (!Auth::check()) {
            return response()->json(['liked' => false]);
        }

        $userId = Auth::id();

        try {
            $existingLike = Http::withHeaders($this->getSupabaseHeaders())
                ->get(env('SUPABASE_REST_URL') . '/likes?user_id=eq.' . $userId . '&image_id=eq.' . $imageId)
                ->json();

            return response()->json(['liked' => !empty($existingLike)]);

        } catch (\Exception $e) {
            return response()->json(['liked' => false]);
        }
    }
}
```

## 4. routes/web.php (perubahan)
```php
    // RUTE LIKE
    Route::post('images/{imageId}/like', [LikeController::class, 'toggle'])->name('likes.toggle');
    Route::get('images/{imageId}/like/check', [LikeController::class, 'check'])->name('likes.check');
```

## 5. resources/views/images/index.blade.php (perubahan)
```html
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Artrium - Galeri Foto Alam</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
```

```html
                                {{-- Tombol Suka --}}
                                <button type="button"
                                        class="like-btn text-gray-400 hover:text-red-500 transition"
                                        data-image-id="{{ $image['id'] }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </button>
```

```html
    {{-- Script untuk Like --}}
    @auth
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const likeButtons = document.querySelectorAll('.like-btn');

            likeButtons.forEach(button => {
                const imageId = button.getAttribute('data-image-id');

                // Cek status like awal
                fetch(`/images/${imageId}/like/check`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.liked) {
                        button.classList.add('text-red-500');
                        button.classList.remove('text-gray-400');
                    }
                });

                // Event listener untuk toggle like
                button.addEventListener('click', function() {
                    fetch(`/images/${imageId}/like`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.liked) {
                            button.classList.add('text-red-500');
                            button.classList.remove('text-gray-400');
                        } else {
                            button.classList.add('text-gray-400');
                            button.classList.remove('text-red-500');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
    @endauth
```

## 6. app/Http/Controllers/ProfileController.php (perubahan)
```php
        $user = Auth::user();
        $userId = $user->id;
        $images = [];
        $likedImages = [];
```

```php
            // 🔹 Ambil gambar yang disukai user
            $likedQuery = env('SUPABASE_REST_URL') . '/likes?select=image_id,user_id,images(*)&user_id=eq.' . $userId;

            $likedResponse = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            ])->get($likedQuery);

            if ($likedResponse->successful()) {
                $likedData = $likedResponse->json();

                if (is_array($likedData)) {
                    $baseStorageUrl = rtrim(env('SUPABASE_URL'), '/') . '/storage/v1/object/public/images/';

                    foreach ($likedData as $like) {
                        if (isset($like['images'])) {
                            $image = $like['images'];
                            $image['image_url'] = $baseStorageUrl . ($image['image_path'] ?? '');
                            $likedImages[] = $image;
                        }
                    }
                }
            }
```

```php
            // Kirim data ke view
            return view('profile.index', [
                'user' => $user,
                'images' => $images,
                'likedImages' => $likedImages,
            ]);
```

## 7. resources/views/profile/index.blade.php (perubahan)
```html
            {{-- Tab Navigation --}}
            <div class="flex justify-center mb-8">
                <div class="bg-gray-900 rounded-lg p-1 flex">
                    <button id="tab-karya-dibuat" class="px-6 py-2 rounded-md text-white bg-yellow-400 font-semibold transition">
                        Karya Dibuat
                    </button>
                    <button id="tab-karya-disukai" class="px-6 py-2 rounded-md text-gray-400 hover:text-white transition">
                        Karya Disukai
                    </button>
                </div>
            </div>

            {{-- Content Karya Dibuat --}}
            <div id="content-karya-dibuat" class="tab-content">
                <h2 class="text-3xl font-bold text-center mb-8 text-white">Karya Dibuat</h2>
                <!-- ... existing content ... -->
            </div>

            {{-- Content Karya Disukai --}}
            <div id="content-karya-disukai" class="tab-content hidden">
                <h2 class="text-3xl font-bold text-center mb-8 text-white">Karya Disukai</h2>

                @if (count($likedImages) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @foreach ($likedImages as $image)
                            <div class="relative group overflow-hidden rounded-lg bg-gray-900 border border-gray-800 hover:border-yellow-400 transition">
                                <img src="{{ $image['image_url'] ?? '#' }}"
                                     alt="{{ $image['title'] }}"
                                     class="w-full card-image transform group-hover:scale-[1.05] transition duration-500">

                                <div class="p-4">
                                    <h3 class="font-semibold text-lg text-white">{{ $image['title'] }}</h3>
                                    <p class="text-sm text-gray-400">
                                        {{ $image['category']['name'] ?? 'Tanpa Kategori' }} |
                                        {{ \Carbon\Carbon::parse($image['created_at'])->translatedFormat('d M Y') }}
                                    </p>

                                    <div class="mt-3 flex justify-center">
                                        <a href="{{ route('images.show', $image['id']) }}"
                                           class="text-xs px-3 py-1 bg-gray-700 text-white rounded hover:bg-gray-600 font-semibold">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 text-xl py-10">
                        Anda belum menyukai karya apapun.
                    </p>
                @endif
            </div>
```

```html
    {{-- Script untuk Tab Navigation --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabDibuat = document.getElementById('tab-karya-dibuat');
            const tabDisukai = document.getElementById('tab-karya-disukai');
            const contentDibuat = document.getElementById('content-karya-dibuat');
            const contentDisukai = document.getElementById('content-karya-disukai');

            tabDibuat.addEventListener('click', function() {
                // Aktifkan tab dibuat
                tabDibuat.classList.add('bg-yellow-400', 'text-white');
                tabDibuat.classList.remove('text-gray-400');
                tabDisukai.classList.remove('bg-yellow-400', 'text-white');
                tabDisukai.classList.add('text-gray-400');

                // Tampilkan content dibuat
                contentDibuat.classList.remove('hidden');
                contentDisukai.classList.add('hidden');
            });

            tabDisukai.addEventListener('click', function() {
                // Aktifkan tab disukai
                tabDisukai.classList.add('bg-yellow-400', 'text-white');
                tabDisukai.classList.remove('text-gray-400');
                tabDibuat.classList.remove('bg-yellow-400', 'text-white');
                tabDibuat.classList.add('text-gray-400');

                // Tampilkan content disukai
                contentDisukai.classList.remove('hidden');
                contentDibuat.classList.add('hidden');
            });
        });
    </script>
