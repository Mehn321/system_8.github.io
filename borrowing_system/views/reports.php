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

// Get report data
$stats = $transaction->getDashboardStats();
$most_borrowed_items = $item->getMostBorrowed(10);
$monthly_report = $transaction->getMonthlyReport();
$overdue_items = $transaction->getOverdueItems();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - Reports</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <h1>Reports & Analytics</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <a href="dashboard.php?logout=1" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Export Options -->
        <div class="form-container">
            <h3>Export Options</h3>
            <div class="form-row">
                <button class="btn btn-primary" onclick="exportReport('transactions')">Export Transactions</button>
                <button class="btn btn-primary" onclick="exportReport('items')">Export Items</button>
                <button class="btn btn-primary" onclick="exportReport('borrowers')">Export Borrowers</button>
                <button class="btn btn-secondary" onclick="printReport()">Print Report</button>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Transactions</h3>
                <div class="stat-number"><?php echo $transaction->getCount(); ?></div>
                <div class="stat-label">All time transactions</div>
            </div>

            <div class="stat-card">
                <h3>Active Items</h3>
                <div class="stat-number"><?php echo $item->getCount(); ?></div>
                <div class="stat-label">Items in inventory</div>
            </div>

            <div class="stat-card">
                <h3>Total Borrowers</h3>
                <div class="stat-number"><?php echo $borrower->getCount(); ?></div>
                <div class="stat-label">Registered borrowers</div>
            </div>

            <div class="stat-card">
                <h3>Overdue Items</h3>
                <div class="stat-number" style="color: #dc3545;"><?php echo $stats['overdue']; ?></div>
                <div class="stat-label">Items past due date</div>
            </div>
        </div>

        <!-- Report Sections -->
        <div class="nav-tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" href="#overview" role="tab" data-bs-toggle="tab">Overview</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="#items" role="tab" data-bs-toggle="tab">Items Report</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="#overdue" role="tab" data-bs-toggle="tab">Overdue Items</a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview">
                <div class="form-container">
                    <h3>Monthly Activity Report</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Total Transactions</th>
                                    <th>Items Borrowed</th>
                                    <th>Items Returned</th>
                                    <th>Overdue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthly_report as $month): ?>
                                    <tr>
                                        <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                                        <td><?php echo $month['total_transactions']; ?></td>
                                        <td><?php echo $month['items_borrowed']; ?></td>
                                        <td><?php echo $month['items_returned']; ?></td>
                                        <td>
                                            <span
                                                style="color: <?php echo $month['overdue'] > 0 ? '#dc3545' : '#28a745'; ?>;">
                                                <?php echo $month['overdue']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Items Report Tab -->
            <div class="tab-pane fade" id="items">
                <div class="form-container">
                    <h3>Most Borrowed Items</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Borrow Count</th>
                                    <th>Available Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1;
                                foreach ($most_borrowed_items as $item_data): ?>
                                    <tr>
                                        <td><?php echo $rank++; ?></td>
                                        <td><?php echo $item_data['item_code']; ?></td>
                                        <td><?php echo $item_data['item_name']; ?></td>
                                        <td><?php echo $item_data['category']; ?></td>
                                        <td><strong><?php echo $item_data['borrow_count']; ?></strong></td>
                                        <td><?php echo $item_data['available_quantity'] . '/' . $item_data['total_quantity']; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-container">
                    <h3>Items by Category</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Total Items</th>
                                    <th>Available Items</th>
                                    <th>Borrowed Items</th>
                                    <th>Utilization Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $categories = [];
                                foreach ($most_borrowed_items as $item) {
                                    $cat = $item['category'];
                                    if (!isset($categories[$cat])) {
                                        $categories[$cat] = ['total' => 0, 'available' => 0, 'borrowed' => 0];
                                    }
                                    $categories[$cat]['total'] += $item['total_quantity'];
                                    $categories[$cat]['available'] += $item['available_quantity'];
                                    $categories[$cat]['borrowed'] += ($item['total_quantity'] - $item['available_quantity']);
                                }

                                foreach ($categories as $category => $data):
                                    $utilization = $data['total'] > 0 ? round((($data['total'] - $data['available']) / $data['total']) * 100, 1) : 0;
                                ?>
                                    <tr>
                                        <td><?php echo $category; ?></td>
                                        <td><?php echo $data['total']; ?></td>
                                        <td><?php echo $data['available']; ?></td>
                                        <td><?php echo $data['borrowed']; ?></td>
                                        <td>
                                            <div style="display: flex; align-items: center;">
                                                <div
                                                    style="flex: 1; background: #eee; height: 10px; margin-right: 10px; border-radius: 5px;">
                                                    <div
                                                        style="width: <?php echo $utilization; ?>%; background: #667eea; height: 100%; border-radius: 5px;">
                                                    </div>
                                                </div>
                                                <?php echo $utilization; ?>%
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            

            <!-- Overdue Items Tab -->
            <div class="tab-pane fade" id="overdue">
                <div class="form-container">
                    <h3>Overdue Items Report</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Borrower</th>
                                    <th>Item</th>
                                    <th>Date Borrowed</th>
                                    <th>Due Date</th>
                                    <th>Days Overdue</th>
                                    <th>Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overdue_items as $item): ?>
                                    <tr>
                                        <td><?php echo $item['transaction_id']; ?></td>
                                        <td><?php echo $item['borrower_name']; ?></td>
                                        <td><?php echo $item['item_name']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($item['date_issued'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($item['date_of_return'])); ?></td>
                                        <td>
                                            <span style="color: #dc3545; font-weight: bold;">
                                                <?php echo $item['days_overdue']; ?> days
                                            </span>
                                        </td>
                                        <td><?php echo $item['contact_number']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportReport(type) {
            const data = {
                type: type,
                date: new Date().toISOString().split('T')[0]
            };

            // Create a form to submit the export request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export.php';

            Object.keys(data).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = data[key];
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        function printReport() {
            window.print();
        }

        // Tab functionality
        const tabLinks = document.querySelectorAll('.nav-link');

        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Remove active class from all tabs
                document.querySelectorAll('.nav-link').forEach(tab => {
                    tab.classList.remove('active');
                });

                // Add active class to clicked tab
                this.classList.add('active');

                // Hide all tab panes
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });

                // Show target tab pane
                const targetId = this.getAttribute('href');
                const targetPane = document.querySelector(targetId);
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }
            });
        });
    </script>
</body>

</html>