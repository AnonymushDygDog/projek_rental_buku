# projek_rental_buku
TOKO KATA — Rental Buku
=======================

CARA INSTALASI CEPAT
--------------------
1. Ekstrak ZIP ini ke folder htdocs (misal: C:\xampp\htdocs\rental)
2. Buat database "db_rental_buku" di phpMyAdmin, lalu import file
   sql/db_rental_buku.sql
3. Pastikan folder "uploads/" dan sub-foldernya bisa ditulis (writable)
4. Buka http://localhost/rental/
   
    Email    : admin@tokokata.com
    Password : admin12344

STRUKTUR FOLDER
---------------
- index.php             Halaman login (struktur kartu terpusat)
- register.php          Pendaftaran akun user
- user_dashboard.php    Katalog, peminjaman, riwayat
- admin_dashboard.php   Panel admin (tambah/hapus buku, statistik)
- style.css             Tema gelap v3 (tanpa emoji, ikon SVG)
- assets/               Gambar asli dekoratif (login, hero, sampul)
- TAMBAH_ADMIN.sql      Panduan SQL tambah akun admin
- uploads/              File yang diupload user (foto buku, identitas, pembayaran)
- sql/                  Dump database
