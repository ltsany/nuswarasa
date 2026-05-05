<?php
session_start();

include "proses/koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Prepared statement untuk hindari SQL injection
$stmt = mysqli_prepare($conn, "SELECT * FROM produk WHERE id_produk = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - NuswaRasa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="layout/cart.css" rel="stylesheet">
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
            <a class="cta-btn" href="proses/logout.php">Logout</a>
        </div>
    </header>

    <div class="banner-red">Keranjang Nuswarasa</div>

    <section class="cart-container">
        <div class="cart-left">
            <?php if (mysqli_num_rows($query) === 0): ?>
                <p style="padding: 20px;">Produk tidak ditemukan.</p>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <div class="cart-item" data-id="<?php echo (int)$row['id_produk']; ?>">
                        <img src="img/<?php echo htmlspecialchars($row['foto']); ?>" alt="Produk">
                        <div class="cart-info">
                            <h3><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                            <p class="price" data-harga="<?php echo (int)$row['harga']; ?>">
                                Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>
                            </p>
                            <div class="qty">
                                <button class="btn-qty minus">-</button>
                                <span class="qty-val">1</span>
                                <button class="btn-qty plus">+</button>
                            </div>
                        </div>
                        <div class="cart-total">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                        <button class="remove">Hapus</button>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <div class="cart-right">
            <h2>Ringkasan Belanja</h2>
            <div class="summary-row">
                <span>Total Item</span>
                <span id="total-item">0</span>
            </div>
            <div class="summary-row">
                <span>Total Harga</span>
                <span id="total-harga">Rp 0</span>
            </div>
            <a id="btn-checkout" class="checkout-btn" href="checkout.php?id=<?php echo (int)$id; ?>&qty=1">
                Checkout Sekarang
            </a>
        </div>
    </section>

    <script>
        function updateSummary() {
            let totalItem = 0;
            let totalHarga = 0;
            let lastId = <?php echo (int)$id; ?>;
            let lastQty = 1;

            document.querySelectorAll(".cart-item").forEach(item => {
                const qtyVal = parseInt(item.querySelector(".qty-val").innerText);
                const harga = parseInt(item.querySelector(".price").getAttribute("data-harga"));
                const itemId = item.getAttribute("data-id");
                const totalItemPrice = harga * qtyVal;

                item.querySelector(".cart-total").innerText =
                    "Rp " + totalItemPrice.toLocaleString("id-ID");

                totalItem += qtyVal;
                totalHarga += totalItemPrice;
                lastId = itemId;
                lastQty = qtyVal;
            });

            document.getElementById("total-item").innerText = totalItem;
            document.getElementById("total-harga").innerText =
                "Rp " + totalHarga.toLocaleString("id-ID");

            // Update link checkout dengan id dan qty yang sesuai
            document.getElementById("btn-checkout")
                .setAttribute("href", "checkout.php?id=" + lastId + "&qty=" + lastQty);
        }

        document.querySelectorAll(".cart-item").forEach(item => {
            const plus = item.querySelector(".plus");
            const minus = item.querySelector(".minus");
            const qtyVal = item.querySelector(".qty-val");

            plus.addEventListener("click", () => {
                qtyVal.innerText = parseInt(qtyVal.innerText) + 1;
                updateSummary();
            });

            minus.addEventListener("click", () => {
                let current = parseInt(qtyVal.innerText);
                if (current > 1) {
                    qtyVal.innerText = current - 1;
                    updateSummary();
                }
            });

            //  hapus keranjang
            item.querySelector(".remove").addEventListener("click", () => {
                item.remove();
                updateSummary();

                // keranjang kosong, nonaktifkan tombol checkout
                const remaining = document.querySelectorAll(".cart-item").length;
                if (remaining === 0) {
                    const btn = document.getElementById("btn-checkout");
                    btn.removeAttribute("href");
                    btn.style.opacity = "0.5";
                    btn.style.pointerEvents = "none";
                    document.getElementById("total-item").innerText = "0";
                    document.getElementById("total-harga").innerText = "Rp 0";
                }
            });
        });

        updateSummary();
    </script>
</body>

</html>