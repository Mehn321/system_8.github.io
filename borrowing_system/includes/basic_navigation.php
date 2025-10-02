<!-- Basic Navigation Menu -->
<div class="basic-navigation">
    <div class="nav-header">
        <button class="nav-toggle-btn" onclick="toggleNav()">☰ Navigation</button>
    </div>

    <div class="nav-menu" id="navMenu">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <ul class="nav-list">
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="transactions.php" class="nav-link">Transactions</a></li>
            </ul>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <ul class="nav-list">
                <li><a href="borrowers.php" class="nav-link">Borrowers</a></li>
                <li><a href="items.php" class="nav-link">Inventory</a></li>
            </ul>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Reports</div>
            <ul class="nav-list">
                <li><a href="reports.php" class="nav-link">Reports</a></li>
                <li><a href="settings.php" class="nav-link">Settings</a></li>
            </ul>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Account</div>
            <ul class="nav-list">
                <li><a href="dashboard.php?logout=1" class="nav-link"
                        onclick="return confirm('Are you sure you want to logout?')">Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<style>
    .basic-navigation {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background: #333;
        color: white;
        z-index: 1000;
    }

    .nav-header {
        padding: 10px 20px;
    }

    .nav-toggle-btn {
        background: #667eea;
        border: none;
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
    }

    .nav-menu {
        display: none;
        background: white;
        color: #333;
        border-top: 1px solid #ddd;
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
        font-weight: bold;
        text-transform: uppercase;
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
    }

    .nav-link:hover {
        color: #667eea;
    }

    @media (min-width: 768px) {
        .basic-navigation {
            width: 250px;
            height: 100vh;
        }

        .nav-header {
            padding: 20px;
            border-bottom: 1px solid #555;
        }

        .nav-toggle-btn {
            display: none;
        }

        .nav-menu {
            display: block;
            background: none;
            color: white;
            border: none;
        }

        .nav-section {
            padding: 20px 0;
            border: none;
        }

        .nav-section-title {
            color: #ccc;
        }

        .nav-link {
            color: white;
            padding: 12px 20px;
        }

        .nav-link:hover {
            background: #555;
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