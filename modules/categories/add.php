<?php
// Menyertakan header dan konfigurasi database
require_once '../../includes/header.php';
require_once '../../config/db.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = "Nama kategori wajib diisi.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
            $stmt->execute([
                'name' => $name,
                'description' => $description
            ]);
            $success = "Kategori berhasil ditambahkan.";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<div class="main-content">
    <h1 class="mb-4"><i class="fas fa-plus me-2"></i>Tambah Kategori</h1>
    <a href="index.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '<?php echo htmlspecialchars($success); ?>',
            });
        </script>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '<?php echo htmlspecialchars($error); ?>',
            });
        </script>
    <?php endif; ?>
    <form action="add.php" method="POST" id="categoryForm">
        <div class="mb-3">
            <label for="name" class="form-label">Nama Kategori</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan</button>
    </form>
</div>
<?php include '../../includes/footer.php'; ?>