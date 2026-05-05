<?php
session_start();
include "../proses/koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../proses/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$cek = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT id_toko FROM toko WHERE id_user='$id_user' LIMIT 1"
));
$id_toko = $cek['id_toko'] ?? 0;

if (empty($id_toko)) {
    die("Akun ini belum terhubung ke toko.");
}

$nama_admin = $_SESSION['nama'] ?? 'Admin';

$total_produk = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM produk WHERE id_toko='$id_toko'"
))['total'] ?? 0;

$total_pesanan = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM pesanan WHERE id_toko='$id_toko'"
))['total'] ?? 0;

$total_pendapatan = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(pr.harga + 10000) AS total 
     FROM pesanan ps 
     JOIN produk pr ON ps.id_produk = pr.id_produk
     WHERE ps.id_toko='$id_toko' AND ps.status='selesai'"
))['total'] ?? 0;

$pesanan_terbaru = mysqli_query(
    $conn,
    "SELECT ps.*, pr.nama_produk, u.nama AS nama_user
     FROM pesanan ps
     JOIN produk pr ON ps.id_produk = pr.id_produk
     JOIN users u ON ps.id_user = u.id_user
     WHERE ps.id_toko='$id_toko'
     ORDER BY ps.id_pesanan DESC
     LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin NuswaRasa</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="../layout/adstyle.css" rel="stylesheet">
</head>

<body>

    <div class="sidebar">
        <div class="logo"><i class='bx bxs-store-alt'></i> NUSWARASA</div>
        <div class="menu">
            <a href="dashAdmin.php" class="active">
                <i class='bx bxs-dashboard'></i> Dashboard
            </a>
            <a href="kelola_produk.php">
                <i class='bx bxs-food-menu'></i> Kelola Produk
            </a>
            <a href="kelola_pesanan.php">
                <i class='bx bxs-shopping-bag'></i> Kelola Pesanan
            </a>
            <a href="toko.php">
                <i class='bx bxs-user-detail'></i> Profil Toko
            </a>
            <div class="logout">
                <a href="../proses/logout.php">
                    <i class='bx bx-log-out'></i> Logout
                </a>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <div class="title"></div>
            <div style="font-weight: 700;">
                Halo, <?php echo htmlspecialchars($nama_admin); ?>
                <i class='bx bxs-user-circle'></i>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Total Produk</h3>
                <p><?php echo $total_produk; ?></p>
            </div>
            <div class="card">
                <h3>Pesanan Masuk</h3>
                <p><?php echo $total_pesanan; ?></p>
            </div>
            <div class="card">
                <h3>Pendapatan</h3>
                <p>Rp <?php echo number_format($total_pendapatan); ?></p>
            </div>
        </div>

        <div class="content-box">
            <h3>Pesanan Terbaru</h3>
            <table>
                <tr>
                    <th>Produk</th>
                    <th>Pembeli</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

                <?php if (mysqli_num_rows($pesanan_terbaru) == 0): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">Belum ada pesanan</td>
                    </tr>
                <?php endif; ?>

                <?php while ($row = mysqli_fetch_assoc($pesanan_terbaru)): ?>
                    <?php
                    $status = $row['status'];
                    $label = [
                        'menunggu' => 'Menunggu',
                        'proses'   => 'Diproses',
                        'kirim'    => 'Dikirim',
                        'selesai'  => 'Selesai',
                    ];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_user']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $status; ?>">
                                <?php echo $label[$status] ?? ucfirst($status); ?>
                            </span>
                        </td>
                        <td>
                            <a href="kelola_pesanan.php" class="btn">Detail</a>
                        </td>
                    </tr>
                <?php endwhile; ?>

            </table>
        </div>
    </div>

</body>

</html>