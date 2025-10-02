<?php
require_once '../includes/config.php';
require_once '../models/Item.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$item = new Item();
$message = '';
$message_type = '';

if (isset($_POST['add_item'])) {
    $data = [
        'item_code' => $_POST['item_code'],
        'item_name' => $_POST['item_name'],
        'item_description' => $_POST['item_description'],
        'category' => $_POST['category'],
        'total_quantity' => $_POST['total_quantity'],
        'unit' => $_POST['unit']
    ];

    if ($item->create($data)) {
        $message = 'Item added successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error adding item.';
        $message_type = 'error';
    }
}

if (isset($_POST['delete_item'])) {
    $item_id = $_POST['item_id'];

    if ($item->delete($item_id)) {
        $message = 'Item deleted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error deleting item. Item may be currently borrowed.';
        $message_type = 'error';
    }
}

// Get all items
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$items_list = $item->getAll($search, $category);
$categories = $item->getCategories();
$low_stock_items = $item->getLowStockItems();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - Inventory Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <h1>Inventory Management</h1>
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

        <!-- Add New Item Form -->
        <div class="form-container">
            <h3>Add New Item</h3>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="item_code">Item Code</label>
                        <input type="text" id="item_code" name="item_code" required>
                    </div>
                    <div class="form-group">
                        <label for="item_name">Item Name</label>
                        <input type="text" id="item_name" name="item_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Supplies">Supplies</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Books">Books</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="total_quantity">Total Quantity</label>
                        <input type="number" id="total_quantity" name="total_quantity" min="1" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="unit">Unit</label>
                        <input type="text" id="unit" name="unit" placeholder="pieces, sets, boxes..." required>
                    </div>
                    <div class="form-group">
                        <label for="item_description">Description</label>
                        <textarea id="item_description" name="item_description" rows="3"></textarea>
                    </div>
                </div>
                <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
            </form>
        </div>

        <!-- Search and Filter -->
        <div class="form-container">
            <form method="GET" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search">Search Items</label>
                        <input type="text" id="search" name="search"
                            placeholder="Search by code, name, or description..." value="<?php echo $search; ?>">
                    </div>
                    <div class="form-group">
                        <label for="category_filter">Filter by Category</label>
                        <select id="category_filter" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
                                    <?php echo $cat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Low Stock Alert -->
        <?php if (!empty($low_stock_items)): ?>
            <div class="form-container">
                <h3>⚠️ Low Stock Alert</h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
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
                                            <span class="status-badge" style="background: #ffc107; color: #212529;">Low Stock</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Items List -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Available</th>
                        <th>Total</th>
                        <th>Unit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items_list as $item_data): ?>
                        <tr>
                            <td><?php echo $item_data['item_code']; ?></td>
                            <td><?php echo $item_data['item_name']; ?></td>
                            <td><?php echo $item_data['category']; ?></td>
                            <td>
                                <span
                                    style="color: <?php echo $item_data['available_quantity'] == 0 ? '#dc3545' : '#28a745'; ?>;">
                                    <?php echo $item_data['available_quantity']; ?>
                                </span>
                            </td>
                            <td><?php echo $item_data['total_quantity']; ?></td>
                            <td><?php echo $item_data['unit']; ?></td>
                            <td>
                                <a href="item_details.php?id=<?php echo $item_data['item_id']; ?>"
                                    class="btn btn-sm btn-primary">View</a>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="item_id" value="<?php echo $item_data['item_id']; ?>">
                                    <button type="submit" name="delete_item" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this item?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>