<?php
require_once '../includes/config.php';
require_once '../models/Transaction.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['transaction_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Transaction ID required']);
    exit;
}

$transaction_id = intval($_GET['transaction_id']);
$transaction = new Transaction();
$items = $transaction->getItems($transaction_id);

header('Content-Type: application/json');
echo json_encode($items);
