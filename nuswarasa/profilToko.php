<?php
session_start();
include "proses/koneksi.php";

$id_toko     = $_GET['id'] ?? 0;
$sudah_login = isset($_SESSION['id_user']);

if (empty($id_toko)) {
    header("Location: kulinerUser.php");
    exit;
}

// Ambil data toko
$toko = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT * FROM toko WHERE id_toko='$id_toko'"
));

if (!$toko) die("Toko tidak ditemukan.");

// Ambil jam operasional
$jam_ops = [];
$q_jam = mysqli_query($conn, "SELECT * FROM jam_operasional WHERE id_toko='$id_toko'");
while ($j = mysqli_fetch_assoc($q_jam)) $jam_ops[$j['hari']] = $j;

// Ambil produk toko
$produk = mysqli_query(
    $conn,
    "SELECT * FROM produk WHERE id_toko='$id_toko' ORDER BY id_produk DESC"
);

// Ambil rata rating semua produk toko
$rata_toko = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT AVG(u.rating) AS rata, COUNT(u.id_ulasan) AS total
    FROM ulasan u
    JOIN produk p ON u.id_produk = p.id_produk
    WHERE p.id_toko = '$id_toko'
"));
$rata  = round($rata_toko['rata'] ?? 0, 1);
$total = $rata_toko['total'] ?? 0;

$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$hari_map  = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
];
$hari_ini        = $hari_map[date('l')];
$status_hari_ini = $jam_ops[$hari_ini] ?? null;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> toko - NuswaRasa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
   <link href="layout/pftoko.css" rel="stylesheet">
      
</head>

<body>
    <header>
        <div class="logo">
            <img src="img/logo.png" alt="Logo" height="100">
        </div>
        <nav>
            <a href="profil.php">Profil</a>
            <a href="index.php">Beranda</a>
            <a href="kuliner.php">Kuliner</a>
            <a href="kontakus.php">Kontak Kami</a>
        </nav>
        <div class="header-buttons">
            <?php if ($sudah_login): ?>
                <a class="cta-btn" href="proses/logout.php">Logout</a>
            <?php else: ?>
                <a class="cta-btn" href="proses/login.php">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="banner-red">Profil Toko</div>

    <div class="container">
        <div class="toko-hero">
            <div class="toko-cover">
                <?php if (!empty($toko['foto'])): ?>
                    <img src="img/<?php echo $toko['foto']; ?>" alt="cover">
                <?php endif; ?>
                <div class="toko-avatar">
                    <?php if (!empty($toko['foto'])): ?>
                        <img src="img/<?php echo $toko['foto']; ?>" alt="">
                    <?php else: ?>
                        <?php echo strtoupper(substr($toko['nama_toko'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="toko-body">
                <div class="toko-nama-besar">
                    <?php echo htmlspecialchars($toko['nama_toko']); ?>
                    <span class="badge-status <?php echo $toko['status'] == 'aktif' ? 'badge-aktif' : 'badge-nonaktif'; ?>"
                        style="font-size:13px;margin-left:8px;">
                        <?php echo $toko['status'] == 'aktif' ? '✅ Aktif' : '❌ Nonaktif'; ?>
                    </span>
                </div>

                <div class="toko-meta">
                    <span> <?php echo htmlspecialchars($toko['alamat'] ?? '-'); ?></span>
                    <?php if ($status_hari_ini): ?>
                        <?php if ($status_hari_ini['tutup'] == 'ya'): ?>
                            <span class="badge-status badge-tutup">🔴 Tutup Hari Ini</span>
                        <?php else: ?>
                            <span class="badge-status badge-buka">
                                🟢 Buka <?php echo substr($status_hari_ini['jam_buka'], 0, 5); ?> - <?php echo substr($status_hari_ini['jam_tutup'], 0, 5); ?>
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($toko['deskripsi'])): ?>
                    <p class="toko-desc"><?php echo htmlspecialchars($toko['deskripsi']); ?></p>
                <?php endif; ?>

                <div class="toko-actions">
                    <?php if (!empty($toko['no_hp'])): ?>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $toko['no_hp']); ?>"
                            target="_blank" class="btn-wa"> WhatsApp Toko</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-num"><?php echo mysqli_num_rows($produk); ?></div>
                <div class="stat-label">Total Produk</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?php echo $total; ?></div>
                <div class="stat-label">Total Ulasan</div>
            </div>
            <div class="stat-card">
                <div class="stat-num">
                    <?php if ($rata > 0): ?>
                        <?php echo $rata; ?> ★
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
                <div class="stat-label">Rating Toko</div>
            </div>
        </div>
        <?php if (!empty($jam_ops)): ?>
            <div class="card">
                <h3> Jam Operasional</h3>
                <div class="jam-grid">
                    <?php foreach ($hari_list as $hari):
                        $j = $jam_ops[$hari] ?? null;
                        $is_hari_ini = ($hari == $hari_ini);
                    ?>
                        <div class="jam-item <?php echo $is_hari_ini ? 'hari-ini' : ''; ?>">
                            <div class="hari-nama"><?php echo substr($hari, 0, 3); ?></div>
                            <?php if (!$j || $j['tutup'] == 'ya'): ?>
                                <div class="libur">Tutup</div>
                            <?php else: ?>
                                <div class="jam-waktu"><?php echo substr($j['jam_buka'], 0, 5); ?></div>
                                <div class="jam-waktu"><?php echo substr($j['jam_tutup'], 0, 5); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="card">
            <h3>Menu Produk</h3>
            <?php
            mysqli_data_seek($produk, 0);
            ?>
            <?php if (mysqli_num_rows($produk) == 0): ?>
                <p style="color:gray;text-align:center;padding:20px;">Belum ada produk.</p>
            <?php else: ?>
                <div class="produk-grid">
                    <?php while ($p = mysqli_fetch_assoc($produk)): ?>
                        <a href="detail.php?id=<?php echo $p['id_produk']; ?>" class="produk-card">
                            <img src="img/<?php echo $p['foto']; ?>" alt="<?php echo $p['nama_produk']; ?>">
                            <div class="produk-info">
                                <div class="produk-kategori"><?php echo htmlspecialchars($p['kategori'] ?? ''); ?></div>
                                <div class="produk-nama"><?php echo htmlspecialchars($p['nama_produk']); ?></div>
                                <div class="produk-harga">Rp <?php echo number_format($p['harga']); ?></div>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</body>

</html>