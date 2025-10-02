<?php
require_once 'includes/config.php';
require_once 'models/Transaction.php';
require_once 'models/Item.php';
require_once 'models/Borrower.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$transaction = new Transaction();
$item = new Item();
$borrower = new Borrower();

$type = $_POST['type'] ?? '';
$date = $_POST['date'] ?? date('Y-m-d');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $type . '_report_' . $date . '.csv"');

$output = fopen('php://output', 'w');

switch ($type) {
    case 'transactions':
        // Export all transactions
        $transactions = $transaction->getAll(['include_returned' => true]);

        // CSV headers
        fputcsv($output, ['Transaction ID', 'Borrower Name', 'ID Number', 'Department', 'Activity Purpose', 'Place of Activity', 'Date Requested', 'Date Needed', 'Date of Return', 'Status', 'Items']);

        foreach ($transactions as $trans) {
            $items = $transaction->getItems($trans['transaction_id']);
            $item_list = [];
            foreach ($items as $item) {
                $item_list[] = $item['item_name'] . ' (' . $item['quantity_issued'] . ')';
            }

            fputcsv($output, [
                $trans['transaction_id'],
                $trans['full_name'],
                $trans['id_number'],
                $trans['department_course_office'],
                $trans['activity_purpose'],
                $trans['place_of_activity'],
                $trans['date_requested'],
                $trans['date_needed'],
                $trans['date_of_return'],
                $trans['status'],
                implode('; ', $item_list)
            ]);
        }
        break;

    case 'items':
        // Export all items
        $items_list = $item->getAll();

        // CSV headers
        fputcsv($output, ['Item Code', 'Item Name', 'Category', 'Description', 'Total Quantity', 'Available Quantity', 'Status']);

        foreach ($items_list as $item_data) {
            fputcsv($output, [
                $item_data['item_code'],
                $item_data['item_name'],
                $item_data['category'],
                $item_data['description'],
                $item_data['total_quantity'],
                $item_data['available_quantity'],
                $item_data['status']
            ]);
        }
        break;

    case 'borrowers':
        // Export all borrowers
        $borrowers_list = $borrower->getAll();

        // CSV headers
        fputcsv($output, ['ID Number', 'Full Name', 'Department/Course/Office', 'Contact Number', 'Email', 'Status']);

        foreach ($borrowers_list as $borrower_data) {
            fputcsv($output, [
                $borrower_data['id_number'],
                $borrower_data['full_name'],
                $borrower_data['department_course_office'],
                $borrower_data['contact_number'],
                $borrower_data['email'],
                $borrower_data['status']
            ]);
        }
        break;

    default:
        // Default to transactions
        header('Location: views/reports.php');
        exit;
}

fclose($output);
exit;
