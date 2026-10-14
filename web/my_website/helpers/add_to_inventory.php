<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/delivery_db.php';
header('Content-Type: application/json');

try {
    $item = trim($_POST['item'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);

    if (!$item || $quantity <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => '⚠️ Invalid item or quantity.'
        ]);
        exit;
    }

    $pdo = getDeliveryDB();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id, quantity FROM delivered_items WHERE item = ? ORDER BY id ASC LIMIT 1");
    $stmt->execute([$item]);
    $delivered = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$delivered) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => "⚠️ '$item' not found in delivered items."
        ]);
        exit;
    }

    $checkInventory = $pdo->prepare("SELECT quantity FROM inventory WHERE item = ?");
    $checkInventory->execute([$item]);
    $existingQty = $checkInventory->fetchColumn();

    if ($existingQty !== false) {

        $updateStmt = $pdo->prepare("
            UPDATE inventory 
            SET quantity = quantity + ?, updated_at = CURRENT_TIMESTAMP
            WHERE item = ?
        ");
        $updateStmt->execute([$quantity, $item]);
    } else {

        $insertStmt = $pdo->prepare("
            INSERT INTO inventory (item, quantity, price, updated_at)
            VALUES (?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $insertStmt->execute([$item, $quantity, 0]);
    }

    $deleteStmt = $pdo->prepare("DELETE FROM delivered_items WHERE id = ?");
    $deleteStmt->execute([$delivered['id']]);

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => "✅ '$item' added to inventory successfully!"
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => "⚠️ Failed to add to inventory: " . $e->getMessage()
    ]);
}