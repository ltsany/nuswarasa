<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['kirim_ulasan'])) {
    $id_user    = $_SESSION['id_user'];
    $id_pesanan = (int) $_POST['id_pesanan'];
    $id_produk  = (int) $_POST['id_produk'];
    $rating     = (int) $_POST['rating'];
    $komentar   = mysqli_real_escape_string($conn, $_POST['komentar']);

    // Simpan ulasan
    mysqli_query($conn, "INSERT INTO ulasan (id_user, id_produk, id_pesanan, rating, komentar)
    VALUES ('$id_user', '$id_produk', '$id_pesanan', '$rating', '$komentar')");

    // Update pesanan jadi sudah diulas
    mysqli_query($conn, "UPDATE pesanan SET sudah_diulas='ya' WHERE id_pesanan='$id_pesanan'");

    header("Location: ../profil.php?pesan=ulasan_berhasil");
    exit;
}
