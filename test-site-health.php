<?php
/**
 * SITE HEALTH CHECK SCRIPT
 * Run this file to check if all components are working properly
 * Access: http://dorve.id/test-site-health.php
 */

// Disable error display for security (will log instead)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$results = [];
$allGood = true;

// Test 1: Config File
try {
    require_once __DIR__ . '/config.php';
    $results['config'] = ['status' => 'OK', 'message' => 'Config loaded successfully'];
} catch (Exception $e) {
    $results['config'] = ['status' => 'ERROR', 'message' => $e->getMessage()];
    $allGood = false;
}

// Test 2: Database Connection
try {
    if (isset($pdo)) {
        $pdo->query("SELECT 1");
        $results['database'] = ['status' => 'OK', 'message' => 'Database connection successful'];
    } else {
        throw new Exception('PDO not initialized');
    }
} catch (Exception $e) {
    $results['database'] = ['status' => 'ERROR', 'message' => $e->getMessage()];
    $allGood = false;
}

// Test 3: Essential Tables
$requiredTables = [
    'users', 'products', 'categories', 'orders', 'cart_items',
    'vouchers', 'user_vouchers', 'product_reviews', 'user_addresses',
    'banners', 'payment_methods', 'bank_accounts'
];

$missingTables = [];
foreach ($requiredTables as $table) {
    try {
        $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
    } catch (Exception $e) {
        $missingTables[] = $table;
    }
}

if (empty($missingTables)) {
    $results['tables'] = ['status' => 'OK', 'message' => 'All required tables exist'];
} else {
    $results['tables'] = ['status' => 'WARNING', 'message' => 'Missing tables: ' . implode(', ', $missingTables)];
}

// Test 4: Helper Functions
$requiredFunctions = [
    'formatPrice', 'isLoggedIn', 'isAdmin', 'getCartCount', 'getCanonicalUrl'
];

$missingFunctions = [];
foreach ($requiredFunctions as $func) {
    if (!function_exists($func)) {
        $missingFunctions[] = $func;
    }
}

if (empty($missingFunctions)) {
    $results['functions'] = ['status' => 'OK', 'message' => 'All helper functions loaded'];
} else {
    $results['functions'] = ['status' => 'ERROR', 'message' => 'Missing functions: ' . implode(', ', $missingFunctions)];
    $allGood = false;
}

// Test 5: File Permissions
$criticalDirs = [
    'uploads' => __DIR__ . '/uploads',
    'uploads/products' => __DIR__ . '/uploads/products',
    'uploads/vouchers' => __DIR__ . '/uploads/vouchers',
    'uploads/reviews' => __DIR__ . '/uploads/reviews'
];

$permissionIssues = [];
foreach ($criticalDirs as $name => $path) {
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }
    if (!is_writable($path)) {
        $permissionIssues[] = $name;
    }
}

if (empty($permissionIssues)) {
    $results['permissions'] = ['status' => 'OK', 'message' => 'Upload directories writable'];
} else {
    $results['permissions'] = ['status' => 'WARNING', 'message' => 'Not writable: ' . implode(', ', $permissionIssues)];
}

// Test 6: Integration Classes
$integrationClasses = [
    'MidtransHelper' => __DIR__ . '/includes/MidtransHelper.php',
    'BiteshipClient' => __DIR__ . '/includes/BiteshipClient.php'
];

$missingClasses = [];
foreach ($integrationClasses as $className => $filePath) {
    if (!file_exists($filePath)) {
        $missingClasses[] = $className;
    }
}

if (empty($missingClasses)) {
    $results['integrations'] = ['status' => 'OK', 'message' => 'Integration classes present'];
} else {
    $results['integrations'] = ['status' => 'WARNING', 'message' => 'Missing: ' . implode(', ', $missingClasses)];
}

// Test 7: Session
try {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $results['session'] = ['status' => 'OK', 'message' => 'Session working'];
    } else {
        throw new Exception('Session not started');
    }
} catch (Exception $e) {
    $results['session'] = ['status' => 'ERROR', 'message' => $e->getMessage()];
    $allGood = false;
}

// Test 8: PHP Version
$phpVersion = phpversion();
if (version_compare($phpVersion, '7.2.0', '>=')) {
    $results['php_version'] = ['status' => 'OK', 'message' => 'PHP ' . $phpVersion];
} else {
    $results['php_version'] = ['status' => 'WARNING', 'message' => 'PHP ' . $phpVersion . ' (Recommended: 7.4+)'];
}

// Test 9: Required PHP Extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring', 'gd'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (empty($missingExtensions)) {
    $results['php_extensions'] = ['status' => 'OK', 'message' => 'All required extensions loaded'];
} else {
    $results['php_extensions'] = ['status' => 'ERROR', 'message' => 'Missing: ' . implode(', ', $missingExtensions)];
    $allGood = false;
}

// Test 10: Check if header.php loads without error
ob_start();
try {
    $page_title = 'Test Page';
    $page_description = 'Test Description';
    include __DIR__ . '/includes/header.php';
    ob_end_clean();
    $results['header_include'] = ['status' => 'OK', 'message' => 'header.php loads without errors'];
} catch (Exception $e) {
    ob_end_clean();
    $results['header_include'] = ['status' => 'ERROR', 'message' => $e->getMessage()];
    $allGood = false;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Health Check - Dorve.id</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            font-size: 32px;
            margin-bottom: 8px;
            color: #1a1a1a;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 32px;
        }
        .overall-status {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 32px;
            font-size: 18px;
            font-weight: 600;
            text-align: center;
        }
        .overall-status.good {
            background: #d1fae5;
            color: #065f46;
        }
        .overall-status.bad {
            background: #fee2e2;
            color: #991b1b;
        }
        .test-item {
            padding: 20px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .test-item.ok {
            border-color: #10b981;
            background: #f0fdf4;
        }
        .test-item.warning {
            border-color: #f59e0b;
            background: #fffbeb;
        }
        .test-item.error {
            border-color: #ef4444;
            background: #fef2f2;
        }
        .status-icon {
            font-size: 32px;
            flex-shrink: 0;
        }
        .test-content {
            flex: 1;
        }
        .test-name {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }
        .test-message {
            font-size: 14px;
            color: #6b7280;
        }
        .test-status {
            font-size: 12px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .test-status.ok {
            background: #d1fae5;
            color: #065f46;
        }
        .test-status.warning {
            background: #fef3c7;
            color: #92400e;
        }
        .test-status.error {
            background: #fee2e2;
            color: #991b1b;
        }
        .footer {
            margin-top: 32px;
            padding-top: 32px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Site Health Check</h1>
        <p class="subtitle">Dorve.id System Diagnostics</p>
        
        <div class="overall-status <?php echo $allGood ? 'good' : 'bad'; ?>">
            <?php if ($allGood): ?>
                ✅ All Critical Systems Operational
            <?php else: ?>
                ⚠️ Some Issues Detected - Review Below
            <?php endif; ?>
        </div>

        <?php foreach ($results as $testName => $result): ?>
            <?php 
            $statusClass = strtolower($result['status']);
            $icon = $statusClass === 'ok' ? '✅' : ($statusClass === 'warning' ? '⚠️' : '❌');
            $displayName = ucwords(str_replace('_', ' ', $testName));
            ?>
            <div class="test-item <?php echo $statusClass; ?>">
                <div class="status-icon"><?php echo $icon; ?></div>
                <div class="test-content">
                    <div class="test-name"><?php echo $displayName; ?></div>
                    <div class="test-message"><?php echo htmlspecialchars($result['message']); ?></div>
                </div>
                <div class="test-status <?php echo $statusClass; ?>">
                    <?php echo $result['status']; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="footer">
            <p><strong>Note:</strong> If you see any errors above, please contact your developer.</p>
            <p style="margin-top: 8px;">Test completed at: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p style="margin-top: 16px; color: #ef4444; font-weight: 600;">⚠️ DELETE THIS FILE AFTER TESTING FOR SECURITY</p>
        </div>
    </div>
</body>
</html>
