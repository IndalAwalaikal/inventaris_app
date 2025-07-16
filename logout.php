<?php
session_start();

// Cek jika ada parameter confirm
if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
    // Hapus semua sesi dan redirect ke login
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Jika tidak ada konfirmasi, tampilkan JavaScript confirmation
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            title: 'Konfirmasi Logout',
            text: 'Apakah Anda yakin ingin keluar dari sistem?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Keluar',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect ke logout dengan konfirmasi
                window.location.href = 'logout.php?confirm=true';
            } else {
                // Kembali ke halaman sebelumnya
                window.history.back();
            }
        });
    </script>
</body>
</html>