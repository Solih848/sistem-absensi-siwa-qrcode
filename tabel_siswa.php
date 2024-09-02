<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login");
    exit();
}

$email = $_SESSION['email'];

include('koneksi.php');

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Start a transaction
    mysqli_begin_transaction($conn);

    try {
        // First, delete related records from the absensi table
        $delete_absensi_query = "DELETE FROM absensi WHERE siswa_id = ?";
        $delete_absensi_stmt = mysqli_prepare($conn, $delete_absensi_query);
        mysqli_stmt_bind_param($delete_absensi_stmt, "i", $id);
        mysqli_stmt_execute($delete_absensi_stmt);
        mysqli_stmt_close($delete_absensi_stmt);

        // Now, get the QR code path and delete the student record
        $query = "SELECT qr_code_path FROM siswa WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            $qr_code_path = $row['qr_code_path'];

            // Delete the record from the siswa table
            $delete_query = "DELETE FROM siswa WHERE id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($delete_stmt, "i", $id);
            mysqli_stmt_execute($delete_stmt);
            mysqli_stmt_close($delete_stmt);

            // If deletion from database is successful, delete the QR code file
            if (file_exists($qr_code_path)) {
                unlink($qr_code_path);
            }

            // Commit the transaction
            mysqli_commit($conn);
            $message = "Data siswa dan semua data absensi terkait berhasil dihapus.";
        } else {
            throw new Exception("Data siswa tidak ditemukan.");
        }

        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        mysqli_rollback($conn);
        $message = "Gagal menghapus data: " . $e->getMessage();
    }
}


// Fetch all students
$query = "SELECT * FROM siswa";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body>
    <?php include 'layout/header.php'; ?>

    <div class="container mt-3 mb-4">
        <?php if (isset($message)): ?>
            <div class="alert alert-info" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1 class="mb-0"><i class="material-icons align-middle me-2">school</i>Data Siswa</h1>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <a href="form_siswa.php" class="btn btn-success me-2">
                        <i class="material-icons align-middle">add</i> Form
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr class="text-center align-middle">
                                <th>No</th> <!-- Kolom nomor -->
                                <th>Nama</th>
                                <th>NIM</th>
                                <th>Alamat</th>
                                <th>Kelas</th>
                                <th>QR Code</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1; // Inisialisasi nomor urut
                            while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr class="text-center align-middle">
                                    <td><?php echo $no++; ?></td> <!-- Menampilkan nomor urut -->
                                    <td><?php echo $row['nama']; ?></td>
                                    <td><?php echo $row['nim']; ?></td>
                                    <td><?php echo $row['alamat']; ?></td>
                                    <td><?php echo $row['kelas']; ?></td>
                                    <td><img src="<?php echo $row['qr_code_path']; ?>" alt="QR Code" width="100" class="img-thumbnail"></td>
                                    <td class="text-center align-middle">
                                        <a href="download.php?file=<?php echo $row['qr_code_path']; ?>" class="btn btn-sm btn-primary">
                                            <i class="material-icons align-middle">download</i> Download QR Code
                                        </a>
                                        <a href="edit_siswa.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="material-icons align-middle">edit</i> Edit
                                        </a>
                                        <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $row['id']; ?>">
                                            <i class="material-icons align-middle">delete</i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS CDN (optional, for certain Bootstrap features) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Delete confirmation script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                        window.location.href = `tabel_siswa.php?action=delete&id=${id}`;
                    }
                });
            });
        });
    </script>

    <?php include 'layout/footer.php'; ?>
</body>

</html>