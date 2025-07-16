<?php
// Menyertakan header dan konfigurasi database
require_once '../../includes/header.php';
require_once '../../config/db.php';

$success = $error = '';
$id = $_GET['id'] ?? 0;

if (!$id) {
    header("Location: index.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $category = $stmt->fetch();

    if (!$category) {
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = "Nama kategori wajib diisi.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name = :name, description = :description WHERE id = :id");
            $stmt->execute([
                'name' => $name,
                'description' => $description,
                'id' => $id
            ]);
            $success = "Kategori berhasil diperbarui.";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<div class="main-content">
    <h1 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Kategori</h1>
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
    <form action="edit.php?id=<?php echo $id; ?>" method="POST" id="categoryForm">
        <div class="mb-3">
            <label for="name" class="form-label">Nama Kategori</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan</button>
    </form>
</div>
<?php include '../../includes/footer.php'; ?>