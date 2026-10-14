<?php
header("Content-Type: application/json");
require_once __DIR__ . "/delivery_db.php";

try {
    $db = getDeliveryDB(); 

    $item = trim($_POST['item'] ?? '');
    if (!$item) {
        echo json_encode(["status" => "error", "message" => "⚠️ No item specified"]);
        exit;
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM inventory WHERE item = :item");
    $stmt->execute([':item' => $item]);
    if ($stmt->fetchColumn() > 0) {
        $stmt = $db->prepare("DELETE FROM inventory WHERE item = :item");
        $stmt->execute([':item' => $item]);
        echo json_encode(["status" => "success", "message" => "🗑 '$item' deleted from inventory."]);
        exit;
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM lowstock WHERE item = :item");
    $stmt->execute([':item' => $item]);
    if ($stmt->fetchColumn() > 0) {
        $stmt = $db->prepare("DELETE FROM lowstock WHERE item = :item");
        $stmt->execute([':item' => $item]);
        echo json_encode(["status" => "success", "message" => "🗑 '$item' deleted from lowstock."]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "⚠️ Item '$item' not found."]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "⚠️ Error deleting item: " . $e->getMessage()
    ]);
}
?>