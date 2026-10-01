<?php
require 'config.php';
require 'functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin_dashboard.php" : "user_dashboard.php"));
    exit;
}

$error = null;
$success = null;

if (isset($_POST['register'])) {
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['nomor_telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nama === '' || $email === '' || $telepon === '' || $alamat === '' || $password === '') {
        $error = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $cek_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $cek_email->bind_param("s", $email);
        $cek_email->execute();
        $result_cek = $cek_email->get_result();

        if ($result_cek->num_rows > 0) {
            $error = "Email sudah terdaftar! Gunakan email lain.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nama_lengkap, email, password, nomor_telepon, alamat, role) VALUES (?, ?, ?, ?, ?, 'user')");
            $stmt->bind_param("sssss", $nama, $email, $hash, $telepon, $alamat);

            if ($stmt->execute()) {
                $success = "Akun berhasil dibuat! Silakan login.";
            } else {
                $error = "Terjadi kesalahan sistem.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Kata — Daftar Akun</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 30px 16px; background: linear-gradient(rgba(14,16,19,.88), rgba(14,16,19,.94)), url('assets/login-side.jpg') center/cover no-repeat fixed;">
    <div class="card" style="width: 100%; max-width: 440px; padding: 36px 34px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <span class="brand-logo"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></span>
            <div>
                <div class="brand" style="font-size: 21px;">Daftar Akun</div>
                <small style="color: var(--muted); font-size: 11px; letter-spacing: .12em; text-transform: uppercase;">Toko Kata</small>
            </div>
        </div>
        <p class="text-muted" style="margin: 0 0 22px; font-size: 14px;">Bergabunglah dan temukan buku favoritmu.</p>

        <?php if ($error): ?>
            <div class="flash error"><svg class="ic" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> <?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="flash success"><svg class="ic" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> <?= e($success) ?> <a href="index.php">Login disini</a></div>
        <?php endif; ?>

        <form method="POST">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_lengkap" placeholder="Nama lengkap kamu" required>
            <label>Email Aktif</label>
            <input type="email" name="email" placeholder="nama@email.com" required>
            <label>Password</label>
            <input type="password" name="password" placeholder="Minimal 6 karakter" required minlength="6">
            <label>Nomor Telepon / WhatsApp</label>
            <input type="text" name="nomor_telepon" placeholder="08xxxxxxxxxx" required>
            <label>Alamat Lengkap</label>
            <textarea name="alamat" placeholder="Jalan, nomor rumah, kota" rows="2" required style="resize: none;"></textarea>

            <button type="submit" name="register" class="btn" style="width: 100%;">Daftar Sekarang <svg class="ic" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></button>
        </form>
        <p class="form-foot">Sudah punya akun? <a href="index.php">Masuk disini</a></p>
    </div>
</body>
</html>
