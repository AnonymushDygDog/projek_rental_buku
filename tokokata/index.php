<?php
require 'config.php';
require 'functions.php'; // Add this line to load the e() function

if (isset($_SESSION['user_id'])) {
// ... rest of the code
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
    <title>Toko Kata — Rental Buku Favoritmu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-shell">
            <div class="auth-side">
                <div>
                    <div class="auth-wordmark">TOKO<span>KATA</span></div>
                    <h1>Baru &amp;<br><em>Trending</em> 📚</h1>
                    <p class="sub">Sewa buku dari era lawas hingga modern, langsung dari rumah. Harga ramah kantong, koleksi terus bertambah.</p>
                    <div class="auth-search-demo">
                        <div class="search-pill">
                            <span class="icon">🔍</span>
                            <input type="text" placeholder="Judul, penulis, atau topik..." disabled>
                        </div>
                    </div>
                    <div class="auth-books">
                        <img class="ab" src="uploads/buku/20260925031134_931c12547768.png" alt="Buku koleksi" onerror="this.style.display='none'">
                        <div class="ab" style="background: linear-gradient(150deg, var(--gold-soft), var(--terra-soft)); display:flex; align-items:center; justify-content:center; font-size:30px;">📖</div>
                        <div class="ab" style="background: linear-gradient(150deg, var(--sage-soft), var(--sky-soft)); display:flex; align-items:center; justify-content:center; font-size:30px;">📚</div>
                    </div>
                    <ul class="auth-features">
                        <li><span class="fi">🕰️</span><div><strong>Dua era koleksi</strong>Buku lawas (&lt; 2000) &amp; modern (&ge; 2000).</div></li>
                        <li><span class="fi">🚚</span><div><strong>Sewa fleksibel</strong>Mulai 1 hari, maksimal 14 hari.</div></li>
                        <li><span class="fi">💬</span><div><strong>Chat admin langsung</strong>Butuh rekomendasi? Tanya kapan saja.</div></li>
                    </ul>
                </div>
                <p class="auth-quote">"Sebuah ruangan tanpa buku seperti tubuh tanpa jiwa." — Cicero</p>
            </div>
            <div class="auth-form">
                <h2>Selamat datang kembali 👋</h2>
                <p class="text-muted" style="margin-top: 0;">Masuk untuk melanjutkan petualangan bacamu.</p>

                <?php if ($error): ?>
                    <div class="flash error">⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="POST">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="nama@email.com" required>
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Masukkan password" required>
                    <button type="submit" name="login" class="btn" style="width: 100%;">Masuk →</button>
                </form>
                <p class="form-foot">Belum punya akun? <a href="register.php">Daftar gratis</a></p>
            </div>
        </div>
    </div>
</body>
</html>
