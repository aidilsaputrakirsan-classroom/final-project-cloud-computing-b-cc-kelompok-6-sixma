# CATATAN2.md - Perubahan untuk Fitur Delete

## Tanggal: 2025-11-05

## Perubahan yang Dilakukan:

### 1. app/Http/Controllers/ReadController.php
- **Ditambahkan method `destroy($id)`**:
  - Mencari user berdasarkan ID menggunakan `User::find($id)`.
  - Jika user tidak ditemukan, mengembalikan response JSON dengan status 404 dan pesan "Data tidak ditemukan".
  - Jika ditemukan, menghapus user dengan `$data->delete()`.
  - Mengembalikan response JSON dengan status 200 dan pesan "Data berhasil dihapus".

### 2. routes/web.php
- **Ditambahkan route DELETE `/read/{id}`**:
  - Mengarah ke `ReadController@destroy` dengan nama route `read.destroy`.
  - Route ini memungkinkan penghapusan data user berdasarkan ID melalui HTTP DELETE request.

## Testing yang Dilakukan:

### Menggunakan php artisan tinker:
- **Sebelum delete**: User count = 11
- **Delete user ID 1**: Berhasil dihapus, count menjadi 10
- **Delete user ID 12**: Berhasil dihapus, count menjadi 10 (setelah create ulang)
- **Delete user ID 13**: Berhasil dihapus, count tetap 10

### Menggunakan curl/Invoke-WebRequest:
- **GET /read**: Mengembalikan 200 OK dengan data semua user (11 user awal, dikurangi yang dihapus via tinker).
- **GET /read/{id}**: Mengembalikan 200 OK jika user ada, 404 jika tidak ada.
- **DELETE /read/{id}**: Mengembalikan 419 unknown status (kemungkinan CSRF token issue di Laravel untuk web routes).

## Masalah yang Ditemukan:
- Route DELETE mengembalikan 419 error, yang menunjukkan masalah CSRF token. Untuk API routes, sebaiknya gunakan `Route::apiResource` atau tambahkan middleware `api` untuk menghindari CSRF.

## Rekomendasi:
- Pindahkan route delete ke `routes/api.php` untuk menghindari CSRF issues.
- Atau tambahkan `@csrf` exemption jika tetap di web routes.

## Status:
- Fitur delete berhasil diimplementasi di level controller dan route.
- Testing via tinker berhasil, namun HTTP DELETE request gagal karena CSRF.
- Perlu penyesuaian route untuk production use.

## Commit Hash:
- Belum di-commit, menunggu konfirmasi.
