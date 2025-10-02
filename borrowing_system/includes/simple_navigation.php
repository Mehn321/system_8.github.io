<!-- Simple Navigation Menu -->
<div class="simple-navigation">
    <div class="nav-header">
        <button class="nav-toggle-btn" onclick="toggleNav()">☰
            <?php echo $system_name ?? 'Borrowing System'; ?></button>
    </div>

    <div class="nav-menu" id="navMenu">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <ul class="nav-list">
                <li><a href="dashboard.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">📊
                        Dashboard</a></li>
                <li><a href="transactions.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>">🔄
                        Transactions</a></li>
            </ul>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <ul class="nav-list">
                <li><a href="borrowers.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'borrowers.php' ? 'active' : ''; ?>">👥
                        Borrowers</a></li>
                <li><a href="items.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'items.php' ? 'active' : ''; ?>">📦
                        Inventory</a></li>
            </ul>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Reports</div>
            <ul class="nav-list">
                <li><a href="reports.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">📈
                        Reports</a></li>
                <li><a href="settings.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">⚙️
                        Settings</a></li>
            </ul>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Account</div>
            <ul class="nav-list">
                <li><a href="dashboard.php?logout=1" class="nav-link"
                        onclick="return confirm('Are you sure you want to logout?')">🚪 Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<style>
    .simple-navigation {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background: #667eea;
        color: white;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .nav-header {
        padding: 10px 20px;
    }

    .nav-toggle-btn {
        background: none;
        border: none;
        color: white;
        font-size: 16px;
        cursor: pointer;
        padding: 10px;
        border-radius: 5px;
    }

    .nav-toggle-btn:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .nav-menu {
        display: none;
        background: white;
        color: #333;
        border-top: 1px solid #ddd;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .nav-menu.open {
        display: block;
    }

    .nav-section {
        padding: 20px;
        border-bottom: 1px solid #eee;
    }

    .nav-section-title {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #666;
        margin-bottom: 10px;
    }

    .nav-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .nav-list li {
        margin: 0;
    }

    .nav-link {
        display: block;
        padding: 10px 0;
        color: #333;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .nav-link:hover {
        color: #667eea;
    }

    .nav-link.active {
        color: #667eea;
        font-weight: 600;
    }

    @media (min-width: 768px) {
        .simple-navigation {
            width: 250px;
            height: 100vh;
        }

        .nav-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-toggle-btn {
            display: none;
        }

        .nav-menu {
            display: block;
            background: none;
            color: white;
            border: none;
            box-shadow: none;
        }

        .nav-section {
            padding: 20px 0;
            border: none;
        }

        .nav-section-title {
            color: rgba(255, 255, 255, 0.7);
        }

        .nav-link {
            color: white;
            padding: 12px 20px;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            border-left-color: white;
            color: white;
        }
    }
</style>

<script>
    function toggleNav() {
        const navMenu = document.getElementById('navMenu');
        navMenu.classList.toggle('open');
    }
</script>