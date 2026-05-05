<?php
session_start();
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi</title>
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
            <a class="cta-btn" href="login.php">Login</a>
        </div>
    </header>

    <div class="banner-red">Registrasi</div>

    <div class="container">
        <div class="card-form">

            <?php
            if (isset($_POST['register'])) {
                $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
                $email    = mysqli_real_escape_string($conn, $_POST['email']);
                $no_hp    = mysqli_real_escape_string($conn, $_POST['no_hp']);
                $password = mysqli_real_escape_string($conn, $_POST['password']);
                $alamat   = mysqli_real_escape_string($conn, $_POST['alamat']);
                $role     = $_POST['role'];

                $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

                if (mysqli_num_rows($cek) > 0) {
                    echo "<div class='status-msg' style='background:#f8d7da; color:#721c24; padding:10px; margin-bottom:15px; border-radius:5px;'>Email sudah terdaftar!</div>";
                } else {

                    $query = "INSERT INTO users (nama, email, no_hp, password, alamat, role) 
                              VALUES ('$nama', '$email', '$no_hp', '$password', '$alamat', '$role')";

                    if (mysqli_query($conn, $query)) {

                        // Simpan id user yang baru dibuat ke session
                        $id_user_baru = mysqli_insert_id($conn);
                        $_SESSION['id_user'] = $id_user_baru;
                        $_SESSION['role']    = $role;

                        // Redirect sesuai role
                        if ($role == 'admin') {
                            header("Location: ../admin/toko.php");
                        } else {
                            header("Location: login.php");
                        }
                        exit;
                    } else {
                        echo "<div class='status-msg' style='background:#f8d7da; color:#721c24; padding:10px; margin-bottom:15px; border-radius:5px;'>Gagal: " . mysqli_error($conn) . "</div>";
                    }
                }
            }
            ?>

            <form action="" method="POST">
                <h3>Data Pribadi</h3>

                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="nama" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>No HP</label>
                    <input type="text" name="no_hp" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>Alamat</label>
                    <input type="text" name="alamat" required>
                </div>

                <div class="role">
                    <label>Login sebagai</label>
                    <select name="role" class="akun" required>
                        <option value="admin">Penjual</option>
                        <option value="user">User</option>
                    </select>
                </div>

                <button type="submit" name="register" class="submit-btn">Selanjutnya</button>
            </form>

        </div>
    </div>

</body>

</html>