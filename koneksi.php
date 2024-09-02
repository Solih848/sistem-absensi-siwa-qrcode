<?php
$servername = "localhost";  // Nama host database
$username = "root";         // Username database
$password = "";             // Password database (kosong jika tidak ada)
$database = "absensi";   // Nama database

// Membuat koneksi
$conn = mysqli_connect($servername, $username, $password, $database);

// Mengecek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
