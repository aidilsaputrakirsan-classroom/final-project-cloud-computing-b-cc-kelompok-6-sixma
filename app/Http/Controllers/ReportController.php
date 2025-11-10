<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Report; // Model Report
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function __construct()
    {
        // 1. Hapus Panggilan 'parent::__construct()'.
        // Ini menyelesaikan error 'Cannot call constructor'.
        
        // 2. Terapkan middleware
        $this->middleware('auth');
    }

    /**
     * Menerima dan menyimpan laporan baru.
     */
    public function store(Request $request, $imageId)
    {
        // 1. Validasi Input
        $request->validate([
            'reason_category' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        // 2. Buat Laporan di Database (menggunakan Model Report)
        Report::create([
            'user_id' => Auth::id(),
            'image_id' => $imageId,
            'reason_category' => $request->reason_category,
            'description' => $request->description,
            'status' => 'new', 
        ]);

        // 3. Redirect Kembali dengan Pesan Sukses
        return back()->with('success', 'Laporan Anda telah berhasil kami terima dan akan segera ditinjau oleh Admin.');
    }
}