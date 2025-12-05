<?php
/**
 * SHARED MEMBER SIDEBAR
 * Include this in all member pages for consistency
 * 
 * Usage: include __DIR__ . '/../includes/member-sidebar.php';
 */

if (!isset($user)) {
    $user = getCurrentUser();
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    .member-sidebar {
        position: sticky;
        top: 120px;
        height: fit-content;
    }

    .sidebar-header {
        padding: 30px;
        background: var(--cream);
        margin-bottom: 24px;
        border-radius: 8px;
    }

    .sidebar-header h3 {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        margin-bottom: 8px;
    }

    .sidebar-header p {
        font-size: 14px;
        color: var(--grey);
    }

    .sidebar-nav {
        list-style: none;
    }

    .sidebar-nav li {
        margin-bottom: 8px;
    }

    .sidebar-nav a {
        display: block;
        padding: 14px 20px;
        color: var(--charcoal);
        text-decoration: none;
        transition: all 0.3s;
        border-radius: 4px;
        font-size: 14px;
    }

    .sidebar-nav a:hover,
    .sidebar-nav a.active {
        background: var(--cream);
        padding-left: 28px;
    }

    .logout-btn {
        margin-top: 24px;
        display: block;
        width: 100%;
        padding: 14px 20px;
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.15);
        color: #C41E3A;
        text-decoration: none;
        text-align: center;
        border-radius: 4px;
        font-size: 14px;
        transition: all 0.3s;
    }

    .logout-btn:hover {
        background: #C41E3A;
        color: var(--white);
    }

    @media (max-width: 968px) {
        .member-sidebar {
            position: static;
        }
    }
</style>

<aside class="member-sidebar">
    <div class="sidebar-header">
        <h3>Welcome back!</h3>
        <p><?php echo htmlspecialchars($user['name']); ?></p>
    </div>

    <ul class="sidebar-nav">
        <li>
            <a href="/member/dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                📊 Dashboard
            </a>
        </li>
        <li>
            <a href="/member/wallet.php" class="<?= $current_page === 'wallet.php' ? 'active' : '' ?>">
                💰 My Wallet
            </a>
        </li>
        <li>
            <a href="/member/orders.php" class="<?= $current_page === 'orders.php' ? 'active' : '' ?>">
                📦 My Orders
            </a>
        </li>
        <li>
            <a href="/member/referral.php" class="<?= $current_page === 'referral.php' ? 'active' : '' ?>">
                🎁 My Referrals
            </a>
        </li>
        <li>
            <a href="/member/address-book.php" class="<?= $current_page === 'address-book.php' ? 'active' : '' ?>">
                📍 Address Book
            </a>
        </li>
        <li>
            <a href="/member/vouchers/" class="<?= strpos($_SERVER['REQUEST_URI'], '/vouchers/') !== false ? 'active' : '' ?>">
                🎫 My Vouchers
            </a>
        </li>
        <li>
            <a href="/member/reviews.php" class="<?= $current_page === 'reviews.php' ? 'active' : '' ?>">
                ⭐ My Reviews
            </a>
        </li>
        <li>
            <a href="/member/profile.php" class="<?= $current_page === 'profile.php' ? 'active' : '' ?>">
                👤 Edit Profile
            </a>
        </li>
        <li>
            <a href="/member/password.php" class="<?= $current_page === 'password.php' ? 'active' : '' ?>">
                🔒 Change Password
            </a>
        </li>
    </ul>

    <a href="/auth/logout.php" class="logout-btn">Logout</a>
</aside>
