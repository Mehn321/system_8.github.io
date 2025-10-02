<?php
require_once '../includes/config.php';
require_once '../models/Admin.php';
require_once '../models/Transaction.php';
// require_once '../models/Item.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}


$admin = new Admin();
$transaction = new Transaction();
// $item = new Item();

// Get dashboard statistics
$stats = $transaction->getDashboardStats();

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
</head>

<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <h1><?php echo $system_name; ?> Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                <a href="?logout=1" class="logout-btn">Logout</a>
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

        
    </div>

    <script src="../assets/js/script.js"></script>
</body>

</html>