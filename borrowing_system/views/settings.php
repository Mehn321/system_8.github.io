<?php
require_once '../includes/config.php';
require_once '../models/Admin.php';
require_once '../models/Category.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin = new Admin();
$categoryModel = new Category();
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

if (isset($_POST['add_category'])) {
    $category_name = trim($_POST['new_category_name']);
    if (!empty($category_name)) {
        if ($categoryModel->create($category_name)) {
            $message = 'Category added successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error adding category.';
            $message_type = 'error';
        }
    } else {
        $message = 'Category name cannot be empty.';
        $message_type = 'error';
    }
}

if (isset($_POST['edit_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = trim($_POST['edit_category_name']);
    if (!empty($category_name)) {
        if ($categoryModel->update($category_id, $category_name)) {
            $message = 'Category updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error updating category.';
            $message_type = 'error';
        }
    } else {
        $message = 'Category name cannot be empty.';
        $message_type = 'error';
    }
}

if (isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'];
    if ($categoryModel->delete($category_id)) {
        $message = 'Category deleted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error deleting category.';
        $message_type = 'error';
    }
}

// Get current settings
$system_settings = $admin->getSystemSettings();
$activity_logs = $admin->getActivityLogs(20);
$categories = $categoryModel->getAll();
?>
<?php include '../includes/navigation.php'; ?>

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

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Settings Tabs -->

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

            <!-- Category Management Tab -->
            <div class="tab-pane fade" id="categories">
                <div class="form-container">
                    <h3>Manage Categories</h3>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_category_name">Add New Category</label>
                                <input type="text" id="new_category_name" name="new_category_name"
                                    placeholder="Category Name" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                            </div>
                        </div>
                    </form>

                    <h4>Existing Categories</h4>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                                    <td>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="category_id"
                                                value="<?php echo $cat['category_id']; ?>">
                                            <input type="text" name="edit_category_name"
                                                value="<?php echo htmlspecialchars($cat['category_name']); ?>" required>
                                            <button type="submit" name="edit_category"
                                                class="btn btn-sm btn-primary">Update</button>
                                        </form>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="category_id"
                                                value="<?php echo $cat['category_id']; ?>">
                                            <button type="submit" name="delete_category" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Tab functionality
        const tabLinks = document.querySelectorAll('.nav-tabs .nav-link');

        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Remove active class from all tabs
                document.querySelectorAll('.nav-tabs .nav-link').forEach(tab => {
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
        <script src="../assets/js/script.js"></script>

</body>

</html>