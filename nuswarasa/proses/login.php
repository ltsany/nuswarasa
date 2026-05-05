<?php
include 'koneksi.php';
session_start();

if (isset($_POST['login'])) {
    // Gunakan $conn atau $koneksi sesuai nama variabel di file koneksi.php kamu
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $data  = mysqli_fetch_assoc($query);

    if ($data) {
        // Cek password (Plaintext)
        if ($password == $data['password']) {

            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['nama']    = $data['nama'];
            $_SESSION['role']    = $data['role'];

            // --- BAGIAN BARU: Cek apakah user ini punya toko ---
            $id_user = $data['id_user'];
            $query_toko = mysqli_query($conn, "SELECT id_toko FROM toko WHERE id_user='$id_user'");
            $data_toko  = mysqli_fetch_assoc($query_toko);

            if ($data_toko) {
                $_SESSION['id_toko'] = $data_toko['id_toko']; // Simpan ID toko kalau ada
            } else {
                $_SESSION['id_toko'] = null; // Set null kalau dia cuma pembeli
            }
            // ---------------------------------------------------

            if ($data['role'] == 'admin') {
                header("Location: ../admin/dashAdmin.php");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../layout/log.css">
</head>

<body>

    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="Logo" height="100">
        </div>
        <nav>
            <a href="">Profil</a>
            <a href="../index.php">Beranda</a>
            <a href="../kuliner.php">Kuliner</a>
            <a href="../kontak.php">Kontak Kami</a>
        </nav>
        <div class="header-buttons">
            <button class="cta-btn">Login</button>
        </div>
    </header>

    <div class="banner-red">Login</div>
    <?php if (isset($error)) { ?>
        <div class="status-msg" style="background:#f8d7da;color:#721c24;">
            <?= $error ?>
        </div>
    <?php } ?>

    <div class="container">
        <div class="card-form">
            <form action="" method="POST">
                <h3>Masuk Akun</h3>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" name="login" class="submit-btn">Login</button>

                <p>
                    Belum punya akun? <a href="register.php">Daftar disini</a>
                </p>
            </form>

        </div>
    </div>

</body>

</html>