<?php
require 'config.php';
require 'functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin_dashboard.php" : "user_dashboard.php"));
    exit;
}

$error = null;

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, nama_lengkap, role, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama'] = $user['nama_lengkap'];

            header("Location: " . ($user['role'] === 'admin' ? "admin_dashboard.php" : "user_dashboard.php"));
            exit;
        }
        $error = "Password salah!";
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Kata — Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 30px 16px; background: linear-gradient(rgba(14,16,19,.88), rgba(14,16,19,.94)), url('assets/open-book.jpg') center/cover no-repeat fixed;">
    <div class="card" style="width: 100%; max-width: 420px; padding: 38px 34px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <span class="brand-logo"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></span>
            <div>
                <div class="brand" style="font-size: 21px;">Toko Kata</div>
                <small style="color: var(--muted); font-size: 11px; letter-spacing: .12em; text-transform: uppercase;">Rental Buku</small>
            </div>
        </div>
        <p class="text-muted" style="margin: 0 0 22px; font-size: 14px;">Temukan bukumu, dari yang lawas hingga modern.</p>

        <?php if ($error): ?>
            <div class="flash error"><svg class="ic" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> <?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" placeholder="nama@email.com" required>
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
            <button type="submit" name="login" class="btn" style="width: 100%;">Masuk <svg class="ic" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></button>
        </form>
        <p class="form-foot">Belum punya akun? <a href="register.php">Daftar disini</a></p>
    </div>
</body>
</html>
