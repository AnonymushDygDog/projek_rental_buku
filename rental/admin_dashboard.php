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
                <span class="brand-logo"><svg class="ic" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
                TOKO KATA <small>panel admin</small>
            </span>
            <div class="navbar-actions">
                <span class="user-chip">
                    <span class="avatar" style="background: linear-gradient(135deg, var(--sky), #A5C4E0);">A</span>
                    <span><?= e($_SESSION['nama']) ?></span>
                </span>
                <a href="logout.php" class="btn btn-outline">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="hero">
            <span class="eyebrow">Dashboard Admin</span>
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
                <span class="stat-icon gold"><svg class="ic" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></span>
                <div><strong><?= $total_judul ?></strong><span>Total judul buku</span></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon terra"><svg class="ic" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span>
                <div><strong><?= $total_stok ?></strong><span>Total unit stok</span></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon sage"><svg class="ic" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
                <div><strong><?= $lawas ?></strong><span>Buku lawas</span></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon sky"><svg class="ic" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></span>
                <div><strong><?= $modern ?></strong><span>Buku modern</span></div>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="flash success"><svg class="ic" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> <?= e($flash) ?></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="flash error"><svg class="ic" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> <?= e($flash_error) ?></div>
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

                    <button type="submit" name="tambah_buku" class="btn" style="width: 100%;">Simpan Buku</button>
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
                                    <img src="assets/cover-default.jpg" alt="Sampul buku" style="width: 46px; height: 62px; object-fit: cover; border-radius: 8px;">
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
                                    <button type="submit" class="btn btn-danger"><svg class="ic" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg> Hapus</button>
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
        <strong>TOKO KATA — Panel Admin</strong> &copy; <?= date('Y') ?>
    </footer>
</body>
</html>
