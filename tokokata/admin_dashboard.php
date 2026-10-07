<?php
require 'config.php';
require 'functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_POST['tambah_buku'])) {
    try {
        $judul = trim($_POST['judul'] ?? '');
        $penulis = trim($_POST['penulis'] ?? '');
        $tahun = (int)($_POST['tahun_terbit'] ?? 0);
        $stok = (int)($_POST['stok'] ?? -1);
        $harga = (float)($_POST['harga'] ?? -1);

        if ($judul === '' || $penulis === '' || $tahun < 1800 || $tahun > 2100) {
            throw new Exception("Data buku tidak valid.");
        }
        if ($stok < 0 || $harga < 0) {
            throw new Exception("Stok dan harga tidak boleh negatif.");
        }

        $foto_buku = simpan_gambar($_FILES['foto_buku'] ?? [], 'uploads/buku', 'Foto buku');

        $stmt = $conn->prepare("INSERT INTO buku (judul, penulis, tahun_terbit, stok, harga_sewa_per_hari, foto_buku) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiids", $judul, $penulis, $tahun, $stok, $harga, $foto_buku);

        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan buku.");
        }

        $_SESSION['flash'] = "Buku berhasil ditambahkan.";
    } catch (Throwable $e) {
        $_SESSION['flash_error'] = $e->getMessage();
    }

    header("Location: admin_dashboard.php");
    exit;
}

$flash = $_SESSION['flash'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);

$buku = $conn->query("SELECT * FROM buku ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin — Toko Kata</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-inner">
            <span class="brand">
                <span class="brand-logo">🛠️</span>
                Toko Kata <small>panel admin</small>
            </span>
            <div class="navbar-actions">
                <span class="user-chip">
                    <span class="avatar" style="background: linear-gradient(135deg, #0EA5E9, #6366F1);">A</span>
                    <span><?= e($_SESSION['nama']) ?></span>
                </span>
                <a href="logout.php" class="btn btn-outline">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="hero">
            <span class="eyebrow">🛠️ Dashboard Admin</span>
            <h1>Kelola <em>koleksi</em> Toko Kata</h1>
            <p class="lead">Tambah buku baru, pantau stok, dan kelola inventaris rental dari satu tempat.</p>
        </div>

        <?php
            $total_judul = 0; $total_stok = 0; $lawas = 0; $modern = 0;
            if ($buku) {
                $total_judul = $buku->num_rows;
                while ($r = $buku->fetch_assoc()) {
                    $total_stok += (int)$r['stok'];
                    ((int)$r['tahun_terbit'] >= 2000 ? $modern++ : $lawas++);
                }
                $buku->data_seek(0);
            }
        ?>

        <div class="stats">
            <div class="stat-card">
                <span class="stat-icon gold">📖</span>
                <div><strong><?= $total_judul ?></strong><span>Total judul buku</span></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon terra">📦</span>
                <div><strong><?= $total_stok ?></strong><span>Total unit stok</span></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon sage">🕰️</span>
                <div><strong><?= $lawas ?></strong><span>Buku lawas</span></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon sky">✨</span>
                <div><strong><?= $modern ?></strong><span>Buku modern</span></div>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="flash success">✅ <?= e($flash) ?></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="flash error">⚠️ <?= e($flash_error) ?></div>
        <?php endif; ?>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 10px; align-items: flex-start;">
            <div class="card" style="flex: 1; min-width: 300px; text-align: left;">
                <div class="section-head" style="margin: 0 0 14px;">
                    <span class="eyebrow">Baru</span>
                    <div><h3 style="font-size: 18px;">Tambah Buku Baru</h3></div>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <label>Judul Buku</label>
                    <input type="text" name="judul" placeholder="Judul Buku" required>

                    <label>Penulis</label>
                    <input type="text" name="penulis" placeholder="Penulis" required>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px;">
                        <div>
                            <label>Tahun Terbit</label>
                            <input type="number" name="tahun_terbit" placeholder="1998" min="1800" max="2100" required>
                        </div>
                        <div>
                            <label>Stok</label>
                            <input type="number" name="stok" placeholder="Jumlah" min="0" required>
                        </div>
                    </div>

                    <label>Harga Sewa / Hari (Rp)</label>
                    <input type="number" name="harga" placeholder="5000" min="0" step="0.01" required>

                    <label>Foto Buku</label>
                    <input type="file" name="foto_buku" accept="image/*" required>

                    <button type="submit" name="tambah_buku" class="btn" style="width: 100%;">＋ Simpan Buku</button>
                </form>
            </div>

            <div class="card" style="flex: 2; min-width: 320px; text-align: left; padding: 10px 22px;">
                <div class="section-head" style="margin: 12px 0 6px;">
                    <span class="eyebrow">Inventaris</span>
                    <div><h3 style="font-size: 18px;">Manajemen Buku</h3></div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Judul</th>
                                <th>Tahun</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($buku): while ($row = $buku->fetch_assoc()):
                            $s = (int)$row['stok'];
                        ?>
                        <tr>
                            <td>
                                <?php if (!empty($row['foto_buku']) && file_exists($row['foto_buku'])): ?>
                                    <img src="<?= e($row['foto_buku']) ?>" alt="<?= e($row['judul']) ?>" style="width: 46px; height: 62px; object-fit: cover; border-radius: 8px; box-shadow: var(--shadow-sm);">
                                <?php else: ?>
                                    <div style="width: 46px; height: 62px; border-radius: 8px; background: linear-gradient(135deg, var(--sage-soft), var(--sky-soft)); display: flex; align-items: center; justify-content: center;">📚</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= e($row['judul']) ?></strong><br><small class="text-muted"><?= e($row['penulis']) ?></small></td>
                            <td>
                                <?= e($row['tahun_terbit']) ?><br>
                                <span class="badge <?= (int)$row['tahun_terbit'] >= 2000 ? 'info' : 'warn' ?>" style="font-size: 10.5px; padding: 2px 8px;">
                                    <?= (int)$row['tahun_terbit'] >= 2000 ? 'Modern' : 'Lawas' ?>
                                </span>
                            </td>
                            <td><strong>Rp <?= number_format((float)$row['harga_sewa_per_hari'], 0, ',', '.') ?></strong></td>
                            <td>
                                <span class="badge <?= $s === 0 ? 'muted' : ($s <= 2 ? 'warn' : 'ok') ?>">
                                    <span class="dot <?= $s === 0 ? 'out' : ($s <= 2 ? 'low' : 'ok') ?>" style="width:7px;height:7px;"></span>
                                    <?= $s ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="hapus_buku.php" onsubmit="return confirm('Yakin hapus buku ini?');" style="margin: 0;">
                                    <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                                    <button type="submit" class="btn btn-danger">🗑 Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <strong>Toko Kata — Panel Admin</strong> &copy; <?= date('Y') ?>
    </footer>
</body>
</html>
