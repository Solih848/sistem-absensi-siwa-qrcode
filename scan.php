<?php
include('koneksi.php');
date_default_timezone_set('Asia/Jakarta');

$notification = ''; // Variabel untuk menyimpan pesan notifikasi

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $qr_content = $_POST['qr_content']; // QR content dari hasil scan
    list($nama, $nim, $kelas) = explode('|', $qr_content);

    // Ambil siswa_id berdasarkan NIM atau Nama
    $query = "SELECT id FROM siswa WHERE nama='$nama' AND nim='$nim' AND kelas='$kelas'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $siswa_id = $row['id'];

    // Cek apakah siswa sudah absen pada hari yang sama
    $tanggal_hari_ini = date('Y-m-d');
    $query = "SELECT * FROM absensi WHERE siswa_id = '$siswa_id' AND DATE(waktu_scan) = '$tanggal_hari_ini'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Jika sudah absen pada hari yang sama, berikan notifikasi
        $notification = "Anda sudah melakukan scan/absen hari ini. Silakan coba lagi besok.";
    } else {
        // Simpan absensi
        $waktu_scan = date('Y-m-d H:i:s');
        $query = "INSERT INTO absensi (siswa_id, nama, kelas, waktu_scan) VALUES ('$siswa_id', '$nama', '$kelas', '$waktu_scan')";
        if (mysqli_query($conn, $query)) {
            $notification = "Absensi berhasil! Terima kasih sudah melakukan scan.";
        } else {
            $notification = "Gagal menyimpan absensi. Silakan coba lagi.";
        }

        // Redirect setelah POST untuk mencegah duplikasi data saat refresh
        header("Location: scan.php?notification=" . urlencode($notification));
        exit();
    }
}

// Tampilkan notifikasi jika ada
if (isset($_GET['notification'])) {
    $notification = urldecode($_GET['notification']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/minified/html5-qrcode.min.js"></script>
    <link href="css/scan.css" rel="stylesheet" />
</head>

<body>
    <?php include 'layout/navbar.html' ?>

    <div class="container mt-5" id="container">

        <div class="card col-md-7 mx-auto ">
            <div class="card-header">
                <h1 class="text-center">Scan QR Code untuk Absensi disini</h1>
            </div>
            <div class="card-body">

                <?php if ($notification): ?>
                    <div class="alert alert-info" role="alert">
                        <strong><?php echo $notification; ?></strong>
                    </div>
                <?php endif; ?>

                <!-- Tempat video stream untuk scan QR -->
                <div id="reader" style="width: 500px; height: 500px;" class="mx-auto"></div>

                <!-- Form untuk mengirim hasil scan ke backend -->
                <form id="scan-form" method="POST" action="">
                    <input type="hidden" name="qr_content" id="qr_content">
                </form>

            </div>
        </div>
    </div>

    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Mengisi input form dengan hasil scan
            document.getElementById('qr_content').value = decodedText;

            // Submit form secara otomatis
            document.getElementById('scan-form').submit();
        }

        function onScanFailure(error) {
            console.warn(`Scan gagal: ${error}`);
        }

        // Inisialisasi dan mulai scanner
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", {
                fps: 10,
                qrbox: 350
            });

        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    </script>

</body>

</html>