<?php
require_once '../includes/config.php';
require_once '../models/Admin.php';

$admin = new Admin();
$error = '';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $admin_data = $admin->login($username, $password);

        if ($admin_data) {
            $_SESSION['admin_id'] = $admin_data['admin_id'];
            $_SESSION['admin_name'] = $admin_data['full_name'];
            $_SESSION['login_time'] = time();

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <h2><?php echo $system_name; ?></h2>
            <p class="login-subtitle">Please sign in to continue</p>

            <?php if ($error): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" name="login" class="btn btn-primary">
                    Sign In
                </button>
            </form>

            <div class="login-footer">
                <p>Version <?php echo $system_version; ?></p>
            </div>
        </div>
    </div>
</body>

</html>