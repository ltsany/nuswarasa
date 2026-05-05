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

if (isset($_POST['ubah_status'])) {
    $id_pesanan = $_POST['id_pesanan'];
    $status     = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE pesanan SET status='$status' WHERE id_pesanan='$id_pesanan'");
    header("Location: kelola_pesanan.php");
    exit;
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

if (!empty($search)) {
    $where = "AND (u.nama LIKE '%$search%' OR pr.nama_produk LIKE '%$search%')";
} else {
    $where = "";
}

$query = mysqli_query($conn, "
    SELECT ps.*, pr.nama_produk, pr.harga, u.nama AS nama_user 
    FROM pesanan ps
    JOIN produk pr ON ps.id_produk = pr.id_produk
    JOIN users u ON ps.id_user = u.id_user
    WHERE ps.id_toko = '$id_toko' $where
    ORDER BY ps.id_pesanan DESC
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin NuswaRasa</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="../layout/pesanan.css" rel="stylesheet">
</head>

<body>
    <div class="sidebar">
        <div class="logo"><i class='bx bxs-store-alt'></i> NUSWARASA</div>
        <div class="menu">
            <a href="dashAdmin.php"><i class='bx bxs-dashboard'></i> Dashboard</a>
            <a href="kelola_produk.php"><i class='bx bxs-food-menu'></i> Kelola Produk</a>
            <a href="kelola_pesanan.php" class="active"><i class='bx bxs-shopping-bag'></i> Kelola Pesanan</a>
            <a href="toko.php"><i class='bx bxs-user-detail'></i> Profil Toko</a>
            <div class="logout">
                <a href="../proses/logout.php"><i class='bx bx-log-out'></i> Logout</a>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h2>Kelola Pesanan</h2>
            <form method="GET">
                <input type="text" name="search" placeholder="Cari pesanan..." class="search"
                    value="<?php echo htmlspecialchars($search); ?>">
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Pembeli</th>
                    <th>Produk</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Bukti</th>
                    <th>Status</th>
                    <th>Ubah Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($query) == 0): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">Belum ada pesanan</td>
                    </tr>
                <?php endif; ?>

                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <?php
                    $harga  = $row['harga'] ?? 0;
                    $total  = $harga + 10000;
                    $status = $row['status'];

                    $tanggal = '-';
                    if (!empty($row['tanggal']) && $row['tanggal'] != '0000-00-00 00:00:00') {
                        $tanggal = date('d M Y', strtotime($row['tanggal']));
                    } elseif (!empty($row['created_at'])) {
                        $tanggal = date('d M Y', strtotime($row['created_at']));
                    }

                    $label = [
                        'menunggu' => 'Menunggu',
                        'proses'   => 'Diproses',
                        'kirim'    => 'Dikirim',
                        'selesai'  => 'Selesai',
                    ];
                    ?>
                    <tr>
                        <td>#<?php echo $row['id_pesanan']; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_user'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_produk'] ?? '-'); ?></td>
                        <td><?php echo $tanggal; ?></td>
                        <td>Rp <?php echo number_format($total); ?></td>
                        <td>
                            <?php if (!empty($row['bukti'])): ?>
                                <a href="../img/<?php echo $row['bukti']; ?>" target="_blank">Lihat Bukti</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status <?php echo $status; ?>">
                                <?php echo $label[$status] ?? ucfirst($status); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="id_pesanan" value="<?php echo $row['id_pesanan']; ?>">
                                <select name="status" class="statusSelect" onchange="this.form.submit()">
                                    <option value="menunggu" <?php echo $status == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                    <option value="proses" <?php echo $status == 'proses'   ? 'selected' : ''; ?>>Diproses</option>
                                    <option value="kirim" <?php echo $status == 'kirim'    ? 'selected' : ''; ?>>Dikirim</option>
                                    <option value="selesai" <?php echo $status == 'selesai'  ? 'selected' : ''; ?>>Selesai</option>
                                </select>
                                <input type="hidden" name="ubah_status" value="1">
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.querySelectorAll(".statusSelect").forEach(select => {
            select.addEventListener("change", function() {
                let status = this.value;
                let badge = this.closest("tr").querySelector(".status");
                badge.className = "status " + status;
                badge.innerText = this.options[this.selectedIndex].text;
            });
        });
    </script>
</body>

</html>