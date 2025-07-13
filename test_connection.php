<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Ganti parameter berikut sesuai kebutuhan Anda
$host = '36.50.77.120';
$user = 'wiracent_admin'; // ganti dengan password user yang benar
$pass = 'Wiracenter!'; // ganti dengan password database Anda
$db   = 'wiracent_db2';
$port = 3306;

// Membuat koneksi
$conn = new mysqli($host, $user, $pass, $db, $port);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
echo "Koneksi berhasil ke database!";
$conn->close();
?> 