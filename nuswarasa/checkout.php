<?php
session_start();
include "proses/koneksi.php";

if (!isset($_SESSION['id_user'])) {
    header("Location: proses/login.php");
    exit;
}

// Tangkap ID dan QTY dari URL
$id  = $_GET['id'] ?? 0;
$qty = $_GET['qty'] ?? 1;

$query = mysqli_query($conn, "SELECT p.*, p.id_toko FROM produk p WHERE p.id_produk='$id'");
$data = mysqli_fetch_assoc($query);

$harga_satuan = $data['harga'] ?? 0;
$subtotal = $harga_satuan * $qty;
$ongkir = 2000;
$total = $subtotal + $ongkir;
$id_toko = $data['id_toko'] ?? 0;

$id_user = $_SESSION['id_user'];
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_assoc($query_user);

if (isset($_POST['buat'])) {
    $payment = $_POST['payment'];
    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $hp     = mysqli_real_escape_string($conn, $_POST['hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $bukti  = $_FILES['bukti']['name'];
    $tmp    = $_FILES['bukti']['tmp_name'];

    if (!empty($bukti)) {
        move_uploaded_file($tmp, "img/" . $bukti);
        mysqli_query($conn, "INSERT INTO pesanan 
        (id_user, id_produk, id_toko, jumlah, metode_pembayaran, bukti, status, nama_penerima)
        VALUES
        ('$id_user', '$id', '$id_toko', '$qty', '$payment', '$bukti', 'menunggu', '$nama')");

        header("Location: index.php?pesan=berhasil");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Checkout | NuswaRasa</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="layout/checkout.css">
</head>

<body>
    <div class="banner-red">Checkout</div>
    <div class="container">
        <form method="POST" enctype="multipart/form-data" class="checkout-layout">
            <div class="left-content">
                <div class="box">
                    <h3>Alamat Pengiriman</h3>
                    <input type="text" name="nama" value="<?php echo $user['nama']; ?>" placeholder="Nama Penerima" required>
                    <input type="text" name="hp" value="<?php echo $user['no_hp']; ?>" placeholder="No HP" required>
                    <textarea name="alamat" required><?php echo $user['alamat']; ?></textarea>
                </div>

                <div class="box" style="margin-top:20px">
                    <h3>Pembayaran</h3>
                    <div class="payment-options">
                    <label class="radio-card">
                     <input type="radio" name="payment" value="bank" onclick="showPaymentDetail('bank')">
                     Transfer Bank
                     </label>

                     <label class="radio-card"> 
                       <input type="radio" name="payment" value="ewallet" onclick="showPaymentDetail('ewallet')">
                       E-Wallet
                       </label>
                      <label class="radio-card">
                          <input type="radio" name="payment" value="qris" onclick="showPaymentDetail('qris')">
                             QRIS
                          </label>
                  </div>

                <div id="bank" class="payment-details" style="display:none;">
                 <p>Pilih Bank:</p>
                 <select name="bank_option">
                    <option value="bca">BCA - 123456789 a/n NuswaRasa</option>
                    <option value="bri">BRI - 987654321 a/n NuswaRasa</option>
                    <option value="mandiri">Mandiri - 1122334455 a/n NuswaRasa</option>
              </select>
                </div>

                <div id="ewallet" class="payment-details" style="display:none;">
                     <p>Pilih E-Wallet:</p>
                     <select name="ewallet_option">
                          <option value="dana">DANA - 0812345678</option>
                         <option value="ovo">OVO - 0812345678</option>
                         <option value="gopay">GoPay - 0812345678</option>
                      </select>
                </div>

                <div id="qris" class="payment-details" style="display:none;">
                 <p>Scan QRIS:</p>
                   <img src="img/qris.jpg" width="200">
                </div>
                    <div id="payment-info" class="payment-details"></div>
                    <input type="file" name="bukti" required style="margin-top:20px">
                </div>
            </div>

            <div class="sidebar-summary">
                <div class="box sticky-box">
                    <h3>Ringkasan</h3>
                    <div class="order-item">
                        <span><?php echo $data['nama_produk']; ?> (x<?php echo $qty; ?>)</span>
                        <span>Rp <?php echo number_format($subtotal); ?></span>
                    </div>
                    <div class="order-item">
                        <span>Ongkir</span>
                        <span>Rp <?php echo number_format($ongkir); ?></span>
                    </div>
                    <hr>
                    <div class="order-item total-price">
                        <span>Total</span>
                        <span>Rp <?php echo number_format($total); ?></span>
                    </div>
                    <button type="submit" name="buat" class="checkout-btn">Buat Pesanan</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function showPaymentDetail(type) {
         document.getElementById('bank').style.display = 'none';
        document.getElementById('ewallet').style.display = 'none';
        document.getElementById('qris').style.display = 'none';
         document.getElementById(type).style.display = 'block';
}
    </script>          
</body>

</html>