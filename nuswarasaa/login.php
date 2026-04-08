<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="layout/log.css">
</head>
<body>

<header>
    <div class="logo">
        <img src="img/logo.png" alt="Logo" height="100">
    </div>
    <nav>
        <a href="#">Profil</a>
        <a href="#">Beranda</a>
        <a href="#">Kuliner</a>
        <a href="#">Kontak Kami</a>
    </nav>
    <div class="header-buttons">
        <button class="cart-btn">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF"><path d="M280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM246-720l66 280h380l66-280H246Zm-46-80h660q17 0 28.5 12.5T897-758l-84 354q-6 24-25.5 39T742-350H282q-26 0-45.5-15T211-404L105-847q-5-20-22-31.5T46-890H0v-70h66q26 0 45.5 15t25.5 45l23 90ZM312-440h380-380Z"/></svg>
        </button>
        <button class="cta-btn">Login</button>
    </div>
</header>

<div class="banner-red">Registrasi</div>

<div class="container">
    <div class="card-form">

        <?php
        if (isset($_POST['register'])) {
            $nama = $_POST['nama'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $password = password_hash($_POST['role']);

            $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND role='$role'");
            
            if(mysqli_num_rows($cek) > 0){
                echo "<div class='status-msg' style='background:#f8d7da; color:#721c24; padding:10px; margin-bottom:15px; border-radius:5px;'>Email sudah terdaftar!</div>";
            } else {

                $query = "INSERT INTO users (nama, email, password, role) 
                          VALUES ('$nama', '$email', '$password', '$role')";
                
                if (mysqli_query($conn, $query)) {
                    echo "<div class='status-msg' style='background:#d4edda; color:#155724; padding:10px; margin-bottom:15px; border-radius:5px;'>Registrasi Berhasil!</div>";
                } else {
                    echo "<div class='status-msg' style='background:#f8d7da; color:#721c24; padding:10px; margin-bottom:15px; border-radius:5px;'>Gagal: " . mysqli_error($conn) . "</div>";
                }
            }

            if($data){
                // cek password hash kalau pakai password_hash
                if(password_verify($password, $data['password'])){
                    session_start();
                    $_SESSION['id_user'] = $data['id_user'];
                    $_SESSION['role'] = $data['role'];
                    echo "Login berhasil sebagai " . $data['role'];
                    // redirect sesuai role
                } else {
                    echo "Password salah";
                }
            }else{
                echo "Email atau role salah";
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
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="role">
                <label>Login sebagai</label>
              <select name="role" class="akun" required>
                <option value="admin">Admin</option>
                <option value="user">User</option>
              </select>
            </div>

            <button type="submit" name="register" class="submit-btn">Selanjutnya</button>
        </form>

    </div>
</div>

</body>
</html>