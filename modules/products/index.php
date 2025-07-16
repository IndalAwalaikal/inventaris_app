<?php
// Mulai sesi dan sertakan file yang diperlukan
session_start();
require_once '../../includes/header.php';
require_once '../../config/db.php';

// Logika untuk menghapus produk (diletakkan di awal sebelum output)
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product) {
            if ($product['image'] && file_exists('../../uploads/products/' . $product['image'])) {
                unlink('../../uploads/products/' . $product['image']);
            }

            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);

            $_SESSION['success'] = "Produk berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Produk tidak ditemukan!";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    // Redirect sebelum output
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Ambil data produk
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<div class="main-content">
    <h1 class="mb-4"><i class="fas fa-box me-2"></i>Daftar Produk</h1>
    <div class="mb-3">
        <a href="add.php" class="btn btn-success me-2"><i class="fas fa-plus me-2"></i>Tambah Produk</a>
        <a href="export.php" class="btn btn-info"><i class="fas fa-download me-2"></i>Export ke CSV</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <table id="productsTable" class="table table-bordered table-hover">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Gambar</th>
                <th>Status Barang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['id']) ?></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['category_name'] ?? 'Tanpa Kategori') ?></td>
                    <td>Rp <?= number_format($product['price'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($product['stock']) ?></td>
                    <td>
                        <?php if ($product['image']): ?>
                            <img src="../../uploads/products/<?= htmlspecialchars($product['image']) ?>" alt="Gambar Produk" loading="lazy" style="max-width: 85px; max-height: 85px; object-fit: cover;">
                        <?php else: ?>
                            Tidak ada gambar
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($product['status_barang'] ?? 'baik') ?></td>
                    <td>
                        <a href="edit.php?id=<?= $product['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                        <a href="javascript:void(0)" onclick="confirmDelete(<?= $product['id'] ?>)" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function confirmDelete(productId) {
        if (confirm("Apakah Anda yakin ingin menghapus produk ini?")) {
            window.location.href = "index.php?delete=" + productId;
        }
    }
</script>

<?php include '../../includes/footer.php'; ?>