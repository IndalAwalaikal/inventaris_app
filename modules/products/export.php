<?php
require_once '../../config/db.php';

try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
    $products = $stmt->fetchAll();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="products_export_' . date('Ymd_His') . '.csv"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Nama', 'Kategori', 'Harga', 'Stok', 'Gambar', 'Tanggal Dibuat', 'Tanggal Diperbarui']);

    foreach ($products as $product) {
        fputcsv($output, [
            $product['id'],
            $product['name'],
            $product['category_name'] ?? 'Tanpa Kategori',
            'Rp ' . number_format($product['price'], 2, ',', '.'),
            $product['stock'],
            $product['image'] ?? 'Tidak ada',
            $product['created_at'],
            $product['updated_at']
        ]);
    }

    fclose($output);
    exit();
} catch (PDOException $e) {
    die("Gagal ekspor data: " . $e->getMessage());
}
