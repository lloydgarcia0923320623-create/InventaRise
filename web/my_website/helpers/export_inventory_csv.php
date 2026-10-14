<?php
require_once __DIR__ . '/delivery_db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="inventarise_inventory_export.csv"');

$handle = fopen('php://output', 'w');

fputcsv($handle, ['item', 'quantity', 'price', 'category', 'supplier', 'reorder_level', 'updated_at']);

foreach (getInventoryCatalog() as $row) {
    fputcsv($handle, [
        $row['item'] ?? '',
        (int)($row['quantity'] ?? 0),
        (float)($row['price'] ?? 0),
        $row['category'] ?? 'General',
        $row['supplier'] ?? 'N/A',
        (int)($row['reorder_level'] ?? 0),
        $row['updated_at'] ?? '',
    ]);
}

fclose($handle);
exit;
