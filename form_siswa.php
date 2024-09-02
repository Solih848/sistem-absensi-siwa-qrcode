<?php
include('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

    include('phpqrcode/qrlib.php');

    // Set ukuran QR code
    $size = 10; // Semakin besar angkanya, semakin besar QR code yang dihasilkan

    $qrContent = $nama . '|' . $nim . '|' . $kelas;
    $qrFileName = $tempDir . preg_replace('/[^a-zA-Z0-9_-]/', '', $nama) . '.png';
    QRcode::png($qrContent, $qrFileName, QR_ECLEVEL_L, $size);

    // Menambahkan nama siswa ke bawah QR code menggunakan font bawaan
    $image = imagecreatefrompng($qrFileName);
    $width = imagesx($image);
    $height = imagesy($image);
    $newHeight = $height + 20; // Tambahkan tinggi untuk teks
    $newImage = imagecreatetruecolor($width, $newHeight);

    // Set warna latar belakang dan teks
    $white = imagecolorallocate($newImage, 255, 255, 255);
    $black = imagecolorallocate($newImage, 0, 0, 0);

    // Isi background gambar baru dengan warna putih
    imagefilledrectangle($newImage, 0, 0, $width, $newHeight, $white);

    // Salin QR code ke gambar baru
    imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);

    // Menambahkan teks di bawah gambar menggunakan font bawaan GD
    $font = 5; // Ukuran font bawaan GD (dari 1 sampai 5)
    $textX = ($width - (imagefontwidth($font) * strlen($nama))) / 2; // Posisikan teks di tengah
    $textY = $height + 5; // Posisikan teks sedikit di bawah gambar QR code
    imagestring($newImage, $font, $textX, $textY, $nama, $black);

    // Simpan gambar baru
    imagepng($newImage, $qrFileName);

    // Hapus gambar sementara dari memori
    imagedestroy($image);
    imagedestroy($newImage);

    // Simpan ke database
    $query = "INSERT INTO siswa (nama, nim, alamat, kelas, qr_code_path) VALUES ('$nama', '$nim', '$alamat', '$kelas', '$qrFileName')";
    if (mysqli_query($conn, $query)) {
        header("Location: tabel_siswa.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Siswa</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include "layout/header.php" ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Form Tambah Siswa</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)) { ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php } ?>
                        <form method="POST">
                            <div class="mb-3">
                                <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama Siswa" required>
                            </div>

                            <div class="mb-3">
                                <input type="text" class="form-control" id="nim" name="nim" placeholder="NIM" required>
                            </div>

                            <div class="mb-3">
                                <textarea class="form-control" id="alamat" name="alamat" placeholder="Alamat" required></textarea>
                            </div>

                            <div class="mb-3">
                                <input type="text" class="form-control" id="kelas" name="kelas" placeholder="Kelas" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Tambah Siswa</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS CDN (optional, only if you need Bootstrap's JavaScript features) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include "layout/footer.php" ?>
</body>

</html>
