<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Mengambil JWT Pengguna untuk operasi CUD.
     */
    private function getAuthHeaders() {
        $userJWT = Auth::user()->supabase_jwt ?? null;

        if (empty($userJWT)) {
            Log::error('JWT Pengguna Kosong saat operasi pelaporan.');
            return [
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/json'
            ];
        }

        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . $userJWT, 
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Menyimpan laporan baru ke database Supabase.
     * Rute: POST /images/{image}/report
     */
    public function store(Request $request, $image) // $image adalah ID gambar yang dilaporkan
    {
        // 1. Cek Autentikasi
        if (!Auth::check()) {
            return back()->with('error', 'Anda harus login untuk melaporkan konten.');
        }
        $user = Auth::user();
        $reporterId = $user->supabase_uuid; 
        
        $authHeaders = $this->getAuthHeaders();
        if (str_contains($authHeaders['Authorization'], env('SUPABASE_ANON_KEY'))) {
            return back()->with('error', 'Sesi login tidak lengkap. Harap logout dan login ulang.');
        }

        // 2. Validasi Input
        $request->validate([
            'reason' => 'required|string|max:100', // Alasan utama (dari form select)
            'details' => 'nullable|string|max:500', // Detail tambahan
        ]);

        try {
            $databaseUrl = env('SUPABASE_REST_URL');
            
            // 3. Persiapkan Data Laporan
            $reportData = [
                'image_id' => $image,
                'user_id' => $reporterId,
                'reason' => $request->reason,
                'details' => $request->details,
                'created_at' => now()->toIso8601String(),
                // 'status' => 'pending' (Asumsi Supabase punya default value)
            ];
            
            // 4. Proses POST Laporan
            $reportResponse = Http::withHeaders($authHeaders)
                                 ->post($databaseUrl . '/reports', $reportData);
            
            // 5. Cek Hasil
            if (!$reportResponse->successful()) {
                Log::error('❌ REPORT_INSERT_FAILURE:', ['status' => $reportResponse->status(), 'error_body' => $reportResponse->body()]);
                
                $message = $reportResponse->status() == 401 ? 'Akses ditolak (401). Policy RLS INSERT "reports" salah.' : 'Gagal mengirim laporan.';
                return back()->with('error', $message);
            }
            
            return back()->with('success', 'Laporan berhasil dikirim. Terima kasih telah membantu menjaga komunitas kami.');

        } catch (\Exception $e) {
            Log::error('❌ EXCEPTION FATAL SAAT MENGIRIM LAPORAN:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan laporan.');
        }
    }
}