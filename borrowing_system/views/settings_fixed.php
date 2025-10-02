<?php
require_once '../includes/config.php';
require_once '../models/Admin.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin = new Admin();
$message = '';
$message_type = '';

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = 'All password fields are required.';
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'New password and confirm password do not match.';
        $message_type = 'error';
    } elseif (strlen($new_password) < 8) {
        $message = 'New password must be at least 8 characters long.';
        $message_type = 'error';
    } else {
        // Verify current password
        $admin_data = $admin->login($_SESSION['admin_name'], $current_password);
        if ($admin_data) {
            if ($admin->changePassword($_SESSION['admin_id'], $new_password)) {
                $message = 'Password changed successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error changing password.';
                $message_type = 'error';
            }
        } else {
            $message = 'Current password is incorrect.';
            $message_type = 'error';
        }
    }
}

if (isset($_POST['update_settings'])) {
    $settings = [
        'max_items_per_transaction' => $_POST['max_items_per_transaction'],
        'max_borrowing_days' => $_POST['max_borrowing_days'],
        'overdue_notice_days' => $_POST['overdue_notice_days']
    ];

    foreach ($settings as $key => $value) {
        $admin->updateSystemSetting($key, $value);
    }

    $message = 'System settings updated successfully!';
    $message_type = 'success';
}

// Get current settings
$system_settings = $admin->getSystemSettings();
$activity_logs = $admin->getActivityLogs(20);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - System Settings</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <h1>System Settings</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <a href="dashboard.php?logout=1" class="logout-btn">Logout</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Settings Tabs -->
        <div class="nav-tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" href="#profile" role="tab" data-bs-toggle="tab">Profile Settings</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="#system" role="tab" data-bs-toggle="tab">System Settings</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="#logs" role="tab" data-bs-toggle="tab">Activity Logs</a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <!-- Profile Settings Tab -->
            <div class="tab-pane fade show active" id="profile">
                <div class="form-container">
                    <h3>Change Password</h3>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" minlength="8" required>
                                <small style="color: #666;">Password must be at least 8 characters long</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>

            <!-- System Settings Tab -->
            <div class="tab-pane fade" id="system">
                <div class="form-container">
                    <h3>System Configuration</h3>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="max_items_per_transaction">Max Items Per Transaction</label>
                                <input type="number" id="max_items_per_transaction" name="max_items_per_transaction"
                                    value="<?php echo $system_settings['max_items_per_transaction']['value'] ?? 5; ?>"
                                    min="1" max="20" required>
                                <small style="color: #666;">Maximum number of items a borrower can request per
                                    transaction</small>
                            </div>
                            <div class="form-group">
                                <label for="max_borrowing_days">Max Borrowing Days</label>
                                <input type="number" id="max_borrowing_days" name="max_borrowing_days"
                                    value="<?php echo $system_settings['max_borrowing_days']['value'] ?? 7; ?>" min="1"
                                    max="30" required>
                                <small style="color: #666;">Maximum number of days items can be borrowed</small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="overdue_notice_days">Overdue Notice Days</label>
                                <input type="number" id="overdue_notice_days" name="overdue_notice_days"
                                    value="<?php echo $system_settings['overdue_notice_days']['value'] ?? 3; ?>" min="1"
                                    max="10" required>
                                <small style="color: #666;">Number of days before due date to send notification</small>
                            </div>
                        </div>
                        <button type="submit" name="update_settings" class="btn btn-primary">Update Settings</button>
                    </form>
                </div>

                <div class="form-container">
                    <h3>System Information</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Setting</th>
                                    <th>Value</th>
                                    <th>Description</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($system_settings as $key => $setting): ?>
                                    <tr>
                                        <td><?php echo ucwords(str_replace('_', ' ', $key)); ?></td>
                                        <td><strong><?php echo $setting['value']; ?></strong></td>
                                        <td><?php echo $setting['description']; ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($setting['updated_at'] ?? 'now')); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Activity Logs Tab -->
            <div class="tab-pane fade" id="logs">
                <div class="form-container">
                    <h3>Recent Activity Logs</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Admin</th>
                                    <th>Action</th>
                                    <th>Table</th>
                                    <th>Record ID</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activity_logs as $log): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                                        <td><?php echo $log['admin_name']; ?></td>
                                        <td>
                                            <span class="status-badge" style="background: #28a745; color: white;">
                                                <?php echo strtoupper($log['action']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo ucwords(str_replace('_', ' ', $log['table_name'])); ?></td>
                                        <td><?php echo $log['record_id']; ?></td>
                                        <td>
                                            <?php if ($log['old_values']): ?>
                                                <small>Old: <?php echo substr($log['old_values'], 0, 50); ?>...</small><br>
                                            <?php endif; ?>
                                            <?php if ($log['new_values']): ?>
                                                <small>New: <?php echo substr($log['new_values'], 0, 50); ?>...</small>
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
    </div>
</body>

</html>