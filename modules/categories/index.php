<?php
// Menyertakan header dan konfigurasi database
require_once '../../includes/header.php';
require_once '../../config/db.php';

// Cek pesan success/error dari URL
$success_message = '';
$error_message = '';

if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}

if (isset($_GET['error'])) {
    $error_message = $_GET['error'];
}

try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY created_at ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>
<div class="main-content">
    <h1 class="mb-4"><i class="fas fa-tags me-2"></i>Daftar Kategori</h1>
    <a href="add.php" class="btn btn-success mb-3"><i class="fas fa-plus me-2"></i>Tambah Kategori</a>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '<?php echo htmlspecialchars($success_message); ?>',
            });
        </script>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '<?php echo htmlspecialchars($error_message); ?>',
            });
        </script>
    <?php endif; ?>
    
    <table id="categoriesTable" class="table table-bordered table-hover">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?php echo htmlspecialchars($category['id']); ?></td>
                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                    <td><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $category['id']; ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="delate.php" method="POST" style="display:inline;" 
                              onsubmit="return confirmDelete('<?php echo htmlspecialchars($category['name']); ?>');">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($category['id']); ?>">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDelete(categoryName) {
    return confirm('Apakah Anda yakin ingin menghapus kategori "' + categoryName + '"?');
}
</script>

<?php include '../../includes/footer.php'; ?>