<?php
require_once '../includes/config.php';
require_once '../models/Transaction.php';
require_once '../models/Item.php';
require_once '../models/Borrower.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$transaction = new Transaction();
$item = new Item();
$borrower = new Borrower();

// Get all transactions including returned
$filters = [];
if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
if (isset($_GET['status'])) $filters['status'] = $_GET['status'];
if (isset($_GET['department'])) $filters['department'] = $_GET['department'];
if (isset($_GET['date_from']) && isset($_GET['date_to'])) {
    $filters['date_from'] = $_GET['date_from'];
    $filters['date_to'] = $_GET['date_to'];
}
$filters['include_returned'] = true;

$transactions_list = $transaction->getAll($filters);
$borrowers_list = $borrower->getAll();
$items_list = $item->getAll();
?>

<?php include '../includes/navigation.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - Transaction History</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">

        <div class="form-container">
            <h3>Transaction History</h3>
            <p>All transactions including completed and returned ones.</p>
            <div class="form-row">
                <button class="btn btn-primary" onclick="exportTransactionHistory()">Export CSV</button>
                <button class="btn btn-secondary" onclick="printTransactionHistory()">Print</button>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="form-container">
            <h4>Filter Transactions</h4>
            <form method="GET" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" placeholder="Search transactions..."
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending"
                                <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>
                                Pending</option>
                            <option value="approved"
                                <?php echo (isset($_GET['status']) && $_GET['status'] == 'approved') ? 'selected' : ''; ?>>
                                Approved</option>
                            <option value="issued"
                                <?php echo (isset($_GET['status']) && $_GET['status'] == 'issued') ? 'selected' : ''; ?>>
                                Issued</option>
                            <option value="returned"
                                <?php echo (isset($_GET['status']) && $_GET['status'] == 'returned') ? 'selected' : ''; ?>>
                                Returned</option>
                            <option value="overdue"
                                <?php echo (isset($_GET['status']) && $_GET['status'] == 'overdue') ? 'selected' : ''; ?>>
                                Overdue</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" placeholder="Filter by department..."
                            value="<?php echo isset($_GET['department']) ? htmlspecialchars($_GET['department']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="date_from">Date From</label>
                        <input type="date" id="date_from" name="date_from"
                            value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="date_to">Date To</label>
                        <input type="date" id="date_to" name="date_to"
                            value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="transaction_history.php" class="btn btn-secondary">Clear Filters</a>
            </form>
        </div>

        <!-- Transactions List -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Borrower</th>
                        <th>Activity Purpose</th>
                        <th>Place of Activity</th>
                        <th>Date Requested</th>
                        <th>Date Needed</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Items Borrowed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions_list as $trans): ?>
                        <tr>
                            <td><?php echo $trans['transaction_id']; ?></td>
                            <td>
                                <strong><?php echo $trans['full_name']; ?></strong><br>
                                <small><?php echo $trans['id_number']; ?><br><?php echo $trans['department_course_office']; ?></small>
                            </td>
                            <td><?php echo $trans['activity_purpose']; ?></td>
                            <td><?php echo $trans['place_of_activity']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($trans['date_requested'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($trans['date_needed'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($trans['date_of_return'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $trans['status']; ?>">
                                    <?php echo ucfirst($trans['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $items = $transaction->getItems($trans['transaction_id']);
                                if (!empty($items)) {
                                    foreach ($items as $item) {
                                        echo htmlspecialchars($item['item_name']) . ' (' . intval($item['quantity_issued']) . ')<br>';
                                    }
                                } else {
                                    echo 'No items borrowed';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        function exportTransactionHistory() {
            // Submit form to export.php with type=transactions
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../export.php';

            const inputType = document.createElement('input');
            inputType.type = 'hidden';
            inputType.name = 'type';
            inputType.value = 'transactions';
            form.appendChild(inputType);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        function printTransactionHistory() {
            let table = document.querySelector('.table').outerHTML;
            let originalContents = document.body.innerHTML;

            // Create a print-friendly version
            let printContents = `
                <html>
                <head>
                    <title>Transaction History</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .status-badge { padding: 2px 8px; border-radius: 4px; font-size: 12px; }
                        .status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
                        .status-approved { background: rgba(16, 185, 129, 0.1); color: #10b981; }
                        .status-issued { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
                        .status-returned { background: rgba(6, 182, 212, 0.1); color: #06b6d4; }
                        .status-overdue { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
                    </style>
                </head>
                <body>
                    <h2>Transaction History</h2>
                    ${table}
                </body>
                </html>
            `;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }
    </script>
</body>

</html>