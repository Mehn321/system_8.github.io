<!-- Navigation Menu -->
<div class="navigation-menu">
    <div class="nav-header">
        <h2><?php echo $system_name ?? 'Borrowing System'; ?></h2>
        <div class="nav-toggle" onclick="toggleNav()">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <div class="nav-body">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <ul class="nav-list">
                <li><a href="dashboard.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">📊</span> Dashboard</a></li>
                <li><a href="transactions.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">🔄</span> Transactions</a></li>
            </ul>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <ul class="nav-list">
                <li><a href="borrowers.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'borrowers.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">👥</span> Borrowers</a></li>
                <li><a href="items.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'items.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">📦</span> Inventory</a></li>
            </ul>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Reports</div>
            <ul class="nav-list">
                <li><a href="reports.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">📈</span> Reports</a></li>
                <li><a href="settings.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">⚙️</span> Settings</a></li>
            </ul>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Account</div>
            <ul class="nav-list">
                <li><a href="dashboard.php?logout=1" class="nav-link"
                        onclick="return confirm('Are you sure you want to logout?')">
                        <span class="nav-icon">🚪</span> Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Navigation Toggle Button -->
<button class="nav-toggle-btn" onclick="toggleNav()">☰</button>

<!-- Navigation Overlay -->
<div class="nav-overlay" onclick="toggleNav()"></div>

<style>
    .navigation-menu {
        position: fixed;
        left: -280px;
        top: 0;
        width: 280px;
        height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        transition: left 0.3s ease;
        z-index: 1000;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .navigation-menu.open {
        left: 0;
    }

    .nav-header {
        padding: 20px;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .nav-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .nav-toggle {
        display: flex;
        flex-direction: column;
        cursor: pointer;
        padding: 5px;
    }

    .nav-toggle span {
        width: 20px;
        height: 2px;
        background: white;
        margin: 2px 0;
        transition: 0.3s;
    }

    .nav-body {
        padding: 20px 0;
    }

    .nav-section {
        margin-bottom: 30px;
    }

    .nav-section-title {
        padding: 0 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.7;
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
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: white;
        text-decoration: none;
        transition: background 0.3s ease;
        border-left: 3px solid transparent;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
        border-left-color: rgba(255, 255, 255, 0.3);
    }

    .nav-link.active {
        background: rgba(255, 255, 255, 0.2);
        border-left-color: white;
    }

    .nav-icon {
        margin-right: 10px;
        font-size: 16px;
    }

    .nav-toggle-btn {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1001;
        background: #667eea;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        font-size: 16px;
    }

    .nav-toggle-btn:hover {
        background: #5a6fd8;
    }

    .nav-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        display: none;
    }

    .nav-overlay.active {
        display: block;
    }

    @media (max-width: 768px) {
        .navigation-menu {
            width: 100%;
            left: -100%;
        }

        .navigation-menu.open {
            left: 0;
        }
    }
</style>

<script>
    function toggleNav() {
        const nav = document.querySelector('.navigation-menu');
        const overlay = document.querySelector('.nav-overlay');
        const toggleBtn = document.querySelector('.nav-toggle-btn');

        nav.classList.toggle('open');
        overlay.classList.toggle('active');

        if (nav.classList.contains('open')) {
            toggleBtn.innerHTML = '✕';
            toggleBtn.style.left = '300px';
        } else {
            toggleBtn.innerHTML = '☰';
            toggleBtn.style.left = '20px';
        }
    }
</script>