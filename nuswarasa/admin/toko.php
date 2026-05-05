<?php
session_start();
include "../proses/koneksi.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../proses/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

$query_toko = mysqli_query($conn, "SELECT * FROM toko WHERE id_user='$id_user'");
$toko = mysqli_fetch_assoc($query_toko);
$id_toko = $toko['id_toko'] ?? 0;

$pesan = "";

// Simpan profil toko
if (isset($_POST['simpan'])) {
    $nama_toko = mysqli_real_escape_string($conn, $_POST['nama_toko']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $alamat    = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_hp     = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $status    = $_POST['status'];

    $foto = $toko['foto'] ?? '';
    if (!empty($_FILES['foto']['name'])) {
        $foto = $_FILES['foto']['name'];
        move_uploaded_file($_FILES['foto']['tmp_name'], "../img/" . $foto);
    }

    if ($toko) {
        mysqli_query($conn, "UPDATE toko SET 
        nama_toko='$nama_toko', deskripsi='$deskripsi',
        alamat='$alamat', no_hp='$no_hp', foto='$foto', status='$status'
        WHERE id_user='$id_user'");
    } else {
        mysqli_query($conn, "INSERT INTO toko 
        (id_user, nama_toko, deskripsi, alamat, no_hp, foto, status)
        VALUES 
        ('$id_user', '$nama_toko', '$deskripsi', '$alamat', '$no_hp', '$foto', '$status')");
    }

    $pesan = "Profil toko berhasil disimpan!";
    $query_toko = mysqli_query($conn, "SELECT * FROM toko WHERE id_user='$id_user'");
    $toko = mysqli_fetch_assoc($query_toko);
    $id_toko = $toko['id_toko'] ?? 0;
}

// Simpan jam operasional
if (isset($_POST['simpan_jam'])) {
    $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    foreach ($hari_list as $hari) {
        $key      = strtolower($hari);
        $tutup    = isset($_POST['tutup_' . $key]) ? 'ya' : 'tidak';
        $jam_buka = mysqli_real_escape_string($conn, $_POST['buka_' . $key] ?? '08:00');
        $jam_tutup = mysqli_real_escape_string($conn, $_POST['tutup_jam_' . $key] ?? '17:00');

        // Cek apakah sudah ada
        $cek = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT id FROM jam_operasional WHERE id_toko='$id_toko' AND hari='$hari'"
        ));

        if ($cek) {
            mysqli_query($conn, "UPDATE jam_operasional SET 
                jam_buka='$jam_buka', jam_tutup='$jam_tutup', tutup='$tutup'
                WHERE id_toko='$id_toko' AND hari='$hari'");
        } else {
            mysqli_query($conn, "INSERT INTO jam_operasional (id_toko, hari, jam_buka, jam_tutup, tutup)
                VALUES ('$id_toko', '$hari', '$jam_buka', '$jam_tutup', '$tutup')");
        }
    }

    $pesan = "Jam operasional berhasil disimpan!";
}

// Ambil jam operasional
$jam_ops = [];
if ($id_toko) {
    $q_jam = mysqli_query($conn, "SELECT * FROM jam_operasional WHERE id_toko='$id_toko'");
    while ($j = mysqli_fetch_assoc($q_jam)) {
        $jam_ops[$j['hari']] = $j;
    }
}

$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Toko - NuswaRasa</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="../layout/toko.css" rel='stylesheet'>
    <style>
        .jam-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .jam-table th {
            text-align: left;
            padding: 10px;
            background: #fdf2f2;
            color: #e53935;
            font-size: 13px;
        }

        .jam-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .jam-table input[type="time"] {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
        }

        .jam-table input[type="time"]:disabled {
            background: #f5f5f5;
            color: #aaa;
        }

        .tutup-label {
            color: #e53935;
            font-size: 13px;
            font-weight: 600;
        }

        .jam-table input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="logo"><i class='bx bxs-store-alt'></i> NUSWARASA</div>
        <div class="menu">
            <a href="dashAdmin.php"><i class='bx bxs-dashboard'></i> Dashboard</a>
            <a href="kelola_produk.php"><i class='bx bxs-food-menu'></i> Kelola Produk</a>
            <a href="kelola_pesanan.php"><i class='bx bxs-shopping-bag'></i> Kelola Pesanan</a>
            <a href="toko.php" class="active"><i class='bx bxs-user-detail'></i> Profil Toko</a>
            <div class="logout">
                <a href="../proses/logout.php"><i class='bx bx-log-out'></i> Logout</a>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h2>Profil Toko</h2>
        </div>

        <?php if ($pesan): ?>
            <div class="notif success"><?= $pesan ?></div>
        <?php endif; ?>

        <?php if (!$toko): ?>
            <div class="info-belum">
                <i class='bx bx-info-circle'></i> Toko kamu belum diisi. Lengkapi profil toko agar produkmu bisa tampil!
            </div>
        <?php endif; ?>

        <!-- Form Profil Toko -->
        <div class="card">
            <h3><i class='bx bxs-store'></i> Informasi Toko</h3>
            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Foto Toko</label>
                    <?php if (!empty($toko['foto'])): ?>
                        <img src="../img/<?= htmlspecialchars($toko['foto']) ?>" class="foto-preview" id="fotoPreview">
                    <?php else: ?>
                        <div class="foto-placeholder" id="fotoPlaceholder"><i class='bx bxs-store'></i></div>
                        <img src="" class="foto-preview" id="fotoPreview" style="display:none;">
                    <?php endif; ?>
                    <input type="file" name="foto" accept="image/*" onchange="previewFoto(this)">
                </div>

                <div class="form-group">
                    <label>Nama Toko <span style="color:#c0392b">*</span></label>
                    <input type="text" name="nama_toko" required
                        value="<?= htmlspecialchars($toko['nama_toko'] ?? '') ?>"
                        placeholder="Contoh: Warung Sate Ambal Bu Sri">
                </div>

                <div class="form-group">
                    <label>Deskripsi Toko</label>
                    <textarea name="deskripsi"><?= htmlspecialchars($toko['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="row-2">
                    <div class="form-group">
                        <label>No. HP / WhatsApp <span style="color:#c0392b">*</span></label>
                        <input type="text" name="no_hp" required
                            value="<?= htmlspecialchars($toko['no_hp'] ?? '') ?>"
                            placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="form-group">
                        <label>Status Toko</label>
                        <select name="status">
                            <option value="aktif" <?= ($toko['status'] ?? '') == 'aktif'    ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= ($toko['status'] ?? '') == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat Toko <span style="color:#c0392b">*</span></label>
                    <textarea name="alamat" required><?= htmlspecialchars($toko['alamat'] ?? '') ?></textarea>
                </div>

                <button type="submit" name="simpan" class="btn-simpan">
                    <i class='bx bx-save'></i> Simpan Profil Toko
                </button>
            </form>
        </div>

        <!-- Form Jam Operasional -->
        <?php if ($id_toko): ?>
            <div class="card" style="margin-top:20px;">
                <h3><i class='bx bx-time'></i> Jam Operasional</h3>
                <form method="POST">
                    <table class="jam-table">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Jam Buka</th>
                                <th>Jam Tutup</th>
                                <th>Tutup / Libur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hari_list as $hari):
                                $key     = strtolower($hari);
                                $j       = $jam_ops[$hari] ?? null;
                                $buka    = $j['jam_buka']  ?? '08:00';
                                $tutup_j = $j['jam_tutup'] ?? '17:00';
                                $libur   = ($j['tutup'] ?? 'tidak') == 'ya';
                            ?>
                                <tr>
                                    <td><b><?= $hari ?></b></td>
                                    <td>
                                        <input type="time" name="buka_<?= $key ?>"
                                            value="<?= $buka ?>"
                                            <?= $libur ? 'disabled' : '' ?>
                                            id="buka_<?= $key ?>">
                                    </td>
                                    <td>
                                        <input type="time" name="tutup_jam_<?= $key ?>"
                                            value="<?= $tutup_j ?>"
                                            <?= $libur ? 'disabled' : '' ?>
                                            id="tutupjam_<?= $key ?>">
                                    </td>
                                    <td>
                                        <input type="checkbox" name="tutup_<?= $key ?>"
                                            id="tutup_<?= $key ?>"
                                            <?= $libur ? 'checked' : '' ?>
                                            onchange="toggleHari('<?= $key ?>', this.checked)">
                                        <label for="tutup_<?= $key ?>" class="tutup-label">Tutup</label>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <button type="submit" name="simpan_jam" class="btn-simpan" style="margin-top:15px;">
                        <i class='bx bx-save'></i> Simpan Jam Operasional
                    </button>
                </form>
            </div>
        <?php endif; ?>

    </div>

    <script>
        function previewFoto(input) {
            const preview = document.getElementById('fotoPreview');
            const placeholder = document.getElementById('fotoPlaceholder');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function toggleHari(key, tutup) {
            document.getElementById('buka_' + key).disabled = tutup;
            document.getElementById('tutupjam_' + key).disabled = tutup;
        }
    </script>

</body>

</html>