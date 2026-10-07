<?php
require 'config.php';
require 'functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);

try {
    if ($id <= 0) {
        throw new Exception("ID buku tidak valid.");
    }

    $cek = $conn->prepare("SELECT COUNT(*) AS total FROM peminjaman WHERE buku_id = ?");
    $cek->bind_param("i", $id);
    $cek->execute();
    $total = (int)$cek->get_result()->fetch_assoc()['total'];

    if ($total > 0) {
        throw new Exception("Buku tidak bisa dihapus karena sudah pernah dipinjam.");
    }

    $stmt = $conn->prepare("SELECT foto_buku FROM buku WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $buku = $stmt->get_result()->fetch_assoc();

    if (!$buku) {
        throw new Exception("Buku tidak ditemukan.");
    }

    $hapus = $conn->prepare("DELETE FROM buku WHERE id = ?");
    $hapus->bind_param("i", $id);
    if (!$hapus->execute()) {
        throw new Exception("Gagal menghapus buku.");
    }

    if (!empty($buku['foto_buku']) && file_exists($buku['foto_buku'])) {
        unlink($buku['foto_buku']);
    }

    $_SESSION['flash'] = "Buku berhasil dihapus.";
} catch (Throwable $e) {
    $_SESSION['flash_error'] = $e->getMessage();
}

header("Location: admin_dashboard.php");
exit;
?>
