<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login");
    exit();
}

$email = $_SESSION['email'];

include('koneksi.php');

// Menghapus data absensi jika tombol hapus ditekan
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $query = "DELETE FROM absensi WHERE id = $delete_id";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data absen berhasil dihapus.');</script>";
    } else {
        echo "<script>alert('Gagal menghapus data absen: " . mysqli_error($conn) . "');</script>";
    }
}

// Mengambil tanggal dari form
$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');

// Base query
$query = "SELECT absensi.id, siswa.nama, siswa.kelas, absensi.waktu_scan 
          FROM absensi 
          JOIN siswa ON absensi.siswa_id = siswa.id
          WHERE DATE(absensi.waktu_scan) = '$selected_date'
          ORDER BY absensi.waktu_scan DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>
</head>

<body>
    <?php include 'layout/header.php'; ?>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1>Data Absensi Siswa</h1>
            </div>
            <div class="card-body">
                <div class="mb-3">
                </div>

                <form action="" method="get" class="mb-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label for="selected_date" class="col-form-label">Pilih Tanggal:</label>
                        </div>
                        <div class="col-auto">
                            <input type="date" id="selected_date" name="selected_date" class="form-control" value="<?= $selected_date ?>" onchange="this.form.submit()">
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table id="absensiTable" class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr class="text-center align-middle">
                                <th>No.</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Waktu Scan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0):
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)) :
                            ?>
                                    <tr class="text-center align-middle">
                                        <td><?= $no++; ?></td>
                                        <td><?= $row['nama']; ?></td>
                                        <td><?= $row['kelas']; ?></td>
                                        <td><?= $row['waktu_scan']; ?></td>
                                        <td>
                                            <a href="tabel_absensi.php?delete_id=<?= $row['id']; ?>&selected_date=<?= $selected_date ?>"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');"
                                                class="btn btn-sm btn-danger">
                                                <i class="material-icons align-middle">delete</i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data absensi untuk tanggal ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#absensiTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                }
            });
        });
    </script>
    <?php include 'layout/footer.php'; ?>

</body>

</html>