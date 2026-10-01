<?php
require 'config.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: user_dashboard.php");
    exit;
}

$transaction = false;

try {
    $peminjaman_id = (int)($_POST['peminjaman_id'] ?? 0);
    if ($peminjaman_id <= 0) {
        throw new Exception("Data pengembalian tidak valid.");
    }

    $conn->begin_transaction();
    $transaction = true;

    $stmt = $conn->prepare("SELECT id, buku_id, status FROM peminjaman WHERE id = ? AND user_id = ? FOR UPDATE");
    $stmt->bind_param("ii", $peminjaman_id, $_SESSION['user_id']);
    $stmt->execute();
    $pinjam = $stmt->get_result()->fetch_assoc();

    if (!$pinjam) {
        throw new Exception("Data peminjaman tidak ditemukan.");
    }
    if ($pinjam['status'] !== 'dipinjam') {
        throw new Exception("Buku sudah dikembalikan sebelumnya.");
    }

    $update = $conn->prepare("UPDATE peminjaman SET status = 'dikembalikan', tanggal_kembali = NOW() WHERE id = ? AND status = 'dipinjam'");
    $update->bind_param("i", $peminjaman_id);
    $update->execute();
    if ($update->affected_rows < 1) {
        throw new Exception("Gagal mengupdate status pengembalian.");
    }

    $stok = $conn->prepare("UPDATE buku SET stok = stok + 1 WHERE id = ?");
    $stok->bind_param("i", $pinjam['buku_id']);
    $stok->execute();

    $conn->commit();
    $transaction = false;

    $_SESSION['flash'] = "Buku berhasil dikembalikan.";
} catch (Throwable $e) {
    if ($transaction) {
        $conn->rollback();
    }
    $_SESSION['flash_error'] = $e->getMessage();
}

header("Location: user_dashboard.php");
exit;
?>
