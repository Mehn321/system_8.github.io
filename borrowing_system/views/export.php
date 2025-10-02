<?php
require_once '../includes/config.php';
require_once '../models/Transaction.php';
require_once '../models/Item.php';
require_once '../models/Borrower.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$type = isset($_POST['type']) ? $_POST['type'] : (isset($_GET['type']) ? $_GET['type'] : '');
if ($type === '') {
    http_response_code(400);
    echo 'Missing export type';
    exit;
}

$filename = 'export_' . $type . '_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

switch ($type) {
    case 'transactions':
        $transaction = new Transaction();
        $rows = $transaction->getAll();
        fputcsv($output, ['Transaction ID', 'Borrower Name', 'ID Number', 'Activity', 'Date Requested', 'Date Needed', 'Return Date', 'Status']);
        foreach ($rows as $row) {
            fputcsv($output, [
                $row['transaction_id'],
                $row['full_name'],
                $row['id_number'],
                $row['activity_purpose'],
                $row['date_requested'],
                $row['date_needed'],
                $row['date_of_return'],
                $row['status']
            ]);
        }
        break;

    case 'items':
        $item = new Item();
        $rows = $item->getAll();
        fputcsv($output, ['Item Code', 'Item Name', 'Category', 'Available', 'Total', 'Unit']);
        foreach ($rows as $row) {
            fputcsv($output, [
                $row['item_code'],
                $row['item_name'],
                $row['category'],
                $row['available_quantity'],
                $row['total_quantity'],
                $row['unit']
            ]);
        }
        break;

    case 'borrowers':
        $borrower = new Borrower();
        $rows = $borrower->getAll();
        fputcsv($output, ['ID Number', 'Full Name', 'Department/Office', 'Contact Number', 'Email Address']);
        foreach ($rows as $row) {
            fputcsv($output, [
                $row['id_number'],
                $row['full_name'],
                $row['department_course_office'],
                $row['contact_number'],
                $row['email_address']
            ]);
        }
        break;

    default:
        http_response_code(400);
        echo 'Invalid export type';
}

fclose($output);
exit;
?>



