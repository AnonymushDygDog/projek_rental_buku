<?php
require 'config.php';
require 'functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit;
}

$flash = $_SESSION['flash'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);

$search = trim($_GET['search'] ?? '');
$era = $_GET['era'] ?? '';
$like = "%$search%";

if ($era === 'lawas') {
    $stmt = $conn->prepare("SELECT * FROM buku WHERE judul LIKE ? AND tahun_terbit < 2000 ORDER BY judul ASC");
    $stmt->bind_param("s", $like);
} elseif ($era === 'modern') {
    $stmt = $conn->prepare("SELECT * FROM buku WHERE judul LIKE ? AND tahun_terbit >= 2000 ORDER BY judul ASC");
    $stmt->bind_param("s", $like);
} else {
    $stmt = $conn->prepare("SELECT * FROM buku WHERE judul LIKE ? ORDER BY judul ASC");
    $stmt->bind_param("s", $like);
}
$stmt->execute();
$buku_result = $stmt->get_result();

$pinjaman_stmt = $conn->prepare("SELECT p.*, b.judul, b.penulis FROM peminjaman p JOIN buku b ON b.id = p.buku_id WHERE p.user_id = ? ORDER BY p.id DESC");
$pinjaman_stmt->bind_param("i", $_SESSION['user_id']);
$pinjaman_stmt->execute();
$pinjaman_result = $pinjaman_stmt->get_result();

/* ===== Query tambahan untuk tampilan (fitur visual seperti landing page) ===== */
$featured = $conn->query("SELECT * FROM buku ORDER BY id DESC LIMIT 1");
$featured = $featured ? $featured->fetch_assoc() : null;

$author_week = $conn->query("SELECT penulis, COUNT(*) AS jml, AVG(tahun_terbit) AS rata_tahun FROM buku GROUP BY penulis ORDER BY jml DESC, penulis ASC LIMIT 1");
$author_week = $author_week ? $author_week->fetch_assoc() : null;

$terakhir_stmt = $conn->prepare("SELECT p.*, b.judul, b.penulis FROM peminjaman p JOIN buku b ON b.id = p.buku_id WHERE p.user_id = ? ORDER BY p.id DESC LIMIT 1");
$terakhir_stmt->bind_param("i", $_SESSION['user_id']);
$terakhir_stmt->execute();
$terakhir = $terakhir_stmt->get_result()->fetch_assoc();

$unggulan = $conn->query("SELECT * FROM buku ORDER BY id DESC LIMIT 8");

function bintang(int $id): string {
    $n = 3 + ($id % 3);
    $s = '';
    $star = '<svg viewBox="0 0 24 24" style="width:13px;height:13px;vertical-align:-2px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="currentColor"/></svg>';
    for ($i = 1; $i <= 5; $i++) $s .= $i <= $n ? $star : '<span class="dim">' . $star . '</span>';
    return $s;
}
function jml_ulasan(int $id): int {
    return 12 + ($id * 7) % 180;
}
\n?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Toko Kata</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-inner">
            <span class="brand">
                <span class="brand-logo"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></span>
                TOKO KATA <small>rental buku</small>
            </span>
            <div class="navbar-actions">
                <div class="user-chip">
                    <span class="avatar"><?= strtoupper(substr(e($_SESSION['nama']), 0, 1)) ?></span>
                    <span><?= e($_SESSION['nama']) ?></span>
                </div>
                <a href="logout.php" class="btn btn-outline">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- HERO -->
        <div class="hero">
            <div class="hero-grid">
                <div>
                    <span class="eyebrow">Selamat datang, <?= e($_SESSION['nama']) ?></span>
                    <h1>Baru &amp;<br><em>Trending</em></h1>
                    <p class="lead">Jelajahi dunia baru dari penulis-penulis hebat. Sewa buku lawas penuh nostalgia hingga terbitan modern terbaru.</p>
                    <form method="GET" class="search-pill">
                        <span class="icon"><svg class="ic" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
                        <input type="text" name="search" placeholder="Judul, penulis, atau topik..." value="<?= e($search) ?>">
                        <?php if ($era): ?><input type="hidden" name="era" value="<?= e($era) ?>"><?php endif; ?>
                        <button type="submit" class="btn">Cari</button>
                    </form>
                </div>
                <div class="featured-book">
                    <?php if ($featured): ?>
                        <div class="book-cover-frame">
                            <?php if (!empty($featured['foto_buku']) && file_exists($featured['foto_buku'])): ?>
                                <img src="<?= e($featured['foto_buku']) ?>" alt="<?= e($featured['judul']) ?>">
                            <?php else: ?>
                                <img class="cover-fallback" src="assets/cover-default.jpg" alt="Sampul buku">
                            <?php endif; ?>
                        </div>
                        <div class="featured-info">
                            <span class="fb-label"><?= (int)$featured['tahun_terbit'] >= 2000 ? 'Rilis Modern' : 'Klasik Lawas' ?></span>
                            <h3><?= e($featured['judul']) ?></h3>
                            <p class="fb-meta"><?= e($featured['penulis']) ?> &bull; <?= e($featured['tahun_terbit']) ?></p>
                            <p class="fb-price">Rp <?= number_format((float)$featured['harga_sewa_per_hari'], 0, ',', '.') ?> <small style="color: var(--muted); font-weight: 600; font-size: 13px;">/hari</small></p>
                            <?php if ((int)$featured['stok'] > 0): ?>
                                <button type="button" class="btn" data-id="<?= e($featured['id']) ?>" data-judul="<?= e($featured['judul']) ?>" onclick="bukaFormPinjam(this)">Pin Sekarang <svg class="ic" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></button>
                            <?php else: ?>
                                <button type="button" class="btn" disabled>Stok Habis</button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <img class="cover-fallback" src="assets/cover-default.jpg" alt="Koleksi buku" style="width:190px;height:270px;">
                        <div class="featured-info"><h3>Koleksi segera hadir</h3><p class="fb-meta">Belum ada buku di katalog.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Penulis Pilihan & Terakhir Dipinjam -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <div class="hero-mini-card">
                <div class="mini-label">Penulis Pilihan Minggu Ini</div>
                <?php if ($author_week): ?>
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 62px; height: 62px; border-radius: 18px; background: linear-gradient(135deg, var(--terra-soft), var(--gold-soft)); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 26px; font-family: 'Fraunces', serif; color: var(--gold); font-weight: 700; flex-shrink: 0;">
                            <?= strtoupper(substr(e($author_week['penulis']), 0, 1)) ?>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 3px; font-size: 19px;"><?= e($author_week['penulis']) ?></h4>
                            <p style="margin: 0; font-size: 13px; color: var(--muted);"><?= (int)$author_week['jml'] ?> buku di katalog &bull; era <?= (int)$author_week['rata_tahun'] >= 2000 ? 'modern' : 'lawas' ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="margin: 0;">Belum ada data penulis.</p>
                <?php endif; ?>
            </div>

            <div class="hero-mini-card">
                <div class="mini-label">Terakhir Kamu Pinjam</div>
                <?php if ($terakhir): ?>
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <img src="assets/cover-default.jpg" alt="Sampul buku" style="width: 52px; height: 72px; object-fit: cover; border-radius: 8px; flex-shrink: 0; box-shadow: var(--shadow-book);">
                        <div>
                            <h4 style="margin: 0 0 3px; font-size: 17px;"><?= e($terakhir['judul']) ?></h4>
                            <p style="margin: 0; font-size: 13px; color: var(--muted);"><?= e($terakhir['penulis']) ?></p>
                            <p style="margin: 6px 0 0;">
                                <?php if ($terakhir['status'] === 'dipinjam'): ?>
                                    <span class="badge warn"><svg class="ic" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Sedang dipinjam &bull; <?= (int)$terakhir['durasi'] ?> hari</span>
                                <?php else: ?>
                                    <span class="badge ok"><svg class="ic" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Selesai &bull; <?= e($terakhir['tanggal_kembali']) ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="margin: 0;">Belum ada riwayat peminjaman. Yuk mulai!</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistik -->
        <div class="stats">
            <div class="stat-card">
                <span class="stat-icon gold"><svg class="ic" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></span>
                <div><strong><?= (int)$buku_result->num_rows ?></strong><span>Judul tersedia</span></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon terra"><svg class="ic" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span>
                <div><strong><?= (int)$pinjaman_result->num_rows ?></strong><span>Total peminjaman</span></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon sage"><svg class="ic" viewBox="0 0 24 24"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg></span>
                <div><strong><?php $aktif = 0; $pinjaman_result->data_seek(0); while ($t = $pinjaman_result->fetch_assoc()) { if ($t['status'] === 'dipinjam') $aktif++; } $pinjaman_result->data_seek(0); echo $aktif; ?></strong><span>Sedang dipinjam</span></div>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="flash success"><svg class="ic" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> <?= e($flash) ?></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="flash error"><svg class="ic" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> <?= e($flash_error) ?></div>
        <?php endif; ?>

        <!-- Carousel Koleksi Terbaru -->
        <div class="section-head">
            <span class="eyebrow">Unggulan</span>
            <div>
                <h3>Koleksi Terbaru</h3>
                <p>Buku-buku terbaru yang baru masuk ke rak kami. Geser untuk menjelajah &rarr;</p>
            </div>
        </div>

        <?php if ($unggulan && $unggulan->num_rows > 0): ?>
        <div class="carousel">
            <?php while ($u = $unggulan->fetch_assoc()): $stok_u = (int)$u['stok']; ?>
                <div class="carousel-card">
                    <div class="cc-cover">
                        <span class="era-badge <?= (int)$u['tahun_terbit'] >= 2000 ? 'modern' : 'lawas' ?>"><?= (int)$u['tahun_terbit'] >= 2000 ? 'Modern' : 'Lawas' ?></span>
                        <?php if (!empty($u['foto_buku']) && file_exists($u['foto_buku'])): ?>
                            <img src="<?= e($u['foto_buku']) ?>" alt="<?= e($u['judul']) ?>">
                        <?php else: ?>
                            <img class="cover-fallback" src="assets/cover-default.jpg" alt="Sampul buku">
                        <?php endif; ?>
                    </div>
                    <h4><?= e($u['judul']) ?></h4>
                    <p class="cc-meta"><?= e($u['penulis']) ?></p>
                    <p class="stars"><?= bintang((int)$u['id']) ?> <span style="color: var(--muted); font-size: 11px; letter-spacing: 0;">(<?= jml_ulasan((int)$u['id']) ?>)</span></p>
                    <p class="cc-price">Rp <?= number_format((float)$u['harga_sewa_per_hari'], 0, ',', '.') ?> <small>/hari</small></p>
                    <?php if ($stok_u > 0): ?>
                        <button type="button" class="btn btn-sage" style="width: 100%;" data-id="<?= e($u['id']) ?>" data-judul="<?= e($u['judul']) ?>" onclick="bukaFormPinjam(this)"><svg class="ic" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg> Pinjam</button>
                    <?php else: ?>
                        <button type="button" class="btn" style="width: 100%;" disabled>Stok Habis</button>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
            <div class="card empty-state">Belum ada buku di katalog.</div>
        <?php endif; ?>

        <!-- Katalog -->
        <div class="section-head">
            <span class="eyebrow">Katalog</span>
            <div>
                <h3>Jelajahi Semua Buku</h3>
                <p>Gunakan filter era untuk mempersempit pencarian.</p>
            </div>
        </div>

        <div class="pill-tabs">
            <a href="?search=<?= urlencode($search) ?>" class="pill-tab <?= $era === '' ? 'active' : '' ?>">Semua Buku</a>
            <a href="?search=<?= urlencode($search) ?>&amp;era=modern" class="pill-tab <?= $era === 'modern' ? 'active' : '' ?>">Modern (&ge; 2000)</a>
            <a href="?search=<?= urlencode($search) ?>&amp;era=lawas" class="pill-tab <?= $era === 'lawas' ? 'active' : '' ?>">Lawas (&lt; 2000)</a>
        </div>

        <form method="GET" class="search-pill" style="margin-bottom: 24px;">
            <span class="icon"><svg class="ic" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
            <input type="text" name="search" placeholder="Cari judul buku..." value="<?= e($search) ?>">
            <?php if ($era): ?><input type="hidden" name="era" value="<?= e($era) ?>"><?php endif; ?>
            <button type="submit" class="btn btn-outline" style="box-shadow: none;">Cari</button>
        </form>

        <div class="book-grid">
            <?php while ($row = $buku_result->fetch_assoc()):
                $is_modern = (int)$row['tahun_terbit'] >= 2000;
                $stok = (int)$row['stok'];
            ?>
                <div class="card book-card">
                    <div class="book-cover">
                        <span class="era-badge <?= $is_modern ? 'modern' : 'lawas' ?>"><?= $is_modern ? 'Modern' : 'Lawas' ?></span>
                        <?php if (!empty($row['foto_buku']) && file_exists($row['foto_buku'])): ?>
                            <img src="<?= e($row['foto_buku']) ?>" alt="<?= e($row['judul']) ?>">
                        <?php else: ?>
                            <img class="cover-fallback" src="assets/cover-default.jpg" alt="Sampul buku">
                        <?php endif; ?>
                    </div>

                    <h4><?= e($row['judul']) ?></h4>
                    <p class="book-meta"><?= e($row['penulis']) ?> &bull; <?= e($row['tahun_terbit']) ?></p>
                    <p class="stars" style="font-size: 12px;"><?= bintang((int)$row['id']) ?></p>
                    <p class="book-price"><strong>Rp <?= number_format((float)$row['harga_sewa_per_hari'], 0, ',', '.') ?></strong> <span class="text-muted" style="font-size:13px;">/hari</span></p>
                    <p class="stock-line">
                        <span class="dot <?= $stok === 0 ? 'out' : ($stok <= 2 ? 'low' : 'ok') ?>"></span>
                        <?= $stok === 0 ? 'Stok habis' : "Stok tersedia: $stok" ?>
                    </p>

                    <?php if ($stok > 0): ?>
                        <button
                            type="button"
                            class="btn"
                            data-id="<?= e($row['id']) ?>"
                            data-judul="<?= e($row['judul']) ?>"
                            onclick="bukaFormPinjam(this)">Pinjam Buku <svg class="ic" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></button>
                    <?php else: ?>
                        <button type="button" class="btn" disabled>Stok Habis</button>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Riwayat -->
        <div class="section-head">
            <span class="eyebrow">Riwayat</span>
            <div>
                <h3>Peminjaman Saya</h3>
                <p>Pantau status buku yang sedang kamu sewa.</p>
            </div>
        </div>

        <div class="card" style="text-align: left; padding: 10px 22px;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Buku</th>
                            <th>Durasi</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($pinjaman_result && $pinjaman_result->num_rows > 0): ?>
                        <?php while ($p = $pinjaman_result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= e($p['judul']) ?></strong><br><small class="text-muted"><?= e($p['penulis']) ?></small></td>
                                <td><?= e($p['durasi']) ?> hari</td>
                                <td><strong>Rp <?= number_format((float)$p['total_harga'], 0, ',', '.') ?></strong></td>
                                <td>
                                    <?php if ($p['status'] === 'dipinjam'): ?>
                                        <span class="badge warn"><svg class="ic" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge ok"><svg class="ic" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Dikembalikan</span><br>
                                        <small class="text-muted"><?= e($p['tanggal_kembali']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['status'] === 'dipinjam'): ?>
                                        <form method="POST" action="kembalikan.php" onsubmit="return confirm('Yakin kembalikan buku ini?');" style="margin: 0;">
                                            <input type="hidden" name="peminjaman_id" value="<?= e($p['id']) ?>">
                                            <button type="submit" class="btn btn-outline" style="padding: 8px 16px; font-size: 13px;">Kembalikan</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5"><div class="empty-state">Belum ada peminjaman. Yuk pinjam buku pertamamu!</div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="formPinjam" class="panel-form" style="display:none;">
            <h3 id="judulBukuPinjam">Pinjam Buku</h3>
            <p class="text-muted" style="margin-top: 0;">Lengkapi data berikut untuk mengajukan peminjaman.</p>
            <form action="proses_pinjam.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="buku_id" id="buku_id_input">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0 18px;">
                    <div>
                        <label>Berapa Hari?</label>
                        <input type="number" name="durasi" min="1" max="14" required>
                    </div>
                    <div>
                        <label>Upload Foto Identitas (KTP/Kartu Pelajar)</label>
                        <input type="file" name="foto_identitas" accept="image/*" required>
                    </div>
                    <div>
                        <label>Upload Bukti Pembayaran</label>
                        <input type="file" name="bukti_bayar" accept="image/*" required>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 6px;">
                    <button type="submit" class="btn">Kirim Permintaan <svg class="ic" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg></button>
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('formPinjam').style.display='none'">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <button onclick="toggleChat()" class="chat-fab" title="Chat Admin"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></button>
    <div class="chat-widget" id="chatWidget">
        <div class="chat-header" onclick="toggleChat()"><span class="status-dot"></span> Chat Admin &mdash; Online</div>
        <div class="chat-body">
            <div class="chat-bubble">Halo! Ada yang bisa kami bantu terkait buku?</div>
        </div>
        <div class="chat-input">
            <input type="text" placeholder="Ketik pesan...">
            <button class="btn"><svg class="ic" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg></button>
        </div>
    </div>

    <footer class="footer">
        <strong>TOKO KATA</strong> &mdash; Baru &amp; Trending, dari yang lawas hingga modern. &copy; <?= date('Y') ?>
    </footer>

    <script>
        function bukaFormPinjam(btn) {
            const id = btn.getAttribute('data-id');
            const judul = btn.getAttribute('data-judul');
            document.getElementById('formPinjam').style.display = 'block';
            document.getElementById('buku_id_input').value = id;
            document.getElementById('judulBukuPinjam').innerText = "Form Pinjam: " + judul;
            document.getElementById('formPinjam').scrollIntoView({ behavior: 'smooth' });
        }
        function toggleChat() {
            var chat = document.getElementById('chatWidget');
            chat.style.display = (chat.style.display === 'block') ? 'none' : 'block';
        }
    </script>
</body>
</html>
