<!-- App Layout -->
<div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h1 class="sidebar-title"><?php echo $system_name ?? 'Borrowing System'; ?></h1>
            <p class="sidebar-subtitle">Inventory Management</p>
        </div>

        <nav class="nav-section">
            <h3 class="nav-section-title">Main</h3>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="dashboard.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="transactions.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>">
                        <i class="fas fa-exchange-alt"></i>
                        Transactions
                    </a>
                </li>
            </ul>
        </nav>

        <nav class="nav-section">
            <h3 class="nav-section-title">Management</h3>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="borrowers.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'borrowers.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        Borrowers
                    </a>
                </li>
                <li class="nav-item">
                    <a href="items.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'items.php' ? 'active' : ''; ?>">
                        <i class="fas fa-boxes"></i>
                        Inventory
                    </a>
                </li>

            </ul>
        </nav>

        <nav class="nav-section">
            <h3 class="nav-section-title">Reports</h3>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="reports.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i>
                        Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </li>
            </ul>
        </nav>


    </aside>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Top Header -->
        <header class="top-header">
            <button class="nav-toggle" onclick="toggleNav()">
                <i class="fas fa-bars"></i>
            </button>

            <h1 class="header-title">
                <?php
                $page = basename($_SERVER['PHP_SELF'], '.php');
                echo ucfirst($page);
                ?>
            </h1>

            <div class="user-menu">
                <span class="user-greeting">
                    <i class="fas fa-user"></i>
                    Welcome, <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>
                </span>
                <a href="dashboard.php?logout=1" class="btn btn-danger btn-sm"
                    onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </header>