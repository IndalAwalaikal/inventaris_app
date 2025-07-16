<?php
// Menyertakan konfigurasi database
require_once '../../config/db.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: index.php?success=Produk berhasil dihapus");
    } catch (PDOException $e) {
        error_log("Delete error: " . $e->getMessage());
        header("Location: index.php?error=" . urlencode("Gagal menghapus produk: " . $e->getMessage()));
    }
} else {
    header("Location: index.php?error=ID tidak valid");
}
exit();
?>