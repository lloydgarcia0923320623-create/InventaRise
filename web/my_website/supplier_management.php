<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: LogIn.php');
    exit();
}

require_once __DIR__ . '/helpers/delivery_db.php';

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_supplier') {
    $supplierName = trim((string)($_POST['supplier_name'] ?? ''));
    $contactPerson = trim((string)($_POST['contact_person'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $notes = trim((string)($_POST['notes'] ?? ''));

    if ($supplierName === '') {
        $message = '⚠️ Supplier name is required.';
        $messageType = 'error';
    } else {
        try {
            addSupplier([
                'supplier_name' => $supplierName,
                'contact_person' => $contactPerson,
                'email' => $email,
                'phone' => $phone,
                'notes' => $notes,
            ]);
            $message = '✅ Supplier added successfully.';
            $messageType = 'success';
        } catch (Throwable $e) {
            $message = '⚠️ Unable to add supplier.';
            $messageType = 'error';
        }
    }
}

if (isset($_GET['delete_supplier']) && $_GET['delete_supplier'] !== '') {
    $supplierId = (int)$_GET['delete_supplier'];
    if ($supplierId > 0) {
        $conn = getDeliveryDB();
        $conn->prepare('DELETE FROM suppliers WHERE id = :id')->execute([':id' => $supplierId]);
        $message = '✅ Supplier removed.';
        $messageType = 'success';
    }
}

$suppliers = getSuppliers();
$inventory = getInventoryCatalog();
$inventoryBySupplier = [];
foreach ($inventory as $item) {
    $supplierName = trim((string)($item['supplier'] ?? 'N/A'));
    if (!isset($inventoryBySupplier[$supplierName])) {
        $inventoryBySupplier[$supplierName] = 0;
    }
    $inventoryBySupplier[$supplierName] += (int)($item['quantity'] ?? 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Management | InventaRise</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #091726, #102a43 35%, #0a1b2a);
            color: #eaf7ff;
        }
        .topbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 18px 28px; background: rgba(9, 22, 38, 0.95); border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand img { width: 42px; height: 42px; border-radius: 12px; }
        .brand h1 { margin: 0; font-size: 1.7rem; }
        .nav-actions { display: flex; gap: 12px; flex-wrap: wrap; }
        .nav-btn, .primary-btn, .danger-btn {
            border: none; border-radius: 10px; padding: 10px 16px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block;
        }
        .nav-btn { background: rgba(255,255,255,0.08); color: #fff; }
        .primary-btn { background: #00c2ff; color: #051722; }
        .danger-btn { background: #ef4444; color: white; }
        .page { max-width: 1200px; margin: 32px auto; padding: 0 20px 40px; }
        .header-card {
            background: rgba(10, 32, 48, 0.9); border: 1px solid rgba(255,255,255,0.06); border-radius: 18px; padding: 24px; box-shadow: 0 18px 40px rgba(0,0,0,0.18);
        }
        .metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 18px; margin-top: 24px; }
        .metric {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 14px; padding: 18px; min-height: 120px;
        }
        .metric h3 { margin: 0 0 14px; color: #8bd3ff; }
        .metric p { margin: 0; font-size: 2rem; font-weight: 700; }
        .content-grid { display: grid; grid-template-columns: 1.1fr 1.4fr; gap: 22px; margin-top: 28px; }
        .panel {
            background: rgba(10, 32, 48, 0.9); border: 1px solid rgba(255,255,255,0.06); border-radius: 18px; padding: 22px; box-shadow: 0 18px 40px rgba(0,0,0,0.12);
        }
        .panel h2 { margin-top: 0; }
        form { display: grid; gap: 14px; }
        label { display: grid; gap: 8px; font-size: 0.95rem; }
        input, textarea { border-radius: 10px; border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.04); padding: 12px 14px; color: #fff; }
        textarea { min-height: 90px; resize: vertical; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 10px; border-bottom: 1px solid rgba(255,255,255,0.08); text-align: left; vertical-align: top; }
        th { color: #9fd8ff; }
        .status-box { display: inline-block; padding: 6px 10px; border-radius: 999px; background: rgba(0,194,255,0.1); color: #8ae4ff; }
        .message { margin-top: 18px; padding: 12px 14px; border-radius: 10px; border: 1px solid transparent; }
        .message.success { background: rgba(34,197,94,0.12); border-color: rgba(34,197,94,0.3); color: #b4f6c7; }
        .message.error { background: rgba(239,68,68,0.12); border-color: rgba(239,68,68,0.3); color: #ffc2c2; }
        @media (max-width: 900px) { .content-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="brand">
            <img src="images/Logo.png" alt="Logo">
            <h1>InventaRise</h1>
        </div>
        <div class="nav-actions">
            <a class="nav-btn" href="home.php">Home</a>
            <a class="nav-btn" href="services.php">Services</a>
            <a class="nav-btn" href="portfolio.php">Portfolio</a>
            <a class="nav-btn" href="LogIn.php?logout=1">Logout</a>
        </div>
    </div>

    <div class="page">
        <div class="header-card">
            <h2 style="margin-top:0;">Supplier Network</h2>
            <p>Keep supplier contacts, delivery partners, and stock visibility in one place for smoother retail operations.</p>
            <div class="metrics">
                <div class="metric">
                    <h3>Total Suppliers</h3>
                    <p><?= count($suppliers) ?></p>
                </div>
                <div class="metric">
                    <h3>Active Inventory</h3>
                    <p><?= array_sum(array_map(fn($item) => (int)($item['quantity'] ?? 0), $inventory)) ?></p>
                </div>
                <div class="metric">
                    <h3>Supplier Coverage</h3>
                    <p><?= count($inventoryBySupplier) ?></p>
                </div>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="message <?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="content-grid">
            <div class="panel">
                <h2>Add Supplier</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_supplier">
                    <label>
                        Supplier Name
                        <input type="text" name="supplier_name" placeholder="e.g. Prime Retail Supply" required>
                    </label>
                    <label>
                        Contact Person
                        <input type="text" name="contact_person" placeholder="Name of contact">
                    </label>
                    <label>
                        Email
                        <input type="email" name="email" placeholder="supplier@email.com">
                    </label>
                    <label>
                        Phone
                        <input type="tel" name="phone" placeholder="+1 555 123 4567">
                    </label>
                    <label>
                        Notes
                        <textarea name="notes" placeholder="Delivery terms, preferred schedule, or notes..."></textarea>
                    </label>
                    <button class="primary-btn" type="submit">Save Supplier</button>
                </form>
            </div>

            <div class="panel">
                <h2>Supplier Directory</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Contact</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($suppliers)): ?>
                            <tr>
                                <td colspan="6">No suppliers added yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($supplier['supplier_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($supplier['contact_person'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($supplier['phone'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($supplier['email'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($supplier['notes'] ?: 'No notes') ?></td>
                                    <td>
                                        <a class="danger-btn" href="supplier_management.php?delete_supplier=<?= (int)$supplier['id'] ?>" onclick="return confirm('Remove this supplier?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
