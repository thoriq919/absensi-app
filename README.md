# Absensi-App

Aplikasi Absensi berbasis web yang dibangun menggunakan [Laravel](https://laravel.com/) untuk mengelola kehadiran karyawan atau pengguna secara efisien. Proyek ini menyediakan fitur untuk mencatat absensi, melihat laporan, dan manajemen data pengguna.

## Prasyarat
Sebelum menginstal proyek ini, pastikan Anda memiliki perangkat lunak berikut di komputer lokal Anda:
- [PHP](https://www.php.net/) (>= 8.0)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) dan [NPM](https://www.npmjs.com/)
- [PostgreSQL](https://www.postgresql.org/) 
- [Git](https://git-scm.com/)

## Cara Menginstal Proyek ke Lokal

Ikuti langkah-langkah berikut untuk mengkloning dan menjalankan proyek ini di mesin lokal Anda:

### 1. Clone Repository
Kloning proyek dari GitHub ke direktori lokal Anda:
```bash
git clone https://github.com/thoriq919/absensi-app.git
cd absensi-app
```

### 2. Install Dependensi PHP
Jalankan perintah berikut untuk menginstal semua dependensi PHP yang diperlukan melalui Composer:
```bash
composer install
```

### 3. Konfigurasi File .env
Salin file .env.example menjadi .env dan sesuaikan pengaturan seperti koneksi database:
```bash
cp .env.example .env
```
Buka file .env dengan editor teks dan sesuaikan dengan kode berikut:
```bash
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=absensi_app
DB_USERNAME=postgres
DB_PASSWORD=postgres
```
username dan password sesuaikan dengan konfigurasi postgresSQL

### 4. Generate Application Key
Buat kunci aplikasi unik untuk Laravel dengan perintah Artisan:
```bash
php artisan key:generate
```

### 5. Install Dependensi JavaScript
Instal dependensi frontend menggunakan NPM:
```bash
npm install
npm run dev
```

### 6. Jalankan Migrasi Database
Jalankan migrasi untuk membuat tabel-tabel yang diperlukan di database:
```bash
php artisan migrate
```

### 7. Jalankan Server lokal
```bash
php artisan serve
```
