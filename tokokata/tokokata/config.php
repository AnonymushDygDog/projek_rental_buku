<?php
session_start();

$host = "localhost";
$user = "root";
$pass = ""; // Sesuaikan jika password MySQL Anda tidak kosong
$db   = "db_rental_buku";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
