<?php
session_start();
include "proses/koneksi.php";

$id          = $_GET['id'] ?? 0;
$sudah_login = isset($_SESSION['id_user']);

if (empty($id)) {
    header("Location: proses/login.php");
    exit;
}

$query = mysqli_query($conn, "
    SELECT p.*, t.nama_toko, t.alamat AS alamat_toko, t.no_hp AS hp_toko, 
           t.foto AS foto_toko, t.id_toko
    FROM produk p
    LEFT JOIN toko t ON p.id_toko = t.id_toko
    WHERE p.id_produk='$id'
");
$data = mysqli_fetch_assoc($query);

if (!$data) die("Produk tidak ditemukan.");

$id_toko = $data['id_toko'] ?? 0;

$jam_ops = [];
if ($id_toko) {
    $q_jam = mysqli_query($conn, "SELECT * FROM jam_operasional WHERE id_toko='$id_toko'");
    while ($j = mysqli_fetch_assoc($q_jam)) $jam_ops[$j['hari']] = $j;
}

$ulasan = mysqli_query($conn, "
    SELECT u.*, us.nama FROM ulasan u
    JOIN users us ON u.id_user = us.id_user
    WHERE u.id_produk = '$id' ORDER BY u.tanggal DESC
");

$rata_rating = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT AVG(rating) AS rata, COUNT(*) AS total FROM ulasan WHERE id_produk='$id'"
));
$rata  = round($rata_rating['rata'] ?? 0, 1);
$total = $rata_rating['total'] ?? 0;

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
    <title><?php echo htmlspecialchars($data['nama_produk']); ?> - NuswaRasa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="layout/detail.css">
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
            <a href="kontak.php">Kontak Kami</a>
        </nav>
        <div class="header-buttons">
            <?php if ($sudah_login): ?>
                <a class="cta-btn" href="proses/logout.php">Logout</a>
            <?php else: ?>
                <a class="cta-btn" href="proses/login.php">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="banner-red">Detail Produk</div>

    <main class="detail-container">
        <div class="card-produk">
            <div class="foto-wrap">
                <img src="img/<?php echo $data['foto']; ?>" alt="<?php echo $data['nama_produk']; ?>">
            </div>
            <div class="info-wrap">
                <div>
                    <div class="nama-produk"><?php echo htmlspecialchars($data['nama_produk']); ?></div>
                    <div class="rating-wrap">
                        <?php if ($total > 0): ?>
                            <span class="bintang"><?php echo str_repeat('★', round($rata)); ?><?php echo str_repeat('☆', 5 - round($rata)); ?></span>
                            <span class="rating-text"><?php echo $rata; ?>/5 &bull; <?php echo $total; ?> ulasan</span>
                        <?php else: ?>
                            <span class="rating-text">Belum ada ulasan</span>
                        <?php endif; ?>
                    </div>

                    <p class="deskripsi-produk"><?php echo htmlspecialchars($data['deskripsi']); ?></p>
                    <a href="profilToko.php?id=<?php echo $id_toko; ?>" class="toko-strip">
                        <div class="toko-ava">
                            <?php if (!empty($data['foto_toko'])): ?>
                                <img src="img/<?php echo $data['foto_toko']; ?>" alt="">
                            <?php else: ?>
                                <?php echo strtoupper(substr($data['nama_toko'] ?? 'T', 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="toko-text">
                            <div class="toko-nama"> <?php echo htmlspecialchars($data['nama_toko'] ?? '-'); ?></div>
                            <div class="toko-alamat"> <?php echo htmlspecialchars($data['alamat_toko'] ?? '-'); ?></div>
                        </div>
                        <?php if ($status_hari_ini && $status_hari_ini['tutup'] == 'ya'): ?>
                            <div style="background:#fff3cd;color:#856404;padding:10px 14px;
            border-radius:8px;font-size:13px;margin-bottom:12px;font-weight:600;">
                                ⚠️ Toko sedang tutup hari ini. Pesanan akan diproses saat toko buka kembali.
                            </div>
                        <?php endif; ?>
                    </a>
                </div>

                <div>
                    <div class="harga">Rp <?php echo number_format($data['harga']); ?></div>
                    <div class="btn-group">
                        <a href="javascript:history.back()" class="btn-kembali"> Kembali</a>
                        <?php if (!empty($data['hp_toko'])): ?>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $data['hp_toko']); ?>"
                                target="_blank" class="btn-wa"> WA</a>
                        <?php endif; ?>
                        <?php if ($sudah_login): ?>
                            <a href="cart.php?id=<?php echo $data['id_produk']; ?>" class="btn-pesan">Pesan</a>
                        <?php else: ?>
                            <a href="proses/login.php" class="btn-pesan">Pesan</a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
        <?php if (!empty($jam_ops)): ?>
            <div class="card-jam">
                <h4> Jam Operasional</h4>
                <div class="jam-grid">
                    <?php foreach ($hari_list as $hari):
                        $j           = $jam_ops[$hari] ?? null;
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
        <div class="card-ulasan">
            <div class="ulasan-header">
                <h3>Ulasan Kuliner</h3>
                <?php if ($total > 0): ?>
                    <span class="bintang"><?php echo str_repeat('★', round($rata)); ?></span>
                    <span class="rating-text"><?php echo $rata; ?>/5 (<?php echo $total; ?> ulasan)</span>
                <?php endif; ?>
            </div>

            <?php if (mysqli_num_rows($ulasan) == 0): ?>
                <p class="kosong">Belum ada ulasan untuk produk ini.</p>
            <?php endif; ?>

            <?php while ($r = mysqli_fetch_assoc($ulasan)): ?>
                <div class="ulasan-item">
                    <div class="ulasan-top">
                        <span class="ulasan-nama"><?php echo htmlspecialchars($r['nama']); ?></span>
                        <span class="ulasan-tgl"><?php echo date('d M Y', strtotime($r['tanggal'])); ?></span>
                    </div>
                    <div class="ulasan-rating">
                        <?php echo str_repeat('★', $r['rating']);
                        echo str_repeat('☆', 5 - $r['rating']); ?>
                    </div>
                    <p class="ulasan-text"><?php echo htmlspecialchars($r['komentar']); ?></p>
                </div>
            <?php endwhile; ?>
        </div>

    </main>
    <footer>
        <?php include "layout/footer.html" ?>
    </footer>
</body>

</html>