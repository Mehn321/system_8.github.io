<?php
require_once '../includes/config.php';
require_once '../models/Transaction.php';
require_once '../models/Borrower.php';
require_once '../models/Item.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$transaction = new Transaction();
$borrower = new Borrower();
$item = new Item();
$message = '';
$message_type = '';

// Handle form submissions
if (isset($_POST['create_transaction'])) {
    $data = [
        'borrower_id' => $_POST['borrower_id'],
        'activity_purpose' => $_POST['activity_purpose'],
        'place_of_activity' => $_POST['place_of_activity'],
        'date_requested' => $_POST['date_requested'],
        'date_needed' => $_POST['date_needed'],
        'date_of_return' => $_POST['date_of_return']
    ];

    $transaction_id = $transaction->create($data);

    if ($transaction_id) {
        // Add items to transaction
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item_id => $quantity) {
                if ($quantity > 0) {
                    $result = $transaction->addItem($transaction_id, $item_id, $quantity);
                    if (!$result) {
                        $message = "Error adding item ID $item_id to transaction.";
                        $message_type = 'error';
                        break;
                    }
                }
            }
        }

        if (empty($message)) {
            $message = 'Transaction created successfully!';
            $message_type = 'success';
        }
    } else {
        $message = 'Error creating transaction.';
        $message_type = 'error';
    }
}

if (isset($_POST['approve_transaction'])) {
    $transaction_id = $_POST['transaction_id'];

    if ($transaction->approve($transaction_id, $_SESSION['admin_id'])) {
        $message = 'Transaction approved successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error approving transaction.';
        $message_type = 'error';
    }
}

if (isset($_POST['issue_items'])) {
    $transaction_id = $_POST['transaction_id'];

    if ($transaction->issueItems($transaction_id, $_SESSION['admin_id'])) {
        $message = 'Items issued successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error issuing items. Insufficient stock.';
        $message_type = 'error';
    }
}

if (isset($_POST['return_items'])) {
    $transaction_id = $_POST['transaction_id'];

    if ($transaction->returnItems($transaction_id, $_POST['quantities_returned'] ?? [], $_SESSION['admin_id'])) {
        $message = 'Items returned successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error returning items.';
        $message_type = 'error';
    }
}

// Get all transactions
$filters = [];
if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
if (isset($_GET['status'])) {
    $filters['status'] = $_GET['status'];
    // Include returned transactions if specifically requested
    if ($_GET['status'] == 'returned') {
        $filters['include_returned'] = true;
    }
}
if (isset($_GET['department'])) $filters['department'] = $_GET['department'];
if (isset($_GET['date_from']) && isset($_GET['date_to'])) {
    $filters['date_from'] = $_GET['date_from'];
    $filters['date_to'] = $_GET['date_to'];
}

$transactions_list = $transaction->getAll($filters);
$borrowers_list = $borrower->getAll();
$items_list = $item->getAll();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - Transaction Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="dashboard-container">

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="form-container">
            <h3>Quick Actions</h3>
            <div class="form-row">
                <button type="button" class="btn btn-primary" onclick="showCreateForm()">Create New Transaction</button>
                <button type="button" class="btn btn-secondary" onclick="showFilterForm()">Filter Transactions</button>
            </div>
        </div>

        <!-- Create Transaction Form (Hidden by default) -->
        <div id="createForm" class="form-container" style="display: none;">
            <h3>Create New Transaction</h3>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="borrower_id">Borrower</label>
                        <select id="borrower_id" name="borrower_id" required>
                            <option value="">Select Borrower</option>
                            <?php foreach ($borrowers_list as $borrower_data): ?>
                            <option value="<?php echo $borrower_data['borrower_id']; ?>">
                                <?php echo $borrower_data['full_name'] . ' (' . $borrower_data['id_number'] . ')'; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="activity_purpose">Activity Purpose</label>
                        <input type="text" id="activity_purpose" name="activity_purpose" required>
                    </div>
                    <div class="form-group">
                        <label for="place_of_activity">Place of Activity</label>
                        <input type="text" id="place_of_activity" name="place_of_activity" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_requested">Date Requested</label>
                        <input type="date" id="date_requested" name="date_requested" required>
                    </div>
                    <div class="form-group">
                        <label for="date_needed">Date Needed</label>
                        <input type="date" id="date_needed" name="date_needed" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_of_return">Date of Return</label>
                        <input type="date" id="date_of_return" name="date_of_return" required>
                    </div>
                </div>

                <!-- Items Selection -->
                <h4>Select Items to Borrow</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="item_select">Item</label>
                        <select id="item_select" onchange="addItemToList()">
                            <option value="">Select Item</option>
                            <?php foreach ($items_list as $item_data): ?>
                            <option value="<?php echo $item_data['item_id']; ?>"
                                data-name="<?php echo $item_data['item_name']; ?>"
                                data-available="<?php echo $item_data['available_quantity']; ?>">
                                <?php echo $item_data['item_name'] . ' (' . $item_data['available_quantity'] . ' available)'; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="selectedItems">
                    <!-- Selected items will be added here -->
                </div>

                <button type="submit" name="create_transaction" class="btn btn-primary">Create Transaction</button>
                <button type="button" class="btn btn-secondary" onclick="hideCreateForm()">Cancel</button>
            </form>
        </div>

        <!-- Filter Form (Hidden by default) -->
        <div id="filterForm" class="form-container" style="display: none;">
            <h3>Filter Transactions</h3>
            <form method="GET" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" placeholder="Search transactions...">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="issued">Issued</option>
                            <option value="returned">Returned</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" placeholder="Filter by department...">
                    </div>
                    <div class="form-group">
                        <label for="date_from">Date From</label>
                        <input type="date" id="date_from" name="date_from">
                    </div>
                    <div class="form-group">
                        <label for="date_to">Date To</label>
                        <input type="date" id="date_to" name="date_to">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <button type="button" class="btn btn-secondary" onclick="hideFilterForm()">Clear Filters</button>
            </form>
        </div>

        <!-- Transactions List -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Borrower</th>
                        <th>Activity</th>
                        <th>Place of Activity</th>
                        <th>Date Needed</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Items Borrowed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions_list as $trans): ?>
                    <tr>
                        <td><?php echo $trans['transaction_id']; ?></td>
                        <td>
                            <strong><?php echo $trans['full_name']; ?></strong><br>
                            <small><?php echo $trans['id_number']; ?></small>
                        </td>
                        <td><?php echo $trans['activity_purpose']; ?></td>
                        <td><?php echo $trans['place_of_activity']; ?></td>
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
                                        $quantity = ($trans['status'] == 'pending' || $trans['status'] == 'approved') ? $item['quantity_required'] : $item['quantity_issued'];
                                        echo htmlspecialchars($item['item_name']) . ' (' . intval($quantity) . ')<br>';
                                    }
                                } else {
                                    echo 'No items borrowed';
                                }
                                ?>
                        </td>
                        <td>
                            <?php if ($trans['status'] == 'pending'): ?>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="transaction_id"
                                    value="<?php echo $trans['transaction_id']; ?>">
                                <button type="submit" name="approve_transaction"
                                    class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <?php endif; ?>

                            <?php if ($trans['status'] == 'approved'): ?>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="transaction_id"
                                    value="<?php echo $trans['transaction_id']; ?>">
                                <button type="submit" name="issue_items" class="btn btn-sm btn-warning">Issue
                                    Items</button>
                            </form>
                            <?php endif; ?>

                            <?php if ($trans['status'] == 'issued'): ?>
                            <button type="button" class="btn btn-sm btn-info"
                                onclick="showReturnForm(<?php echo $trans['transaction_id']; ?>)">Return Items</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Return Items Modal -->
    <div id="returnModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div
            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 10px; max-width: 500px; width: 90%;">
            <h3>Return Items</h3>
            <form id="returnForm" method="POST" action="">
                <input type="hidden" name="transaction_id" id="returnTransactionId">
                <div id="returnItemsList"></div>
                <div style="margin-top: 20px;">
                    <button type="submit" name="return_items" class="btn btn-primary">Submit Return</button>
                    <button type="button" class="btn btn-secondary" onclick="hideReturnForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function showCreateForm() {
        document.getElementById('createForm').style.display = 'block';
        document.getElementById('filterForm').style.display = 'none';
    }

    function hideCreateForm() {
        document.getElementById('createForm').style.display = 'none';
    }

    function showFilterForm() {
        document.getElementById('filterForm').style.display = 'block';
        document.getElementById('createForm').style.display = 'none';
    }

    function hideFilterForm() {
        document.getElementById('filterForm').style.display = 'none';
        // Clear filters
        window.location.href = 'transactions.php';
    }

    function addItemToList() {
        const select = document.getElementById('item_select');
        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption.value) {
            const itemId = selectedOption.value;
            const itemName = selectedOption.getAttribute('data-name');
            const available = selectedOption.getAttribute('data-available');

            const container = document.getElementById('selectedItems');

            // Check if item already added
            const existingInputs = container.querySelectorAll('input[name^="items"]');
            for (let input of existingInputs) {
                if (input.name === `items[${itemId}]`) {
                    alert('This item is already added.');
                    select.selectedIndex = 0;
                    return;
                }
            }

            const itemDiv = document.createElement('div');
            itemDiv.innerHTML = `
                    <div style="margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        <strong>${itemName}</strong> (Available: ${available})<br>
                        <label>Quantity to borrow:
                            <input type="number" name="items[${itemId}]" min="1" max="${available}" value="1" style="margin-left: 10px;">
                        </label>
                        <button type="button" onclick="removeItem(this)" style="margin-left: 10px; background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px;">Remove</button>
                    </div>
                `;

            container.appendChild(itemDiv);
            select.selectedIndex = 0;
        }
    }

    function removeItem(button) {
        button.parentElement.remove();
    }
    </script>
    <script src="../assets/js/script.js"></script>
</body>

</html>