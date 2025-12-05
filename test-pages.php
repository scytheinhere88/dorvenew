<?php
/**
 * Test All Pages Script - Syntax Check Only
 * Checks PHP syntax without executing the files
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Disable auto-redirect
if (!defined('NO_REDIRECT')) {
    define('NO_REDIRECT', true);
}

$pages_to_test = [
    // Public Pages
    'Homepage' => '/index.php',
    'All Products' => '/pages/all-products.php',
    'New Collection' => '/pages/new-collection.php',
    'FAQ' => '/pages/faq.php',
    'Product Detail' => '/pages/product-detail.php',
    'Privacy Policy' => '/pages/privacy-policy.php',
    'Terms' => '/pages/terms.php',
    
    // Auth Pages
    'Login' => '/auth/login.php',
    'Register' => '/auth/register.php',
    
    // Member Pages
    'Member Dashboard' => '/member/dashboard.php',
    'Member Wallet' => '/member/wallet.php',
    'Member Orders' => '/member/orders.php',
    'Member Referral' => '/member/referral.php',
    'Member Profile' => '/member/profile.php',
    
    // Admin Pages
    'Admin Dashboard' => '/admin/index.php',
    'Admin Products' => '/admin/products/index.php',
    'Admin Orders' => '/admin/orders/index.php',
    'Admin Deposits' => '/admin/deposits/index.php',
];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Page Test Results</title>
    <style>
        body {
            font-family: monospace;
            background: #1a1a1a;
            color: #00ff00;
            padding: 20px;
        }
        .result {
            margin: 10px 0;
            padding: 10px;
            border-left: 4px solid;
        }
        .success {
            border-color: #00ff00;
            background: #003300;
        }
        .error {
            border-color: #ff0000;
            background: #330000;
            color: #ff6666;
        }
        .warning {
            border-color: #ffaa00;
            background: #332200;
            color: #ffcc66;
        }
        pre {
            white-space: pre-wrap;
            font-size: 11px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h1>🧪 Page Test Results</h1>
    <p>Testing <?php echo count($pages_to_test); ?> pages...</p>
    <hr>
    
    <?php
    foreach ($pages_to_test as $name => $path) {
        $full_path = __DIR__ . $path;
        
        echo "<div class='result ";
        
        if (!file_exists($full_path)) {
            echo "error'>";
            echo "❌ <strong>$name</strong> - FILE NOT FOUND<br>";
            echo "Path: $path";
            echo "</div>";
            continue;
        }
        
        // Check PHP syntax without executing
        $output = shell_exec("php -l " . escapeshellarg($full_path) . " 2>&1");
        
        if (strpos($output, 'No syntax errors') !== false) {
            // Syntax OK, now check for common issues in code
            $content = file_get_contents($full_path);
            $issues = [];
            
            // Check for problematic table references
            if (preg_match('/wallet_topups|bank_accounts|payment_gateways|variant_stock/', $content)) {
                preg_match_all('/(wallet_topups|bank_accounts|payment_gateways|variant_stock)/', $content, $matches);
                $issues[] = "References to missing tables: " . implode(', ', array_unique($matches[0]));
            }
            
            // Check for problematic columns
            if (preg_match('/payment_status|is_published|is_new_collection/', $content)) {
                preg_match_all('/(payment_status|is_published|is_new_collection)/', $content, $matches);
                $issues[] = "References to non-existent columns: " . implode(', ', array_unique($matches[0]));
            }
            
            if (!empty($issues)) {
                echo "warning'>";
                echo "⚠️ <strong>$name</strong> - POTENTIAL ISSUES<br>";
                echo "<pre>" . implode("\n", $issues) . "</pre>";
            } else {
                echo "success'>";
                echo "✅ <strong>$name</strong> - SYNTAX OK";
            }
        } else {
            echo "error'>";
            echo "❌ <strong>$name</strong> - SYNTAX ERROR<br>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }
        
        echo "</div>";
    }
    ?>
    
    <hr>
    <p>Test completed!</p>
</body>
</html>
