# Artrium: Galeri Digital Alam Indonesia 🌿

**Dokumentasi Proyek Akhir: Platform Galeri Digital berbasis Livewire dan Supabase.**

![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat&logo=laravel&logoColor=white)  
![Livewire](https://img.shields.io/badge/Livewire-3.5-4E56A6?style=flat&logo=livewire&logoColor=white)  
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)  
![License](https://img.shields.io/badge/License-MIT-yellow.svg). (https://opensource.org/licenses/MIT)

Kami mengembangkan Artrium sebagai platform web untuk mengeksplorasi, mengunggah, dan mengelola fotografi alam Indonesia, bertujuan untuk mendukung apresiasi seni dan interaksi komunitas pengguna.

## 📚 Daftar Isi

1. [Ikhtisar Fungsionalitas](#1-ikhtisar-fungsionalitas)
2. [Stack Teknologi & Arsitektur](#2-stack-teknologi--arsitektur)
3. [Persyaratan Teknis](#3-persyaratan-teknis)
4. [Panduan Instalasi Lokal](#4-panduan-instalasi-lokal)
5. [Konfigurasi Supabase](#5-konfigurasi-supabase)
6. [Struktur Kode Kunci](#6-struktur-kode-kunci)
7. [Lisensi dan Credits](#7-lisensi-dan-credits)

---

## 1. Ikhtisar Fungsionalitas

Kami merancang Artrium dengan fokus pada interaksi pengguna dan manajemen konten visual secara *full-stack*:

* **Galeri & Navigasi:** Kami menyediakan tampilan foto publik yang dinamis dengan fungsionalitas **Search & Filter** yang responsif.
* **Manajemen Konten:** Kami memberikan kontrol penuh kepada pengguna atas operasi **Upload**, **Edit**, dan **Delete** foto.
* **Interaksi Komunitas:** Kami mengimplementasikan fitur **Like** dan **Komentar** pada setiap foto.
* **Moderasi:** Kami menyediakan fitur **Report** konten bermasalah, yang logikanya diolah melalui `ReportController` untuk moderasi admin.
* **Notifikasi *Real-time***: Kami memastikan sistem memiliki notifikasi terpusat untuk *update* interaksi.
* **Desain:** Desain kami adalah **Responsif** dan mendukung tema **Dark Mode** dinamis.

---

## 2. Stack Teknologi & Arsitektur

Kami mengandalkan kombinasi Laravel, Livewire, dan Supabase untuk arsitektur *full-stack* proyek ini.

### A. Backend & Database
Proyek kami mengandalkan Supabase sebagai *backend-as-a-service* untuk data (PostgreSQL) dan *storage* file:

| Komponen | Spesifikasi | Keterangan |
| :--- | :--- | :--- |
| **PHP Framework** | Laravel 12.0 | Kerangka kerja utama yang kami gunakan. |
| **Full-stack** | Livewire 3.5 | Kami gunakan untuk antarmuka pengguna yang reaktif. |
| **Database** | **PostgreSQL (Supabase)** | Database relasional utama kami (`DB_CONNECTION=pgsql`). |
| **Storage & API** | **Supabase API** | Menggunakan API khusus untuk akses REST dan *Storage*. |

### B. Struktur Controller Kunci
Sistem logika utama kami diatur melalui *Controller* yang terdapat pada *path* `app/Http/Controllers`:

* `ImageController.php`: Bertanggung jawab menangani operasi **CRUD** untuk foto.
* `LikeController.php`: Mengelola logika interaksi *Like* pada foto.
* `CommentController.php`: Mengelola pembuatan dan pemrosesan komentar.
* `ReportController.php`: Mengatur pengiriman dan pemrosesan laporan konten.
* `NotificationController.php`: Mengelola notifikasi pengguna.
* `ProfileController.php`: Mengelola profil dan pengaturan pengguna.
* `Auth Controllers`: Kami menggunakan standar Laravel/Breeze untuk proses otentikasi.

---

## 3. Persyaratan Teknis

Untuk memastikan proyek berjalan optimal, lingkungan pengembangan atau produksi harus memenuhi:

* **PHP:** `>= 8.2`
* **Tools:** **Composer**, **Node.js & NPM**
* **Database:** Koneksi ke **PostgreSQL** (melalui Supabase)
* **Web Server:** Apache atau Nginx

---

## 4. Panduan Instalasi Lokal

Kami menyajikan langkah-langkah untuk menjalankan Artrium di lingkungan lokal:

### 1. Clone Repository
```bash
git clone [URL-REPOSITORY-ANDA]
cd [folder-proyek-artrium]
````

### 2\. Instalasi Dependencies

Kami menggunakan Composer dan NPM untuk mengelola *dependencies*:

```bash
# Instalasi PHP
composer install

# Instalasi Node
npm install
```

### 3\. Setup Environment

Kami menyalin `.env.example` dan membuat *application key*:

```bash
# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4\. Build Assets & Storage Link

Kami mengompilasi aset dan menyiapkan *storage*:

```bash
# Buat symbolic link storage (jika diperlukan untuk penyimpanan lokal)
php artisan storage:link

# Compile assets frontend
npm run build
# Untuk development: npm run dev
```

### 5\. Jalankan Server

Aplikasi akan dapat diakses pada `http://localhost:8000`.

```bash
php artisan serve
```

-----

## 5. Konfigurasi Supabase

Agar proyek kami dapat terhubung ke database dan layanan *storage* eksternal Supabase, kami telah mengonfigurasi `.env` dengan kredensial yang relevan:

```env
# Database Supabase (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=aws-1-ap-southeast-1.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.tfrucxlrxpqxskuxmmeb
DB_PASSWORD=artrium06-2025

# Supabase API Keys & URLs
SUPABASE_URL=[https://tfrucxlrxpqxskuxmmeb.supabase.co](https://tfrucxlrxpqxskuxmmeb.supabase.co) 
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InRmcnVjeGxyeHBxeHNrdXhtbWViIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjE2NjIyNjUsImV4cCI6MjA3NzAzODI2NX0.Tj_ZmDz5V2WPiQ2FipHXiuZJ8qrPnYhHP0KhRXAy6B0
SUPABASE_TABLE=galeri

SUPABASE_REST_URL=${SUPABASE_URL}/rest/v1
SUPABASE_STORAGE_URL=${SUPABASE_URL}/storage/v1
```

-----

## 6. Struktur Kode Kunci

### Endpoint Logika Utama

  * **Galeri / Homepage:** Ditangani oleh `Controller.php` atau Komponen Livewire yang memuat `home.blade.php` atau `welcome.blade.php`.
  * **Manajemen Foto:** Logika CRUD berada di `ImageController.php`.
  * **Moderasi Admin:** Fungsi utama berada di `ReportController.php` dan tampilan *dashboard* kami terletak di `admin/dashboard.blade.php`.

### Views Utama

Kami menggunakan *views* kunci berikut:

  * `resources/views/home.blade.php` atau `welcome.blade.php`: Halaman utama dan Galeri.
  * `resources/views/admin/dashboard.blade.php`: Tampilan panel administrasi/moderasi.

-----

## 7. Lisensi dan Credits

### Lisensi

Proyek Artrium dilisensikan di bawah **[MIT License](https://www.google.com/search?q=LICENSE)**.

### Credits

Kami berterima kasih kepada *framework* dan *tools* yang digunakan dalam pengembangan proyek kami:

  * **Laravel Framework**
  * **Livewire**
  * **Supabase**

## 7. Pembagian Tugas Tim

Kami melaksanakan proyek ini dengan pembagian tugas utama sebagai berikut:

| Fitur / Modul | Penanggung Jawab Utama | Keterangan |
| :--- | :--- | :--- |
| **Create (Unggah)** | Kirana | Implementasi `ImageController::store` dan *form* upload. |
| **Read (Detail) & Like** | Darwis | Implementasi `ImageController::show` dan `LikeController`. |
| **Update, Delete & Auth** | Ria | Implementasi `ImageController::update/destroy` dan Auth Controllers. |
| **Home, Dashboard Admin** | Aldo | Desain dan implementasi *frontpage* dan *admin dashboard*. |
| **Explorer, Search, Filter** | Kirana | Logika pencarian dan filter di halaman galeri. |
| **Comment & Report** | Ria | Implementasi `CommentController` dan `ReportController`. |
| **Notifikasi** | Kirana | Implementasi sistem notifikasi *real-time*. |
| **Profil** | Ria | Manajemen profil pengguna. |

-----

## Support

Jika ada pertanyaan atau issues, silakan:
- Buat issue di GitHub repository. https://github.com/aidilsaputrakirsan-classroom/final-project-cloud-computing-b-cc-kelompok-6-sixma
- Hubungi tim development aka Kelompok 6 Sixma

---

**Developed with ❤️ for Final Project Mata Kuliah Cloud Computing Sistem Informasi Institut Teknologi Kalimantan**

*Last updated: 9:00 PM Friday, 5 Desember 2025*

<!-- end list -->

```
```
