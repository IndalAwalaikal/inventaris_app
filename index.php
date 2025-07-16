<?php
ob_start();

// Menyertakan header dan konfigurasi database
require_once 'includes/header.php';
require_once 'config/db.php';

// Logika untuk generate PDF
if (isset($_GET['generate_pdf']) && $_GET['generate_pdf'] === 'true') {
    
    try {
        // Ambil data untuk laporan
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
        $total_products = $stmt->fetchColumn();

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories");
        $total_categories = $stmt->fetchColumn();

        $stmt = $pdo->query("SELECT c.name, COUNT(p.id) as total FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY total DESC");
        $category_stats = $stmt->fetchAll();

        $stmt = $pdo->query("SELECT p.name, c.name as category_name, p.price, p.stock, p.created_at, p.status_barang FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 10");
        $recent_products = $stmt->fetchAll();

        $stmt = $pdo->query("SELECT p.name, c.name as category_name, p.stock, p.status_barang FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock < 10 ORDER BY p.stock ASC");
        $low_stock_products = $stmt->fetchAll();

        // Set headers untuk PDF
        header('Content-Type: text/html; charset=utf-8');
        
        // HTML untuk PDF
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Laporan Dashboard</title>
            <style>
                body {font-family: Arial, sans-serif;margin: 0;padding: 0;}
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { color: #333; margin: 0; }
                .header p { color: #666; margin: 5px 0; }
                .section { margin-bottom: 30px; }
                .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
                .stat-box { background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff; }
                .stat-box h3 { margin: 0 0 10px 0; color: #333; }
                .stat-number { font-size: 2em; font-weight: bold; color: #007bff; }
                .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .table th, .table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                .table th { background-color: #007bff; color: white; }
                .table tr:nth-child(even) { background-color: #f9f9f9; }
                .alert { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
                .alert-success { background-color: #d4edda; border-left-color: #28a745; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .text-danger { color: #dc3545; }
                .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 0.9em; color: #666; }
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>LAPORAN DASHBOARD</h1>
                <h2>Sistem Manajemen Produk</h2>
                <p>Tanggal: ' . date('d F Y, H:i:s') . '</p>
            </div>

            <div class="section">
                <h3>RINGKASAN STATISTIK</h3>
                <div class="stats-grid">
                    <div class="stat-box">
                        <h3>Total Produk</h3>
                        <div class="stat-number">' . $total_products . '</div>
                        <p>produk terdaftar</p>
                    </div>
                    <div class="stat-box">
                        <h3>Total Kategori</h3>
                        <div class="stat-number">' . $total_categories . '</div>
                        <p>kategori tersedia</p>
                    </div>
                    <div class="stat-box">
                        <h3>Stok Rendah</h3>
                        <div class="stat-number text-danger">' . count($low_stock_products) . '</div>
                        <p>produk perlu perhatian</p>
                    </div>
                </div>
            </div>

            <div class="section">
                <h3>STATISTIK PRODUK PER KATEGORI</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Jumlah Produk</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>';

        $no = 1;
        foreach ($category_stats as $stat) {
            $percentage = $total_products > 0 ? round(($stat['total'] / $total_products) * 100, 2) : 0;
            echo '<tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td>' . htmlspecialchars($stat['name']) . '</td>
                    <td class="text-center">' . $stat['total'] . '</td>
                    <td class="text-center">' . $percentage . '%</td>
                  </tr>';
        }

        echo '</tbody>
                </table>
            </div>

            <div class="section">
                <h3>PRODUK TERBARU (10 Terakhir)</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status Barang</th>
                            <th>Tanggal Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>';

        $no = 1;
        foreach ($recent_products as $product) {
            echo '<tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td>' . htmlspecialchars($product['name']) . '</td>
                    <td>' . htmlspecialchars($product['category_name'] ?? 'Tanpa Kategori') . '</td>
                    <td class="text-right">Rp ' . number_format($product['price'], 0, ',', '.') . '</td>
                    <td class="text-center">' . $product['stock'] . '</td>
                    <td class="text-center">' . htmlspecialchars($product['status_barang'] ?? 'baik') . '</td>
                    <td class="text-center">' . date('d/m/Y', strtotime($product['created_at'])) . '</td>
                  </tr>';
        }

        echo '</tbody>
                </table>
            </div>';

        // Section untuk produk stok rendah
        if (count($low_stock_products) > 0) {
            echo '<div class="section">
                    <div class="alert">
                        <strong>⚠️ PERINGATAN STOK RENDAH</strong><br>
                        Berikut adalah produk dengan stok kurang dari 10 unit:
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Stok Tersisa</th>
                                <th>Status Barang</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            $no = 1;
            foreach ($low_stock_products as $product) {
                echo '<tr>
                        <td class="text-center">' . $no++ . '</td>
                        <td>' . htmlspecialchars($product['name']) . '</td>
                        <td>' . htmlspecialchars($product['category_name'] ?? 'Tanpa Kategori') . '</td>
                        <td class="text-center text-danger"><strong>' . $product['stock'] . '</strong></td>
                        <td class="text-center">' . htmlspecialchars($product['status_barang'] ?? 'baik') . '</td>
                      </tr>';
            }
            
            echo '</tbody>
                    </table>
                </div>';
        } else {
            echo '<div class="section">
                    <div class="alert alert-success">
                        <strong>✅ STOK AMAN</strong><br>
                        Semua produk memiliki stok yang mencukupi (≥10 unit)
                    </div>
                </div>';
        }

        echo '<div class="footer">
                <div style="display: flex; justify-content: space-between;">
                    <div>
                        <strong>Catatan:</strong><br>
                        • Laporan dibuat secara otomatis oleh sistem<br>
                        • Data yang ditampilkan adalah data real-time<br>
                        • Stok rendah: kurang dari 10 unit
                    </div>
                    <div style="text-align: right;">
                        <strong>Sistem Manajemen Produk</strong><br>
                        Generated: ' . date('d/m/Y H:i:s') . '
                    </div>
                </div>
            </div>

            <script>
                // Auto print saat halaman dimuat
                window.onload = function() {
                    window.print();
                }
            </script>
        </body>
        </html>';
        
        ob_end_flush();
        exit();

    } catch (Exception $e) {
        $error = "Error generating PDF: " . $e->getMessage();
    }
}

// Ambil data untuk dashboard
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $total_products = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories");
    $total_categories = $stmt->fetchColumn();

    // Query yang diperbaiki untuk mengatasi masalah chart
    $stmt = $pdo->query("SELECT c.name, COUNT(p.id) as total FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id, c.name ORDER BY total DESC");
    $category_stats = $stmt->fetchAll();

    // Ambil data stok rendah untuk notifikasi
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE stock < 10");
    $low_stock_count = $stmt->fetchColumn();

} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!-- Pastikan Chart.js dimuat -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-home me-2"></i>Dashboard</h1>
        <div>
            <a href="?generate_pdf=true" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf me-2"></i>Download Laporan PDF
            </a>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Alert jika tidak ada data kategori -->
    <?php if (empty($category_stats)): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Peringatan:</strong> Tidak ada data kategori ditemukan. Silakan tambahkan kategori terlebih dahulu.
            <a href="modules/categories/add.php" class="alert-link">Tambah Kategori</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($low_stock_count > 0): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Peringatan!</strong> Ada <?php echo $low_stock_count; ?> produk dengan stok rendah (kurang dari 10 unit).
            <a href="modules/products/index.php" class="alert-link">Lihat produk</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-primary">
                                <i class="fas fa-box me-2"></i>Total Produk
                            </h5>
                            <h2 class="text-primary"><?php echo $total_products; ?></h2>
                            <p class="text-muted mb-0">produk terdaftar</p>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-box fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="modules/products/index.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i>Lihat Semua
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-success">
                                <i class="fas fa-tags me-2"></i>Total Kategori
                            </h5>
                            <h2 class="text-success"><?php echo $total_categories; ?></h2>
                            <p class="text-muted mb-0">kategori tersedia</p>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-tags fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="modules/categories/index.php" class="btn btn-success btn-sm">
                            <i class="fas fa-arrow-right me-1"></i>Lihat Semua
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>Stok Rendah
                            </h5>
                            <h2 class="text-warning"><?php echo $low_stock_count; ?></h2>
                            <p class="text-muted mb-0">produk perlu perhatian</p>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="modules/products/index.php?filter=low_stock" class="btn btn-warning btn-sm">
                            <i class="fas fa-arrow-right me-1"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Statistik Produk per Kategori
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($category_stats)): ?>
                        <div id="chartContainer">
                            <canvas id="categoryChart"></canvas>
                            <!-- Fallback table jika chart tidak bisa dimuat -->
                            <div id="chartFallback" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Chart tidak dapat dimuat. Menampilkan data dalam bentuk tabel.
                                </div>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kategori</th>
                                            <th>Jumlah Produk</th>
                                            <th>Persentase</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($category_stats as $stat): ?>
                                            <?php $percentage = $total_products > 0 ? round(($stat['total'] / $total_products) * 100, 2) : 0; ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($stat['name']); ?></td>
                                                <td><?php echo $stat['total']; ?></td>
                                                <td><?php echo $percentage; ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            Belum ada data kategori untuk ditampilkan dalam chart.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Ringkasan Kategori
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($category_stats)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($category_stats as $stat): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><?php echo htmlspecialchars($stat['name']); ?></span>
                                    <span class="badge bg-primary rounded-pill"><?php echo $stat['total']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Tidak ada data kategori untuk ditampilkan.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk menampilkan fallback table
    function showFallback() {
        const canvas = document.getElementById('categoryChart');
        const fallback = document.getElementById('chartFallback');
        
        if (canvas) canvas.style.display = 'none';
        if (fallback) fallback.style.display = 'block';
    }
    
    // Cek apakah Chart.js tersedia
    if (typeof Chart === 'undefined') {
        console.error('Chart.js tidak dimuat!');
        showFallback();
        return;
    }
    
    // Cek apakah canvas element ada
    const canvas = document.getElementById('categoryChart');
    if (!canvas) {
        console.error('Canvas element tidak ditemukan!');
        return;
    }
    
    // Cek apakah kita bisa mendapatkan context
    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.error('Tidak bisa mendapatkan 2D context!');
        showFallback();
        return;
    }
    
    // Data untuk chart
    const labels = [
        <?php 
        if (!empty($category_stats)) {
            foreach ($category_stats as $stat) { 
                echo "'" . addslashes(htmlspecialchars($stat['name'])) . "',"; 
            }
        }
        ?>
    ];
    
    const data = [
        <?php 
        if (!empty($category_stats)) {
            foreach ($category_stats as $stat) { 
                echo $stat['total'] . ','; 
            }
        }
        ?>
    ];
    
    // Cek apakah kita punya data
    if (labels.length === 0 || data.length === 0) {
        console.warn('Tidak ada data untuk chart');
        showFallback();
        return;
    }
    
    // Buat chart
    try {
        const categoryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Produk',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        
        console.log('Chart berhasil dibuat');
        
    } catch (error) {
        console.error('Error membuat chart:', error);
        showFallback();
    }
});
</script>

<style>
#categoryChart {
    height: 300px !important;
}

#chartFallback {
    margin-top: 20px;
}
</style>

<?php include 'includes/footer.php'; ?>