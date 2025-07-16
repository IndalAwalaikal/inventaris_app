<?php
// Memulai sesi hanya jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: /inventaris_app/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris App</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.6.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/inventaris_app/assets/css/style.css" rel="stylesheet">
    <link href="/inventaris_app/assets/css/header.css" rel="stylesheet">
    <!-- Tambahkan di includes/header.php -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
</head>
<body>
    <!-- Header Section -->
    <header class="header-section">
        <div class="container-fluid">
            <div class="header-content">
                <!-- Brand -->
                <div class="brand-section">
                    <div class="brand-icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="brand-text">
                        <h1>Inventaris App</h1>
                    </div>
                </div>
                
                <!-- Search -->
                <div class="search-section">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="form-control search-input" placeholder="Cari produk, kategori..." id="global-search" autocomplete="off">
                        <div class="search-results" id="search-results"></div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="header-actions">
                    <!-- Waktu -->
                    <div class="time-display">
                        <i class="fas fa-clock me-2"></i>
                        <span id="current-time"><?php echo date('H:i:s'); ?></span>
                    </div>
                    
                    <!-- User Info -->
                    <div class="user-info" onclick="window.location.href='/inventaris_app/profile.php'">
                        <div class="user-avatar">
                            <?php 
                            $admin_name = $_SESSION['admin_name'] ?? 'Admin';
                            echo substr($admin_name, 0, 1); 
                            ?>
                        </div>
                        <div class="user-details d-none d-md-block">
                            <p class="user-name"><?php echo $admin_name; ?></p>
                            <p class="user-role">Administrator</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Konten utama -->
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-4">
            
<script src="/inventaris_app/assets/js/header.js"></script>