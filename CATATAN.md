# Catatan Perubahan - Fitur Read dan Testing

## Tanggal: 5 November 2025

## Perubahan yang Dilakukan:

### 1. Konfigurasi Database
- **File**: `config/database.php`
- **Perubahan**: Mengubah default database connection dari 'pgsql' ke 'sqlite' untuk memudahkan development lokal.
- **Detail**: 
  - Line 19: `'default' => env('DB_CONNECTION', 'sqlite')`
  - Line 32: `'database' => env('DB_DATABASE', database_path('database.sqlite'))`

### 2. Migrasi Database
- **Command**: `php artisan migrate`
- **Hasil**: Berhasil membuat tabel-tabel berikut:
  - users
  - cache
  - jobs
  - images
  - categories

### 3. Seeding Data
- **Command**: `php artisan db:seed` dan `php artisan tinker --execute="App\Models\User::factory(10)->create();"`
- **Hasil**: Berhasil membuat 11 user dummy untuk testing fitur read.

### 4. Testing Fitur Read
- **Endpoint `/read`**: Mengembalikan semua data user (11 user) dalam format JSON.
- **Endpoint `/read/1`**: Mengembalikan data user spesifik berdasarkan ID.
- **Endpoint `/read/999`**: Mengembalikan error 404 untuk user yang tidak ada.

## Git Commit dan Push:
- **Branch**: feature/image
- **Commit Hash**: ca5d2ee
- **Message**: "Update database config to use SQLite for local development"
- **Status**: Berhasil di-push ke GitHub repository kelompok.

## Kesimpulan:
Fitur read telah berhasil dikembangkan, diuji, dan di-commit ke repository. Semua perubahan telah terintegrasi dengan baik dan siap untuk deployment atau pengembangan lanjutan.
