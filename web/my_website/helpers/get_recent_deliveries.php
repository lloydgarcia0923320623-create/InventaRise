<?php
require 'delivery_db.php';
header('Content-Type: application/json');

$item = $_GET['item'] ?? '';
if (!$item) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'No item specified'
    ]);
    exit;
}

$db = getDeliveryDB();

$stmt = $db->prepare("
    SELECT client_name, address, item, quantity, delivery_date, contact
    FROM deliveries
    WHERE item = ?
    ORDER BY delivery_date DESC
    LIMIT 2
");
$stmt->execute([$item]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($results) {
    echo json_encode([
        'status' => 'success', 
        'data' => $results
    ]);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'No previous deliveries found'
    ]);
}