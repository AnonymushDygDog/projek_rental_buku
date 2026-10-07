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
    <title>Toko Kata — Daftar Akun Baru</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-shell">
            <div class="auth-side">
                <div>
                    <div class="auth-wordmark">TOKO<span>KATA</span></div>
                    <h1>Mulai perpustakaan <em>pribadimu</em> hari ini.</h1>
                    <p>Satu akun untuk mengakses ribuan halaman. Proses pendaftaran kurang dari 2 menit.</p>
                    <ul class="auth-features">
                        <li><span class="fi">✅</span><div><strong>Gratis mendaftar</strong>Tidak ada biaya pendaftaran atau bulanan.</div></li>
                        <li><span class="fi">🪪</span><div><strong>Verifikasi sederhana</strong>Cukup foto identitas saat meminjam.</div></li>
                        <li><span class="fi">📚</span><div><strong>Riwayat peminjaman</strong>Pantau semua buku yang pernah kamu baca.</div></li>
                    </ul>
                </div>
                <p class="auth-quote">"Buku adalah teman yang tak pernah mengkhianati." — Soekarno</p>
            </div>
            <div class="auth-form">
                <h2>Buat akun baru</h2>
                <p class="text-muted" style="margin-top: 0;">Isi data di bawah untuk bergabung.</p>

                <?php if ($error): ?>
                    <div class="flash error">⚠️ <?= e($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="flash success">🎉 <?= e($success) ?> <a href="index.php">Login disini</a></div>
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
                    <textarea name="alamat" placeholder="Jalan, nomor rumah, kota, kode pos" rows="2" required style="resize: none;"></textarea>

                    <button type="submit" name="register" class="btn" style="width: 100%;">Daftar Sekarang →</button>
                </form>
                <p class="form-foot">Sudah punya akun? <a href="index.php">Masuk disini</a></p>
            </div>
        </div>
    </div>
</body>
</html>
