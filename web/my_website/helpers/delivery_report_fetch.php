<?php
require_once 'helpers/delivery_db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "<p class='error_message'>You must be logged in.</p>";
    exit;
}

createDeliveriesTable();

$conn = getDeliveryDB();

$delivery_id = $_GET['delivery_id'] ?? '';

if ($delivery_id) {
    $delivery_id = trim($delivery_id);

    if (preg_match('/^DID\s*(\d+)$/i', $delivery_id, $matches)) {
        $id = intval($matches[1]);
        $stmt = $conn->prepare("
            SELECT * FROM deliveries
            WHERE user_id = :user_id AND id = :id
            ORDER BY created_at DESC
        ");
        $stmt->execute([
            ':user_id' => $user_id,
            ':id' => $id
        ]);
    } else {

        $stmt = $conn->prepare("
            SELECT * FROM deliveries
            WHERE user_id = :user_id
            AND (client_name LIKE :search OR item LIKE :search)
            ORDER BY created_at DESC
        ");
        $stmt->execute([
            ':user_id' => $user_id,
            ':search' => "%$delivery_id%"
        ]);
    }
} else {

    $stmt = $conn->prepare("
        SELECT * FROM deliveries
        WHERE user_id = :user_id
        ORDER BY delivery_date DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
}

$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$deliveries) {
    echo "<p class='error_message'>No deliveries found.</p>";
    exit;
}

echo "<table border='1' cellpadding='10' cellspacing='0'>
        <thead>
            <tr>
                <th>Delivery ID</th>
                <th>Client Name</th>
                <th>Address</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Estimated Delivery</th>
                <th>Assigned Driver</th>
                <th>Contact</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>";

foreach ($deliveries as $d) {
    $did = "DID " . str_pad($d['id'], 3, "0", STR_PAD_LEFT);
    $status_class = strtolower(str_replace(' ', '-', $d['status']));
    echo "<tr>
            <td>{$did}</td>
            <td>".htmlspecialchars($d['client_name'])."</td>
            <td>".htmlspecialchars($d['address'])."</td>
            <td>".htmlspecialchars($d['item'])."</td>
            <td>".intval($d['quantity'])."</td>
            <td><span class='status {$status_class}'>".htmlspecialchars($d['status'])."</span></td>
            <td>".htmlspecialchars($d['delivery_date'])."</td>
            <td>".htmlspecialchars($d['assigned_driver'])."</td>
            <td>".htmlspecialchars($d['contact'])."</td>
            <td>".htmlspecialchars($d['created_at'])."</td>
          </tr>";
}

echo "</tbody></table>";
