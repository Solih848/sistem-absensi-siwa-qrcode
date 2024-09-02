<?php
include('koneksi.php');
include('phpqrcode/qrlib.php'); // Pastikan Anda menyertakan library QR code

// Cek apakah parameter id tersedia di URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data siswa berdasarkan ID
    $query = "SELECT * FROM siswa WHERE id = $id";
    $result = mysqli_query($conn, $query);

    // Tambahkan pengecekan apakah query berhasil dieksekusi
    if ($result && mysqli_num_rows($result) > 0) {
        $siswa = mysqli_fetch_assoc($result);

        // Jika form disubmit
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

            // Set ukuran QR code
            $size = 10; // Semakin besar angkanya, semakin besar QR code yang dihasilkan

            // Generate QR code baru
            $qrContent = $nama . '|' . $nim . '|' . $kelas;
            $qrFileName = $tempDir . $nama . '.png'; // Nama file QR code berdasarkan nama siswa

            // Hapus QR code lama jika ada
            if (file_exists($siswa['qr_code_path'])) {
                unlink($siswa['qr_code_path']);
            }

            // Buat QR code baru
            QRcode::png($qrContent, $qrFileName, QR_ECLEVEL_L, $size);

            // Tambahkan teks nama siswa di bawah QR code
            addTextBelowQRCode($qrFileName, $nama);

            // Update data siswa dan path QR code baru di database
            $query = "UPDATE siswa SET nama = '$nama', nim = '$nim', alamat = '$alamat', kelas = '$kelas', qr_code_path = '$qrFileName' WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                // Redirect ke tabel_siswa.php setelah berhasil diupdate
                header("Location: tabel_siswa.php");
                exit();
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    } else {
        // Redirect jika data tidak ditemukan atau query gagal
        $error = "Data siswa tidak ditemukan.";
    }
} else {
    // Redirect jika id tidak ada di URL
    header("Location: tabel_siswa.php");
    exit();
}

// Fungsi untuk menambahkan teks di bawah QR code
function addTextBelowQRCode($qrFileName, $text)
{
    $image = imagecreatefrompng($qrFileName);
    $width = imagesx($image);
    $height = imagesy($image);

    // Tambahkan area untuk teks di bawah gambar QR code
    $newHeight = $height + 20; // Tambahkan tinggi area teks
    $newImage = imagecreatetruecolor($width, $newHeight);

    // Set background warna putih
    $white = imagecolorallocate($newImage, 255, 255, 255);
    imagefilledrectangle($newImage, 0, 0, $width, $newHeight, $white);

    // Salin QR code ke gambar baru
    imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);

    // Set warna teks hitam
    $black = imagecolorallocate($newImage, 0, 0, 0);

    // Tentukan font dan ukuran font
    $font = 5; // Ukuran font GD bawaan

    // Tentukan posisi teks
    $textWidth = imagefontwidth($font) * strlen($text);
    $textX = ($width - $textWidth) / 2; // Posisi X agar teks berada di tengah
    $textY = $height + 5; // Posisi Y agar teks berada di bawah QR code

    // Tambahkan teks ke gambar baru
    imagestring($newImage, $font, $textX, $textY, $text, $black);

    // Simpan gambar baru
    imagepng($newImage, $qrFileName);

    // Bersihkan memori
    imagedestroy($image);
    imagedestroy($newImage);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include "layout/header.php" ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3>Edit Data Siswa</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)) { ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php } ?>
                        <?php if (isset($siswa)) { ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="nama" name="nama" value="<?php echo $siswa['nama']; ?>" placeholder="Nama Siswa" required>
                                </div>
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="nim" name="nim" value="<?php echo $siswa['nim']; ?>" placeholder="NIM" required>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" id="alamat" name="alamat" placeholder="Alamat" required><?php echo $siswa['alamat']; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="kelas" name="kelas" value="<?php echo $siswa['kelas']; ?>" placeholder="Kelas" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Siswa</button>
                                <a href="tabel_siswa.php" class="btn btn-danger">Batal</a>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include "layout/footer.php" ?>
</body>

</html>