<?php
require_once __DIR__ . '/delivery_db.php';

function getAnalyticsPayload(): array
{
    return [
        'low_stock' => getLowStockItems(),
        'monthly_deliveries' => getMonthlyDeliveries(),
        'revenue_overview' => getRevenueOverview(),
    ];
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    header('Content-Type: application/json');

    try {
        echo json_encode([
            'status' => 'success',
            'data' => getAnalyticsPayload(),
        ], JSON_THROW_ON_ERROR);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
        ]);
    }

    exit;
}
