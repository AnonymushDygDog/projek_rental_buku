<?php
require 'config.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: user_dashboard.php");
    exit;
}

$transaction = false;
$foto_identitas = null;
$bukti_bayar = null;

try {
    $buku_id = (int)($_POST['buku_id'] ?? 0);
    $durasi = (int)($_POST['durasi'] ?? 0);

    if ($buku_id <= 0 || $durasi < 1 || $durasi > 14) {
        throw new Exception("Data pinjam tidak valid.");
    }

    $foto_identitas = simpan_gambar($_FILES['foto_identitas'] ?? [], 'uploads/identitas', 'Foto identitas');
    $bukti_bayar = simpan_gambar($_FILES['bukti_bayar'] ?? [], 'uploads/pembayaran', 'Bukti pembayaran');

    $conn->begin_transaction();
    $transaction = true;

    $stmt = $conn->prepare("SELECT id, stok, harga_sewa_per_hari FROM buku WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $buku_id);
    $stmt->execute();
    $buku = $stmt->get_result()->fetch_assoc();

    if (!$buku) {
        throw new Exception("Buku tidak ditemukan.");
    }
    if ((int)$buku['stok'] < 1) {
        throw new Exception("Stok buku sedang habis.");
    }

    $total_harga = (float)$buku['harga_sewa_per_hari'] * $durasi;

    $insert = $conn->prepare("INSERT INTO peminjaman (user_id, buku_id, durasi, total_harga, foto_identitas, bukti_bayar) VALUES (?, ?, ?, ?, ?, ?)");
    $insert->bind_param("iiidss", $_SESSION['user_id'], $buku_id, $durasi, $total_harga, $foto_identitas, $bukti_bayar);
    if (!$insert->execute()) {
        throw new Exception("Gagal menyimpan data peminjaman.");
    }

    $update = $conn->prepare("UPDATE buku SET stok = stok - 1 WHERE id = ? AND stok > 0");
    $update->bind_param("i", $buku_id);
    $update->execute();
    if ($update->affected_rows < 1) {
        throw new Exception("Gagal memperbarui stok buku.");
    }

    $conn->commit();
    $transaction = false;

    $_SESSION['flash'] = "Permintaan pinjam berhasil. Total: Rp " . number_format($total_harga, 0, ',', '.');
} catch (Throwable $e) {
    if ($transaction) {
        $conn->rollback();
    }
    $_SESSION['flash_error'] = $e->getMessage();
}

header("Location: user_dashboard.php");
exit;
?>
