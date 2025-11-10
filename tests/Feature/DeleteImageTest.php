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
