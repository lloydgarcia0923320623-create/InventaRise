<?php
require_once __DIR__ . '/delivery_db.php';
header('Content-Type: application/json');

try {
    
    $conn = getDeliveryDB();

    $item_name = trim($_POST['item'] ?? '');
    $new_quantity = intval($_POST['quantity'] ?? 0);

    if ($item_name === '' || $new_quantity < 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit;
    }

    $check = $conn->prepare("SELECT COUNT(*) FROM inventory WHERE item = :item");
    $check->execute([':item' => $item_name]);
    $exists = $check->fetchColumn();

    if ($exists) {

        $stmt = $conn->prepare("
            UPDATE inventory
            SET quantity = :quantity, updated_at = CURRENT_TIMESTAMP
            WHERE item = :item
        ");
        $stmt->execute([':quantity' => $new_quantity, ':item' => $item_name]);
        echo json_encode(['status' => 'success', 'message' => 'Inventory quantity updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found in inventory']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
