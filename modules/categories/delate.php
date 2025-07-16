<?php
// Menyertakan konfigurasi database
require_once '../../config/db.php';

// Cek apakah request adalah POST dan ID tersedia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    if ($id > 0) {
        try {
            // Hapus kategori langsung
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result && $stmt->rowCount() > 0) {
                header("Location: index.php?success=" . urlencode("Kategori berhasil dihapus"));
            } else {
                header("Location: index.php?error=" . urlencode("Kategori tidak ditemukan atau sudah dihapus"));
            }
            
        } catch (PDOException $e) {
            error_log("Delete error: " . $e->getMessage());
            header("Location: index.php?error=" . urlencode("Gagal menghapus kategori: " . $e->getMessage()));
        }
    } else {
        header("Location: index.php?error=" . urlencode("ID tidak valid"));
    }
} else {
    header("Location: index.php?error=" . urlencode("Request tidak valid"));
}
exit();
?>