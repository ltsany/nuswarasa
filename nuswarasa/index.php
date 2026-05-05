<?php
session_start();
include "proses/koneksi.php";

$sudah_login = isset($_SESSION['id_user']);

$query = mysqli_query(
    $conn,
    "SELECT * FROM produk WHERE populer='ya' ORDER BY id_produk DESC LIMIT 6"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="layout/beranda.css">
</head>

<body>

    <header>
        <div class="logo">
            <img src="img/logo.png" alt="Logo" height="100">
        </div>
        <nav>
            <?php if ($sudah_login): ?>
                <a href="profil.php">Profil</a>
                <a href="index.php">Beranda</a>
                <a href="kuliner.php">Kuliner</a>
                <a href="kontak.php">Kontak Kami</a>
            <?php else: ?>
                <a href="proses/login.php">Profil</a>
                <a href="index.php">Beranda</a>
                <a href="kuliner.php">Kuliner</a>
                <a href="kontak.php">Kontak Kami</a>
            <?php endif; ?>
        </nav>
        <div class="header-buttons">
            <?php if ($sudah_login): ?>
                <a class="cta-btn" href="proses/logout.php">Logout</a>
            <?php else: ?>
                <a class="cta-btn" href="proses/login.php">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil'): ?>
        <div id="popup-sukses" style="
    position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.5); z-index:9999;
    display:flex; align-items:center; justify-content:center;">
            <div style="
        background:white; border-radius:16px; padding:40px;
        text-align:center; max-width:400px; width:90%;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <span class="material-symbols-outlined" style="
            font-size:80px;
            color:#b30000;
            background:#fff0f0;
            border-radius:50%;
            padding:15px;
            display:inline-block;">
                    check
                </span>

                <h2 style="color:#155724;margin:15px 0 10px;">Pesanan Berhasil!</h2>
                <p style="color:#555;margin-bottom:20px;">
                    Pesanan kamu sudah masuk. Silakan tunggu konfirmasi dari penjual.
                </p>
                <button onclick="document.getElementById('popup-sukses').style.display='none'"
                    style="background:#b30000;color:white;border:none;padding:12px 30px;
            border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;
            width:100%;">
                    OK, Mengerti
                </button>
            </div>
        </div>
    <?php endif; ?>

    <div class="banner-red">Selamat datang!</div>

    <section class="hero">
        <div class="hero-content">
            <img src="img/poster.png" alt="Hero Logo">
        </div>
    </section>

    <main>
        <h2 class="section-title">POPULER</h2>
        <div class="product-grid">
            <?php while ($row = mysqli_fetch_assoc($query)): ?>
                <div class="card">
                    <img src="img/<?php echo $row['foto']; ?>" alt="">
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                        <p class="price">Rp. <?php echo number_format($row['harga']); ?></p>
                        <?php if ($sudah_login): ?>
                            <a class="order-btn" href="detail.php?id=<?php echo $row['id_produk']; ?>">Pesan</a>
                        <?php else: ?>
                            <a class="order-btn" href="detail.php"> Pesan</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>
    <footer>
        <?php include "layout/footer.html" ?>
    </footer>

</body>

</html>