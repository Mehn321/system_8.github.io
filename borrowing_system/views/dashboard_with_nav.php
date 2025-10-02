<?php
require_once '../includes/config.php';
require_once '../models/Admin.php';
require_once '../models/Transaction.php';
require_once '../models/Item.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin = new Admin();
$transaction = new Transaction();
$item = new Item();

// Get dashboard statistics
$stats = $transaction->getDashboardStats();

// Get recent transactions
$recent_transactions = $transaction->getAll([], 5);

// Get low stock items
$low_stock_items = $item->getLowStockItems(5);

// Get most borrowed items (removed)
// $most_borrowed = $item->getMostBorrowed(5);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <!-- Include Navigation -->
    <?php include 'navigation_fixed.php'; ?>

    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <h1><?php echo $system_name; ?> Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="form-container">
            <h3>Quick Navigation</h3>
            <div class="form-row">
                <a href="borrowers.php" class="btn btn-primary">Manage Borrowers</a>
                <a href="items.php" class="btn btn-primary">Manage Inventory</a>
                <a href="transactions.php" class="btn btn-primary">Manage Transactions</a>
                <a href="reports.php" class="btn btn-secondary">View Reports</a>
                <a href="settings.php" class="btn btn-secondary">System Settings</a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Today's Borrowed</h3>
                <div class="stat-number"><?php echo $stats['today_borrowed']; ?></div>
                <div class="stat-label">Items borrowed today</div>
            </div>

            <div class="stat-card">
                <h3>This Week</h3>
                <div class="stat-number"><?php echo $stats['week_borrowed']; ?></div>
                <div class="stat-label">Items borrowed this week</div>
            </div>

            <div class="stat-card">
                <h3>This Month</h3>
                <div class="stat-number"><?php echo $stats['month_borrowed']; ?></div>
                <div class="stat-label">Items borrowed this month</div>
            </div>

            <div class="stat-card">
                <h3>Overdue Items</h3>
                <div class="stat-number" style="color: #dc3545;"><?php echo $stats['overdue']; ?></div>
                <div class="stat-label">Items past due date</div>
            </div>

            <div class="stat-card">
                <h3>Due Soon</h3>
                <div class="stat-number" style="color: #ffc107;"><?php echo $stats['due_soon']; ?></div>
                <div class="stat-label">Items due within 3 days</div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="nav-tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" href="#transactions" role="tab" data-bs-toggle="tab">Recent
                        Transactions</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="#low-stock" role="tab" data-bs-toggle="tab">Low Stock Alert</a>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Recent Transactions -->
            <div class="tab-pane fade show active" id="transactions">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Borrower</th>
                                <th>Activity</th>
                                <th>Date Needed</th>
                                <th>Return Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_transactions as $trans): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $trans['full_name']; ?></strong><br>
                                        <small><?php echo $trans['id_number']; ?></small>
                                    </td>
                                    <td><?php echo $trans['activity_purpose']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($trans['date_needed'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($trans['date_of_return'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $trans['status']; ?>">
                                            <?php echo ucfirst($trans['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="transaction_details.php?id=<?php echo $trans['transaction_id']; ?>"
                                            class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Low Stock Items -->
            <div class="tab-pane fade" id="low-stock">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Available</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($low_stock_items as $item_data): ?>
                                <tr>
                                    <td><?php echo $item_data['item_code']; ?></td>
                                    <td><?php echo $item_data['item_name']; ?></td>
                                    <td><?php echo $item_data['category']; ?></td>
                                    <td>
                                        <span
                                            style="color: <?php echo $item_data['available_quantity'] == 0 ? '#dc3545' : '#ffc107'; ?>;">
                                            <?php echo $item_data['available_quantity']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $item_data['total_quantity']; ?></td>
                                    <td>
                                        <?php if ($item_data['available_quantity'] == 0): ?>
                                            <span class="status-badge" style="background: #dc3545; color: white;">Out of
                                                Stock</span>
                                        <?php else: ?>
                                            <span class="status-badge" style="background: #ffc107; color: #212529;">Low
                                                Stock</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
        </div>
    </div>
</body>

</html>