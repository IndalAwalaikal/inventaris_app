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
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch();

    if (!$product) {
        header("Location: index.php");
        exit();
    }

    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $image = $_FILES['image']['name'] ?? '';
    $status_barang = $_POST['status_barang'] ?? 'baik'; // Default ke 'baik' jika tidak diisi

    if (empty($name) || empty($price) || empty($stock)) {
        $error = "Nama, harga, dan stok wajib diisi.";
    } else {
        try {
            if ($image) {
                $target_dir = "../../uploads/products/";
                $target_file = $target_dir . basename($image);
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($imageFileType, $allowed_types)) {
                    $error = "Hanya file JPG, JPEG, PNG, dan GIF yang diperbolehkan.";
                } elseif ($_FILES['image']['size'] > 5000000) {
                    $error = "Ukuran file terlalu besar (maksimum 5MB).";
                } else {
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $error = "Gagal mengunggah gambar.";
                    }
                }
            }

            if (!$error) {
                $stmt = $pdo->prepare("UPDATE products SET category_id = :category_id, name = :name, description = :description, price = :price, stock = :stock, image = :image, status_barang = :status_barang WHERE id = :id");
                $stmt->execute([
                    'category_id' => $category_id,
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'stock' => $stock,
                    'image' => $image ?: $product['image'],
                    'status_barang' => $status_barang,
                    'id' => $id
                ]);
                $success = "Produk berhasil diperbarui.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<div class="main-content">
    <h1 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Produk</h1>
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
    <form action="edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" id="productForm">
        <div class="mb-3">
            <label for="category_id" class="form-label">Kategori</label>
            <select class="form-control" id="category_id" name="category_id">
                <option value="">Pilih Kategori</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Nama Produk</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Harga</label>
            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="stock" class="form-label">Stok</label>
            <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?php echo $product['stock']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Gambar Produk</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(event)">
            <?php if ($product['image']): ?>
                <img id="imagePreview" src="../../uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="Gambar Produk" class="mt-2">
            <?php else: ?>
                <img id="imagePreview" style="display: none;" alt="Pratinjau Gambar">
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="status_barang" class="form-label">Status Barang</label>
            <select class="form-control" id="status_barang" name="status_barang" required>
                <option value="baik" <?php echo $product['status_barang'] === 'baik' ? 'selected' : ''; ?>>Baik</option>
                <option value="rusak" <?php echo $product['status_barang'] === 'rusak' ? 'selected' : ''; ?>>Rusak</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan</button>
    </form>
</div>
<?php include '../../includes/footer.php'; ?>