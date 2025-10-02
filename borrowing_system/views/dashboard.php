<?php
require_once '../includes/config.php';
require_once '../models/Admin.php';
require_once '../models/Transaction.php';
require_once '../models/Item.php';
require_once '../models/Borrower.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}


$admin = new Admin();
$transaction = new Transaction();
$item = new Item();
$borrower = new Borrower();

// Get dashboard statistics
$stats = $transaction->getDashboardStats();
$most_borrowed = $item->getMostBorrowed(5);
$low_stock = $item->getLowStockItems();
$recent_transactions = $transaction->getAll([], 5, 0);

// Get most borrowed items (removed)
// $most_borrowed = $item->getMostBorrowed(5);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<?php include '../includes/navigation.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container fade-in">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card clickable" data-type="total_transactions">
                <div class="stat-header">
                    <h3 class="stat-title">Total Transactions</h3>
                    <div class="stat-icon"><i class="fas fa-list"></i></div>
                </div>
                <div class="stat-number"><a href="transactions.php?status=returned"
                        style="color: inherit; text-decoration: none;"><?php echo $transaction->getCount(); ?></a></div>
                <div class="stat-label">All transactions (click to view returned)</div>
            </div>

            <div class="stat-card clickable" data-type="today_borrowed">
                <div class="stat-header">
                    <h3 class="stat-title">Today's Borrowed</h3>
                    <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                </div>
                <div class="stat-number"><?php echo $stats['today_borrowed']; ?></div>
                <div class="stat-label">Items borrowed today</div>
            </div>

            <div class="stat-card clickable" data-type="week_borrowed">
                <div class="stat-header">
                    <h3 class="stat-title">This Week</h3>
                    <div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
                </div>
                <div class="stat-number"><?php echo $stats['week_borrowed']; ?></div>
                <div class="stat-label">Items borrowed this week</div>
            </div>

            <div class="stat-card clickable" data-type="month_borrowed">
                <div class="stat-header">
                    <h3 class="stat-title">This Month</h3>
                    <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                </div>
                <div class="stat-number"><?php echo $stats['month_borrowed']; ?></div>
                <div class="stat-label">Items borrowed this month</div>
            </div>

            <div class="stat-card clickable" data-type="overdue">
                <div class="stat-header">
                    <h3 class="stat-title">Overdue Items</h3>
                    <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
                <div class="stat-number" style="color: var(--danger-color);"><?php echo $stats['overdue']; ?></div>
                <div class="stat-label">Items past due date</div>
            </div>

            <div class="stat-card clickable" data-type="due_soon">
                <div class="stat-header">
                    <h3 class="stat-title">Due Soon</h3>
                    <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                </div>
                <div class="stat-number" style="color: var(--warning-color);"><?php echo $stats['due_soon']; ?></div>
                <div class="stat-label">Items due within 3 days</div>
            </div>

            <div class="stat-card clickable" data-type="total_borrowers">
                <div class="stat-header">
                    <h3 class="stat-title">Total Borrowers</h3>
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                </div>
                <div class="stat-number"><a href="borrowers.php"
                        style="color: inherit; text-decoration: none;"><?php echo $borrower->getCount(); ?></a></div>
                <div class="stat-label">Registered borrowers (click to view)</div>
            </div>

            <div class="stat-card clickable" data-type="total_items">
                <div class="stat-header">
                    <h3 class="stat-title">Total Items</h3>
                    <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                </div>
                <div class="stat-number"><a href="items.php"
                        style="color: inherit; text-decoration: none;"><?php echo $item->getCount(); ?></a></div>
                <div class="stat-label">Inventory items (click to view)</div>
            </div>

            <div class="stat-card clickable" data-type="low_stock">
                <div class="stat-header">
                    <h3 class="stat-title">Low Stock Items</h3>
                    <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
                </div>
                <div class="stat-number" style="color: var(--warning-color);"><?php echo count($low_stock); ?></div>
                <div class="stat-label">Items with low stock (≤5)</div>
            </div>
        </div>

        <!-- Dashboard Sections -->
        <div class="dashboard-sections">
            <!-- Most Borrowed Items -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">Most Borrowed Items</h3>
                    <a href="items.php" class="btn btn-secondary btn-sm">View All</a>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Code</th>
                                <th>Borrow Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($most_borrowed)): ?>
                                <?php foreach ($most_borrowed as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['item_code']); ?></td>
                                        <td><?php echo $item['borrow_count']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">Recent Transactions</h3>
                    <a href="transactions.php" class="btn btn-secondary btn-sm">View All</a>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Borrower</th>
                                <th>Activity</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_transactions)): ?>
                                <?php foreach ($recent_transactions as $trans): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($trans['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($trans['activity_purpose']); ?></td>
                                        <td><span
                                                class="status-badge status-<?php echo $trans['status']; ?>"><?php echo ucfirst($trans['status']); ?></span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($trans['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No recent transactions</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">Quick Actions</h3>
                </div>
                <div class="quick-actions">
                    <a href="borrowers.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add Borrower
                    </a>
                    <a href="items.php" class="btn btn-primary">
                        <i class="fas fa-box-open"></i> Add Item
                    </a>
                    <a href="transactions.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> New Transaction
                    </a>
                    <a href="reports.php" class="btn btn-secondary">
                        <i class="fas fa-chart-bar"></i> View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Stat Details -->
    <div id="statModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeStatModal()">&times;</span>
            <h3 id="modalTitle"></h3>
            <div id="modalContent"></div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>

</html>