<?php
define('LOW_STOCK_THRESHOLD', 10);

function getDeliveryDB(): PDO {
    static $conn = null; 

    if ($conn !== null) {
        return $conn;
    }

   
    $db_file = realpath(__DIR__ . '/../db') . '/deliveries.db';

    
    if (!file_exists(dirname($db_file))) {
        mkdir(dirname($db_file), 0755, true);
    }

    try {

        $conn = new PDO("sqlite:" . $db_file);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    
        createDeliveriesTable($conn);
        createDeliveredItemsTable($conn);
        createInventoryTable($conn);
        createSuppliersTable($conn);
        ensureInventoryMetadata($conn);

        $columns = $conn->query("PRAGMA table_info(deliveries)")->fetchAll();
        $hasPrice = array_filter($columns, fn($col) => $col['name'] === 'price');
        if (empty($hasPrice)) {
            $conn->exec("ALTER TABLE deliveries ADD COLUMN price REAL DEFAULT 0");
        }

        return $conn;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// TABLE CREATION
function createDeliveriesTable(PDO $conn): void{
    $conn->exec("
        CREATE TABLE IF NOT EXISTS deliveries (
            delivery_id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            client_name TEXT NOT NULL,
            address TEXT NOT NULL,
            item TEXT NOT NULL,
            quantity INTEGER NOT NULL,
            delivery_date DATE NOT NULL,
            status INTEGER CHECK(status IN (1,2,3,4)) DEFAULT 1,
            assigned_driver TEXT,
            contact TEXT,
            price REAL DEFAULT 0, -- For revenue tracking
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");
}

function createDeliveredItemsTable(PDO $conn): void {
    $conn->exec("
        CREATE TABLE IF NOT EXISTS delivered_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            item TEXT NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 0,
            price REAL DEFAULT 0,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");
}

function createInventoryTable(PDO $conn): void{
    $conn->exec("
        CREATE TABLE IF NOT EXISTS inventory (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            item TEXT NOT NULL UNIQUE,
            quantity INTEGER NOT NULL DEFAULT 0,
            price REAL DEFAULT 0,
            category TEXT DEFAULT 'General',
            supplier TEXT DEFAULT 'N/A',
            reorder_level INTEGER DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");
}

function createSuppliersTable(PDO $conn): void {
    $conn->exec("
        CREATE TABLE IF NOT EXISTS suppliers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            supplier_name TEXT NOT NULL UNIQUE,
            contact_person TEXT,
            email TEXT,
            phone TEXT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");
}

function ensureInventoryMetadata(PDO $conn): void {
    $columns = $conn->query("PRAGMA table_info(inventory)")->fetchAll(PDO::FETCH_ASSOC);
    $existingColumns = array_map(static fn($column) => $column['name'], $columns);

    $newColumns = [
        'category' => 'ALTER TABLE inventory ADD COLUMN category TEXT DEFAULT "General"',
        'supplier' => 'ALTER TABLE inventory ADD COLUMN supplier TEXT DEFAULT "N/A"',
        'reorder_level' => 'ALTER TABLE inventory ADD COLUMN reorder_level INTEGER DEFAULT 0',
    ];

    foreach ($newColumns as $columnName => $alterSql) {
        if (!in_array($columnName, $existingColumns, true)) {
            $conn->exec($alterSql);
        }
    }
}

// DELIVERY OPERATIONS
function getAllDeliveries(): array{
    $conn = getDeliveryDB();
    $stmt = $conn->query("SELECT * FROM deliveries ORDER BY delivery_id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insertDelivery(array $data): int{
    $conn = getDeliveryDB();
    $stmt = $conn->prepare("
        INSERT INTO deliveries (user_id, client_name, address, item, quantity, delivery_date, status, contact, price)
        VALUES (:user_id, :client_name, :address, :item, :quantity, :delivery_date, :status, :contact, :price)
    ");
    $stmt->execute($data);
    return (int) $conn->lastInsertId();
}

function moveDeliveredToDeliveredItems(): void {
    $conn = getDeliveryDB();

    $columns = $conn->query("PRAGMA table_info(deliveries)")->fetchAll();
    $hasMovedFlag = array_filter($columns, fn($col) => $col['name'] === 'moved_to_delivered');
    if (empty($hasMovedFlag)) {
        $conn->exec("ALTER TABLE deliveries ADD COLUMN moved_to_delivered INTEGER DEFAULT 0");
    }

    $stmt = $conn->prepare("
        INSERT INTO delivered_items (item, quantity, price)
        SELECT item, quantity, price
        FROM deliveries
        WHERE status = 3 AND moved_to_delivered = 0
    ");
    $stmt->execute();

    $conn->exec("UPDATE deliveries SET moved_to_delivered = 1 WHERE status = 3 AND moved_to_delivered = 0");
}

function statusLabel(int $statusCode): string{
    $map = [
        1 => 'Pending',
        2 => 'In Transit',
        3 => 'Delivered',
        4 => 'Delayed'
    ];
    return $map[$statusCode] ?? 'Unknown';
}

function getFreshDeliveryDB(): PDO{
    return getDeliveryDB();
}

    function getInventoryStats() {
    $conn = getDeliveryDB();

    moveDeliveredToDeliveredItems();

    $stmtTotalItems = $conn->query("
        SELECT COALESCE(SUM(quantity), 0) AS total_items
        FROM inventory
    ");
    $totalItems = $stmtTotalItems->fetch(PDO::FETCH_ASSOC)['total_items'] ?? 0;

    $stmtPending = $conn->query("
        SELECT COUNT(*) AS total
        FROM deliveries
        WHERE status = 1
    ");
    $pendingOrders = $stmtPending->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $stmtLowStock = $conn->prepare("
        SELECT item, quantity
        FROM inventory
        WHERE quantity <= :threshold
    ");
    $stmtLowStock->execute([':threshold' => LOW_STOCK_THRESHOLD]);
    $lowStock = $stmtLowStock->fetchAll(PDO::FETCH_ASSOC);
    $lowStockCount = count($lowStock);

    $stmtDelivered = $conn->query("
        SELECT *
        FROM delivered_items
        ORDER BY added_at DESC
    ");
    $delivered_list = $stmtDelivered->fetchAll(PDO::FETCH_ASSOC);

    $stmtInventory = $conn->query("
        SELECT item, SUM(quantity) AS total_quantity
        FROM inventory
        GROUP BY item
    ");
    $inventory_list = $stmtInventory->fetchAll(PDO::FETCH_ASSOC);

    return [
        'total_items' => (int)$totalItems,
        'low_stock' => (int)$lowStockCount,
        'pending_orders' => (int)$pendingOrders,
        'delivered_list' => $delivered_list,
        'inventory_list' => $inventory_list,
    ];
}

function getSuppliers(): array {
    $conn = getDeliveryDB();
    $stmt = $conn->query("SELECT * FROM suppliers ORDER BY supplier_name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addSupplier(array $data): int {
    $conn = getDeliveryDB();
    $stmt = $conn->prepare("
        INSERT INTO suppliers (supplier_name, contact_person, email, phone, notes)
        VALUES (:supplier_name, :contact_person, :email, :phone, :notes)
    ");
    $stmt->execute([
        ':supplier_name' => trim($data['supplier_name'] ?? ''),
        ':contact_person' => trim($data['contact_person'] ?? ''),
        ':email' => trim($data['email'] ?? ''),
        ':phone' => trim($data['phone'] ?? ''),
        ':notes' => trim($data['notes'] ?? ''),
    ]);

    return (int)$conn->lastInsertId();
}

function getInventoryCatalog(): array {
    $conn = getDeliveryDB();
    $stmt = $conn->query("SELECT * FROM inventory ORDER BY category, item ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// REPORT ANALYTICS
function getLowStockItems($threshold = LOW_STOCK_THRESHOLD): array{
    $db = getDeliveryDB();

    $stmt = $db->prepare("
        SELECT item, SUM(quantity) AS total_qty
        FROM inventory
        GROUP BY item
        HAVING total_qty <= :threshold
    ");
    $stmt->execute([':threshold' => $threshold]);

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            'item' => $row['item'],
            'total_qty' => (int) $row['total_qty']
        ];
    }

    return $data;
}

function getMonthlyDeliveries(): array{
    $db = getDeliveryDB();

    $stmt = $db->query("
        SELECT 
            strftime('%Y-%m', delivery_date) AS month,
            COUNT(*) AS deliveries
        FROM deliveries
        GROUP BY month
        ORDER BY month ASC
    ");

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            'month' => $row['month'],
            'deliveries' => (int) $row['deliveries']
        ];
    }

    return $data;
}

function getRevenueOverview(): array{
    $db = getDeliveryDB();

    $stmt = $db->query("
        SELECT 
            strftime('%Y-%m', delivery_date) AS month,
            SUM(price * quantity) AS revenue
        FROM deliveries
        WHERE status = 3 -- Delivered only
        GROUP BY month
        ORDER BY month ASC
    ");

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            'month' => $row['month'],
            'revenue' => (float) $row['revenue']
        ];
    }

    return $data;
}

?>