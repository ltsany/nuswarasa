<?php
session_start(); 
include "proses/koneksi.php";

$sudah_login = isset($_SESSION['id_user']);
$query = mysqli_query($conn, "SELECT * FROM produk ORDER BY id_produk DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Kuliner | NuswaRasa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="layout/kuliner.css">
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
            <?php if($sudah_login): ?>
                <a class="cta-btn" href="proses/logout.php">Logout</a>
            <?php else: ?>
                <a class="cta-btn" href="proses/login.php">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="banner-red">Kuliner khas kota kami</div>

    <main>
        <div style="text-align: center; margin: 20px 0;">
            <input type="text" id="searchInput" placeholder="Cari kuliner favoritmu..." 
                   style="width: 80%; max-width: 500px; padding: 12px; border-radius: 25px; border: 1px solid #ddd;">
        </div>

        <div class="product-grid">
            <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                <div class="card">
                    <img src="img/<?php echo $row['foto']; ?>" alt="<?php echo $row['nama_produk']; ?>">
                    <div class="card-body">
                        <h3><?php echo $row['nama_produk']; ?></h3>
                        <p class="price">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                        <a class="order-btn" href="detail.php?id=<?php echo $row['id_produk']; ?>">Pesan</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </main>

    <script>
        const searchInput = document.getElementById("searchInput");
        const cards = document.querySelectorAll(".card");

        searchInput.addEventListener("keyup", function() {
            const searchValue = this.value.toLowerCase();

            cards.forEach(card => {
                const title = card.querySelector("h3").textContent.toLowerCase();
                card.style.display = title.includes(searchValue) ? "block" : "none";
            });
        });
    </script>

    <footer>
        <?php include "layout/footer.html" ?>
    </footer>
</body>

</html>