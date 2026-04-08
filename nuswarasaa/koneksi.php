<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "nuswarasa";

$conn = mysqli_connect("127.0.0.1","root","","nuswarasa");

if(!$conn){
    die("Koneksi gagal");
}

?>