<?php
session_start();
include "proses/koneksi.php";

if (!isset($_SESSION['id_user'])) {
    header("Location: proses/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil data user
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_assoc($query_user);

// Update profil
if (isset($_POST['update'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $hp     = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email  = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    mysqli_query($conn, "UPDATE users SET nama='$nama', no_hp='$hp', email='$email', alamat='$alamat' WHERE id_user='$id_user'");
    header("Location: profil.php?pesan=berhasil");
    exit;
}

$pesanan = mysqli_query($conn, "
    SELECT ps.id_pesanan, ps.status, ps.bukti, ps.metode_pembayaran, ps.sudah_diulas,
           pr.nama_produk, pr.foto, pr.harga, pr.id_produk
    FROM pesanan ps
    JOIN produk pr ON ps.id_produk = pr.id_produk
    WHERE ps.id_user = '$id_user'
    ORDER BY ps.id_pesanan DESC
");

// Statistik
$total_pesanan  = mysqli_num_rows($pesanan);
$selesai        = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM pesanan WHERE id_user='$id_user' AND status='selesai'"
))['total'] ?? 0;
$total_belanja  = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(pr.harga + 10000) AS total 
     FROM pesanan ps JOIN produk pr ON ps.id_produk = pr.id_produk
     WHERE ps.id_user='$id_user' AND ps.status='selesai'"
))['total'] ?? 0;

// Reset pointer pesanan
mysqli_data_seek($pesanan, 0);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200..1000&display=swap" rel="stylesheet">
    <link href="layout/profil.css" rel="stylesheet">

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
            <a class="cta-btn" href="proses/logout.php">Logout</a>
        </div>
    </header>

    <div class="banner-red">Profil Saya</div>
    <div class="modal-overlay" id="modalEdit">
        <div class="modal">
            <h3>Edit Profil</h3>
            <form method="POST">
                <label>Nama Lengkap</label>
                <input type="text" name="nama"
                    value="<?php echo htmlspecialchars($user['nama']); ?>" required>

                <label>No Telepon</label>
                <input type="text" name="no_hp"
                    value="<?php echo htmlspecialchars($user['no_hp'] ?? ''); ?>">

                <label>Email</label>
                <input type="text" name="email"
                    value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">

                <label>Alamat</label>
                <textarea name="alamat" rows="3"><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>

                <div class="modal-buttons">
                    <button type="button" onclick="tutupModal()">Batal</button>
                    <button type="submit" name="update" class="btn-simpan">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil'): ?>
        <div class="alert-sukses"> Profil berhasil diperbarui!</div>
    <?php endif; ?>

    <div class="container">

        <!-- Profile Top -->
        <div class="profile-top">
            <div class="avatar">
                <?php echo strtoupper(substr($user['nama'], 0, 1)); ?>
            </div>
            <div>
                <div class="name"><?php echo htmlspecialchars($user['nama']); ?></div>
                <div class="email"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <button class="btn-edit" onclick="bukaModal()">Edit Profil</button>
        </div>

        <div class="stats">
            <div class="stat-item">
                <span class="stat-num"><?php echo $total_pesanan; ?></span>
                <span class="stat-label">Total Pesanan</span>
            </div>
            <div class="stat-item">
                <span class="stat-num"><?php echo $selesai; ?></span>
                <span class="stat-label">Pesanan Selesai</span>
            </div>
            <div class="stat-item">
                <span class="stat-num">Rp <?php echo number_format($total_belanja); ?></span>
                <span class="stat-label">Total Belanja</span>
            </div>
        </div>
        <div class="card">
            <h3 class="card-title">Informasi Pribadi</h3>
            <div class="grid">
                <div class="item">
                    <label>Nama Lengkap</label>
                    <p><?php echo htmlspecialchars($user['nama']); ?></p>
                </div>
                <div class="item">
                    <label>No Telepon</label>
                    <p><?php echo htmlspecialchars($user['no_hp'] ?? '-'); ?></p>
                </div>
                <div class="item">
                    <label>Email</label>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="item">
                    <label>Alamat</label>
                    <p><?php echo htmlspecialchars($user['alamat'] ?? '-'); ?></p>
                </div>
            </div>
        </div>

        <?php while ($row = mysqli_fetch_assoc($pesanan)):
            $status   = $row['status'];
            $label    = [
                'menunggu' => 'Menunggu',
                'proses'   => 'Diproses',
                'kirim'    => 'Dikirim',
                'selesai'  => 'Selesai',
            ];
        ?>
            <div class="order-item">
                <img src="img/<?php echo $row['foto']; ?>" alt="foto">
                <div class="order-info">
                    <div class="order-name"><?php echo htmlspecialchars($row['nama_produk']); ?></div>
                    <div class="order-harga">Rp <?php echo number_format($row['harga'] + 10000); ?></div>
                </div>
                <span class="badge status-<?php echo $status; ?>">
                    <?php echo $label[$status] ?? ucfirst($status); ?>
                </span>

                <?php if ($status == 'selesai' && $row['sudah_diulas'] == 'tidak'): ?>
                    <button class="btn-ulasan" onclick="bukaUlasan(
                '<?php echo $row['id_pesanan']; ?>',
                '<?php echo $row['id_produk']; ?>',
                '<?php echo addslashes($row['nama_produk']); ?>'
            )">⭐ Beri Ulasan</button>
                <?php elseif ($status == 'selesai' && $row['sudah_diulas'] == 'ya'): ?>
                    <span class="sudah-ulas">Sudah Diulas</span>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="modal-overlay" id="modalUlasan">
        <div class="modal">
            <h3>Beri Ulasan</h3>
            <p id="nama-produk-ulasan" style="color:gray;margin-bottom:10px;"></p>

            <form method="POST" action="proses/simpan_ulasan.php">
                <input type="hidden" name="id_pesanan" id="input-id-pesanan">
                <input type="hidden" name="id_produk" id="input-id-produk">

                <label>Rating</label>
                <div class="star-rating">
                    <input type="radio" name="rating" value="5" id="s5"><label for="s5">⭐</label>
                    <input type="radio" name="rating" value="4" id="s4"><label for="s4">⭐</label>
                    <input type="radio" name="rating" value="3" id="s3"><label for="s3">⭐</label>
                    <input type="radio" name="rating" value="2" id="s2"><label for="s2">⭐</label>
                    <input type="radio" name="rating" value="1" id="s1"><label for="s1">⭐</label>
                </div>

                <label style="margin-top:10px;">Komentar</label>
                <textarea name="komentar" rows="4" placeholder="Tulis ulasanmu..." required></textarea>

                <div class="modal-buttons">
                    <button type="button" onclick="tutupUlasan()">Batal</button>
                    <button type="submit" name="kirim_ulasan" class="btn-simpan">Kirim</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaModal() {
            document.getElementById('modalEdit').classList.add('active');
        }

        function tutupModal() {
            document.getElementById('modalEdit').classList.remove('active');
        }

        document.getElementById('modalEdit').addEventListener('click', function(e) {
            if (e.target === this) tutupModal();
        });

        function bukaUlasan(id_pesanan, id_produk, nama_produk) {
            document.getElementById('input-id-pesanan').value = id_pesanan;
            document.getElementById('input-id-produk').value = id_produk;
            document.getElementById('nama-produk-ulasan').innerText = nama_produk;
            document.getElementById('modalUlasan').classList.add('active');
        }

        function tutupUlasan() {
            document.getElementById('modalUlasan').classList.remove('active');
        }

        document.getElementById('modalUlasan').addEventListener('click', function(e) {
            if (e.target === this) tutupUlasan();
        });
    </script>
</body>

</html>