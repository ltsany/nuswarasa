<?php
session_start();
include "proses/koneksi.php";

require 'proses/PHPMailer/PHPMailer.php';
require 'proses/PHPMailer/SMTP.php';
require 'proses/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$sudah_login  = isset($_SESSION['id_user']);
$pesan_sukses = '';
$pesan_error  = '';

if (isset($_POST['kirim'])) {
    $nama  = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $pesan = htmlspecialchars($_POST['pesan']);

    if (empty($nama) || empty($email) || empty($pesan)) {
        $pesan_error = "Semua field harus diisi!";
    } else {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'lutfysany17@gmail.com'; // ← ganti
            $mail->Password   = 'mcwu epbh kdfc msyl';      // ← App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom($email, $nama);
            $mail->addAddress('lutfysany17@gmail.com');

            $mail->isHTML(true);
            $mail->Subject = "Pesan dari $nama - NuswaRasa";
            $mail->Body    = "
                <div style='font-family:sans-serif;max-width:500px;'>
                    <h2 style='color:#b30000;'>Pesan Baru dari Website NuswaRasa</h2>
                    <table style='width:100%;border-collapse:collapse;'>
                        <tr>
                            <td style='padding:8px;font-weight:bold;'>Nama</td>
                            <td style='padding:8px;'>$nama</td>
                        </tr>
                        <tr style='background:#f9f9f9;'>
                            <td style='padding:8px;font-weight:bold;'>Email</td>
                            <td style='padding:8px;'>$email</td>
                        </tr>
                        <tr>
                            <td style='padding:8px;font-weight:bold;'>Pesan</td>
                            <td style='padding:8px;'>$pesan</td>
                        </tr>
                    </table>
                </div>
            ";

            $mail->send();
            $pesan_sukses = "Pesan berhasil dikirim! Kami akan segera menghubungi kamu.";
        } catch (Exception $e) {
            $pesan_error = "Gagal mengirim pesan: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - NuswaRasa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="layout/kontak.css">
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

    <div class="banner-red">Hubungi Kami</div>

    <div class="container">

        <!-- Info Kontak -->
        <div class="card">
            <div class="title">Informasi Kontak</div>

            <div class="info">
                <label>Alamat</label>
                <p>Jl. Nusantara No.12, Indonesia</p>
            </div>
            <div class="info">
                <label>Email</label>
                <p>nuswarasa@email.com</p>
            </div>
            <div class="info">
                <label> No Telepon</label>
                <p>08123456789</p>
            </div>
            <div class="info">
                <label> Jam Operasional</label>
                <p>Senin - Sabtu (08.00 - 20.00)</p>
            </div>
        </div>

        <!-- Form Kirim Pesan -->
        <div class="card">
            <div class="title">Kirim Pesan</div>

            <?php if ($pesan_sukses): ?>
                <div style="background:#d4edda;color:#155724;padding:12px 16px;
                    border-radius:8px;margin-bottom:16px;font-weight:600;">
                    ✅ <?php echo $pesan_sukses; ?>
                </div>
            <?php endif; ?>

            <?php if ($pesan_error): ?>
                <div style="background:#ffebee;color:#e53935;padding:12px 16px;
                    border-radius:8px;margin-bottom:16px;font-weight:600;">
                    ❌ <?php echo $pesan_error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Nama</label>
                    <input type="text" name="nama" placeholder="Masukkan nama"
                        value="<?php echo $sudah_login ? htmlspecialchars($_SESSION['nama'] ?? '') : ''; ?>" required>
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Masukkan email" required>
                </div>

                <div class="input-group">
                    <label>Pesan</label>
                    <textarea name="pesan" placeholder="Tulis pesan kamu..." required></textarea>
                </div>

                <button type="submit" name="kirim">Kirim Pesan</button>
            </form>
        </div>

    </div>

    <!-- WA Floating -->
    <a href="https://wa.me/6285786848908?text=Halo%20saya%20ingin%20bertanya%20tentang%20NuswaRasa"
        class="wa-floating" target="_blank">
        <span>💬 Chat Kami</span>
    </a>

    <footer>
        <?php include "layout/footer.html" ?>
    </footer>
</body>

</html>