<?php
require_once __DIR__ . '/delivery_db.php';

header('Content-Type: application/json');

$item_name = trim($_POST['item'] ?? '');
$quantity = intval($_POST['quantity'] ?? 0);
$price = floatval($_POST['price'] ?? 0); 

if ($item_name === '' || $quantity <= 0) {
    echo json_encode(['status' => 'error', 'message' => '⚠️ Invalid item or quantity.']);
    exit;
}

try {
    $conn = getDeliveryDB();

    $stmt = $conn->prepare("
        INSERT INTO inventory (item, quantity, price)
        VALUES (:item, :quantity, :price)
        ON CONFLICT(item) DO UPDATE SET
            quantity = excluded.quantity,
             price = excluded.price,
            updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([
        ':item' => $item_name,
        ':quantity' => $quantity,
        ':price' => $price
    ]);

    echo json_encode(['status' => 'success', 'message' => '✅ Inventory updated successfully!']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '⚠️ Database error: ' . $e->getMessage()]);
}