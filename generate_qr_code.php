<?php
include('koneksi.php');
include('phpqrcode/qrlib.php');

// Ambil data dari form
$nama = $_POST['nama'];
$nim = $_POST['nim'];
$alamat = $_POST['alamat'];
$kelas = $_POST['kelas'];

// Set direktori penyimpanan QR code
$tempDir = "qrcodes/";

// Cek dan buat direktori jika belum ada
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}

// Set ukuran QR code
$size = 10;
$qrContent = $nama . '|' . $nim . '|' . $kelas;
$nama_siswa = preg_replace('/[^A-Za-z0-9]/', '_', $nama); 
$qrFileName = $tempDir . $nama_siswa . '.png';

QRcode::png($qrContent, $qrFileName, QR_ECLEVEL_L, $size);

// Buat gambar baru dengan ukuran tertentu untuk menampung QR code dan teks
$image = imagecreatefrompng($qrFileName);
$width = imagesx($image);
$height = imagesy($image);

// Buat gambar baru dengan tinggi tambahan untuk teks
$newHeight = $height + 50; // 50px untuk teks di bawah QR code
$newImage = imagecreatetruecolor($width, $newHeight);

// Salin QR code ke gambar baru
imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);

// Tentukan warna teks (hitam)
$black = imagecolorallocate($newImage, 0, 0, 0);

// Tambahkan teks di bawah QR code
$font = 5; // Ukuran font (gunakan ukuran 1-5)
$padding = 5; // Padding antara QR code dan teks
imagestring($newImage, $font, $padding, $height + $padding, $nama, $black);
imagestring($newImage, $font, $padding, $height + $padding + 20, $kelas, $black);

// Simpan gambar baru yang sudah ada teksnya
$newFileName = $tempDir . uniqid() . '_with_text.png';
imagepng($newImage, $newFileName);

// Hapus gambar sementara
imagedestroy($image);
imagedestroy($newImage);

// Redirect atau berikan feedback
echo "QR Code berhasil dibuat dan disimpan sebagai: " . $newFileName;
