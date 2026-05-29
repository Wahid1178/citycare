# CityCare Pro - PHP Native + MongoDB

CityCare Pro adalah aplikasi web pengaduan dan monitoring fasilitas umum berbasis PHP Native dan MongoDB/NoSQL.

## Role Pengguna

### 1. Masyarakat
- Registrasi akun
- Login
- Membuat laporan
- Melihat laporan sendiri
- Filter laporan sendiri
- Edit laporan jika status masih Menunggu
- Hapus laporan jika status masih Menunggu

### 2. Petugas
- Melihat dashboard petugas
- Melihat semua laporan masyarakat
- Filter laporan berdasarkan keyword, kategori, status, prioritas, dan wilayah
- Memproses laporan
- Mengubah status laporan
- Memberi catatan petugas
- Melihat rekap laporan yang ditangani

### 3. Admin
- Dashboard admin
- Kelola semua laporan
- Memberi catatan admin
- Mengubah status dan prioritas laporan
- Menugaskan petugas
- Menghapus laporan
- Manajemen user
- Tambah/edit/hapus user
- Manajemen kategori laporan
- Tambah/edit/hapus kategori
- Melihat rekapitulasi lengkap

## Fitur Wajib TUBES
- Database NoSQL MongoDB
- CRUD
- Filter
- Rekapitulasi data:
  - COUNT
  - SUM
  - AVG
  - MAX
  - MIN
  - Greater Than

## Cara Menjalankan
1. Pastikan MongoDB Server aktif.
2. Pastikan extension PHP MongoDB aktif.
3. Jalankan:
   composer install
4. Jalankan:
   php -S localhost:8000
5. Buka:
   http://localhost:8000/seed.php
6. Login:
   http://localhost:8000/auth/login.php

## Akun Demo
- Admin: admin@citycare.com / 123456
- Petugas: petugas@citycare.com / 123456
- Petugas 2: drainase@citycare.com / 123456
- Masyarakat: user@citycare.com / 123456

## MongoDB Compass
Connection string:
mongodb://localhost:27017

Database:
citycare_pro_db
