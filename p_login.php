<?php
include 'koneksi.php';
// Ambil nilai dari form login
$email = $_POST['email'];
$password = $_POST['password'];

// Lindungi dari SQL injection
$email = mysqli_real_escape_string($conn, $email);
$password = mysqli_real_escape_string($conn, $password);

// Query untuk memeriksa apakah email dan password cocok
$sql = "SELECT id FROM admin WHERE email = '$email' AND password = '$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Login berhasil
    session_start();
    $_SESSION['email'] = $email;
    header("Location: tabel_siswa.php"); // Redirect ke halaman users
} else {
    // Login gagal
    echo "Email atau password salah.";
}

$conn->close();
