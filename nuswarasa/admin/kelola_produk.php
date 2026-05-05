<?php
session_start();
include "../proses/koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../proses/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

$cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_toko FROM toko WHERE id_user='$id_user'"));
$id_toko = $cek['id_toko'] ?? 0;

if (empty($id_toko)) {
    die("Akun ini belum memiliki toko. Silakan isi profil toko terlebih dahulu.");
}

// Hapus produk
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM produk WHERE id_produk='$id' AND id_toko='$id_toko'");
    header("Location: kelola_produk.php");
    exit;
}

// Tambah produk
if (isset($_POST['tambah'])) {
    $nama      = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $harga     = $_POST['harga'];
    $stok      = $_POST['stok'];
    $kategori  = mysqli_real_escape_string($conn, $_POST['kategori']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $populer   = isset($_POST['populer']) ? 'ya' : 'tidak';

    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];

    if (!empty($foto)) {
        move_uploaded_file($tmp, "../img/" . $foto);
    }

    mysqli_query($conn, "INSERT INTO produk 
        (nama_produk, harga, stok, kategori, deskripsi, foto, populer, id_toko)
        VALUES 
        ('$nama', '$harga', '$stok', '$kategori', '$deskripsi', '$foto', '$populer', '$id_toko')");

    header("Location: kelola_produk.php");
    exit;
}

// Edit produk
if (isset($_POST['edit'])) {
    $id        = $_POST['id_produk'];
    $nama      = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $harga     = $_POST['harga'];
    $stok      = $_POST['stok'];
    $kategori  = mysqli_real_escape_string($conn, $_POST['kategori']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $populer   = isset($_POST['populer']) ? 'ya' : 'tidak';

    $foto_lama = $_POST['foto_lama'];
    $foto      = $_FILES['foto']['name'];
    $tmp       = $_FILES['foto']['tmp_name'];

    if (!empty($foto)) {
        move_uploaded_file($tmp, "../img/" . $foto);
    } else {
        $foto = $foto_lama;
    }

    mysqli_query($conn, "UPDATE produk SET
        nama_produk='$nama', harga='$harga', stok='$stok',
        kategori='$kategori', deskripsi='$deskripsi',
        foto='$foto', populer='$populer'
        WHERE id_produk='$id' AND id_toko='$id_toko'");

    header("Location: kelola_produk.php");
    exit;
}

// Search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

if (!empty($search)) {
    $where = "WHERE id_toko='$id_toko' AND nama_produk LIKE '%$search%'";
} else {
    $where = "WHERE id_toko='$id_toko'";
}

$query = mysqli_query($conn, "SELECT * FROM produk $where ORDER BY id_produk DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin NuswaRasa</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="../layout/produk.css" rel="stylesheet">
</head>

<body>
    <div class="sidebar">
        <div class="logo"><i class='bx bxs-store-alt'></i> NUSWARASA</div>
        <div class="menu">
            <a href="dashAdmin.php"><i class='bx bxs-dashboard'></i> Dashboard</a>
            <a href="kelola_produk.php" class="active"><i class='bx bxs-food-menu'></i> Kelola Produk</a>
            <a href="kelola_pesanan.php"><i class='bx bxs-shopping-bag'></i> Kelola Pesanan</a>
            <a href="toko.php"><i class='bx bxs-user-detail'></i> Profil Toko</a>
            <div class="logout">
                <a href="../proses/logout.php"><i class='bx bx-log-out'></i> Logout</a>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h2>Kelola Produk</h2>
            <div>
                <form method="GET" style="display:inline">
                    <input type="text" name="search" placeholder="Cari produk..." class="search"
                        value="<?php echo htmlspecialchars($search); ?>">
                </form>
                <button class="btn" onclick="bukaModal('modalTambah')">+ Tambah Produk</button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Kategori</th>
                    <th>Populer</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($query) == 0): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">Belum ada produk</td>
                    </tr>
                <?php endif; ?>

                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><img src="../img/<?php echo $row['foto']; ?>" width="60" style="border-radius:8px;object-fit:cover;height:60px;"></td>
                        <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                        <td>Rp <?php echo number_format($row['harga']); ?></td>
                        <td><?php echo $row['stok']; ?></td>
                        <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                        <td><?php echo $row['populer'] == 'ya' ? '⭐ Ya' : 'Tidak'; ?></td>
                        <td class="action">
                            <button class="edit" onclick="bukaEdit(
                            '<?php echo $row['id_produk']; ?>',
                            '<?php echo addslashes($row['nama_produk']); ?>',
                            '<?php echo $row['harga']; ?>',
                            '<?php echo $row['stok']; ?>',
                            '<?php echo addslashes($row['kategori']); ?>',
                            '<?php echo addslashes($row['deskripsi']); ?>',
                            '<?php echo $row['foto']; ?>',
                            '<?php echo $row['populer']; ?>'
                        )">Edit</button>
                            <button class="hapus" onclick="return konfirmHapus(<?php echo $row['id_produk']; ?>)">Hapus</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Tambah -->
    <div class="modal-overlay" id="modalTambah">
        <div class="modal">
            <h3>Tambah Produk</h3>
            <form method="POST" enctype="multipart/form-data">
                <label>Nama Produk</label>
                <input type="text" name="nama_produk" required>

                <label>Harga</label>
                <input type="number" name="harga" required>

                <label>Stok</label>
                <input type="number" name="stok" required>

                <label>Kategori</label>
                <input type="text" name="kategori">

                <label>Deskripsi</label>
                <textarea name="deskripsi"></textarea>

                <label>Foto</label>
                <input type="file" name="foto" accept="image/*">

                <div class="populer-check">
                    <input type="checkbox" name="populer" id="populer_tambah">
                    <label for="populer_tambah" style="margin:0;">Tampilkan di Beranda (Populer)</label>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn-batal" onclick="tutupModal('modalTambah')">Batal</button>
                    <button type="submit" name="tambah" class="btn-simpan">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal-overlay" id="modalEdit">
        <div class="modal">
            <h3>Edit Produk</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_produk" id="edit_id">
                <input type="hidden" name="foto_lama" id="edit_foto_lama">

                <label>Nama Produk</label>
                <input type="text" name="nama_produk" id="edit_nama" required>

                <label>Harga</label>
                <input type="number" name="harga" id="edit_harga" required>

                <label>Stok</label>
                <input type="number" name="stok" id="edit_stok" required>

                <label>Kategori</label>
                <input type="text" name="kategori" id="edit_kategori">

                <label>Deskripsi</label>
                <textarea name="deskripsi" id="edit_deskripsi"></textarea>

                <label>Foto Baru (kosongkan jika tidak diganti)</label>
                <input type="file" name="foto" accept="image/*">

                <div class="populer-check">
                    <input type="checkbox" name="populer" id="edit_populer">
                    <label for="edit_populer" style="margin:0;">Tampilkan di Beranda (Populer)</label>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn-batal" onclick="tutupModal('modalEdit')">Batal</button>
                    <button type="submit" name="edit" class="btn-simpan">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaModal(id) {
            document.getElementById(id).classList.add('active')
        }

        function tutupModal(id) {
            document.getElementById(id).classList.remove('active')
        }

        function bukaEdit(id, nama, harga, stok, kategori, deskripsi, foto, populer) {
            document.getElementById('edit_id').value = id
            document.getElementById('edit_nama').value = nama
            document.getElementById('edit_harga').value = harga
            document.getElementById('edit_stok').value = stok
            document.getElementById('edit_kategori').value = kategori
            document.getElementById('edit_deskripsi').value = deskripsi
            document.getElementById('edit_foto_lama').value = foto
            document.getElementById('edit_populer').checked = (populer === 'ya')
            bukaModal('modalEdit')
        }

        function konfirmHapus(id) {
            if (confirm("Yakin ingin menghapus produk ini?")) {
                window.location.href = "kelola_produk.php?hapus=" + id
            }
        }

        // Tutup modal kalau klik di luar
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) tutupModal(this.id)
            })
        })
    </script>
</body>

</html>