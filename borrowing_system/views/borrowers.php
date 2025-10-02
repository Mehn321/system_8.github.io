<?php
require_once '../includes/config.php';
require_once '../models/Borrower.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$borrower = new Borrower();
$message = '';
$message_type = '';

if (isset($_POST['add_borrower'])) {
    $data = [
        'id_number' => $_POST['id_number'],
        'full_name' => $_POST['full_name'],
        'department_course_office' => $_POST['department_course_office'],
        'contact_number' => $_POST['contact_number'],
        'email_address' => $_POST['email_address']
    ];

    if ($borrower->create($data)) {
        $message = 'Borrower added successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error adding borrower.';
        $message_type = 'error';
    }
}

if (isset($_POST['delete_borrower'])) {
    $borrower_id = $_POST['borrower_id'];

    if ($borrower->delete($borrower_id)) {
        $message = 'Borrower deleted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error deleting borrower.';
        $message_type = 'error';
    }
}

// Get all borrowers
$search = isset($_GET['search']) ? $_GET['search'] : '';
$borrowers_list = $borrower->getAll($search);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - Borrowers</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <h1>Borrowers Management</h1>
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

        <!-- Add New Borrower Form -->
        <div class="form-container">
            <h3>Add New Borrower</h3>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="id_number">ID Number</label>
                        <input type="text" id="id_number" name="id_number" required>
                    </div>
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="department_course_office">Department/Course/Office</label>
                        <input type="text" id="department_course_office" name="department_course_office" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email_address">Email Address</label>
                        <input type="email" id="email_address" name="email_address" required>
                    </div>
                </div>
                <button type="submit" name="add_borrower" class="btn btn-primary">Add Borrower</button>
            </form>
        </div>

        <!-- Search -->
        <div class="form-container">
            <form method="GET" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search">Search Borrowers</label>
                        <input type="text" id="search" name="search" placeholder="Search by name, ID, or department..."
                            value="<?php echo $search; ?>">
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Borrowers List -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Full Name</th>
                        <th>Department/Office</th>
                        <th>Contact Number</th>
                        <th>Email Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($borrowers_list as $borrower_data): ?>
                    <tr>
                        <td><?php echo $borrower_data['id_number']; ?></td>
                        <td><?php echo $borrower_data['full_name']; ?></td>
                        <td><?php echo $borrower_data['department_course_office']; ?></td>
                        <td><?php echo $borrower_data['contact_number']; ?></td>
                        <td><?php echo $borrower_data['email_address']; ?></td>
                        <td>
                            <a href="borrower_details.php?id=<?php echo $borrower_data['borrower_id']; ?>"
                                class="btn btn-sm btn-primary">View</a>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="borrower_id"
                                    value="<?php echo $borrower_data['borrower_id']; ?>">
                                <button type="submit" name="delete_borrower" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this borrower?')">Delete</button>
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