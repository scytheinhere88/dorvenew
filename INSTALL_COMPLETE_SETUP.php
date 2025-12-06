<?php
/**
 * =====================================================
 * DORVE.ID - COMPLETE DATABASE SETUP
 * =====================================================
 * Run this file once via browser to setup all required tables
 * Access: http://dorve.id/INSTALL_COMPLETE_SETUP.php
 * 
 * This will create:
 * - banners table (slider, popup, marquee)
 * - categories.icon field
 * - products featured fields
 * - product_images table
 * - settings table
 * - payment_gateway_settings table
 * - bank_accounts table
 * =====================================================
 */

// Direct database connection
require_once __DIR__ . '/config.php';

$success = [];
$errors = [];
$warnings = [];

// Function to execute SQL safely
function executeSql($pdo, $sql, $description) {
    global $success, $errors, $warnings;
    
    try {
        $pdo->exec($sql);
        $success[] = "✅ $description";
        return true;
    } catch (PDOException $e) {
        // Check if it's a "already exists" error (safe to ignore)
        if (strpos($e->getMessage(), 'already exists') !== false || 
            strpos($e->getMessage(), 'Duplicate') !== false) {
            $warnings[] = "⏭️ $description (already exists - skipped)";
            return true;
        } else {
            $errors[] = "❌ $description - Error: " . $e->getMessage();
            return false;
        }
    }
}

// Start transaction for safety
try {
    $pdo->beginTransaction();

    // =====================================================
    // 1. BANNERS TABLE
    // =====================================================
    $sql = "CREATE TABLE IF NOT EXISTS `banners` (
      `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
      `banner_type` VARCHAR(50) NOT NULL COMMENT 'slider, popup, marquee',
      `title` VARCHAR(255) DEFAULT NULL,
      `subtitle` TEXT DEFAULT NULL,
      `image_url` VARCHAR(500) DEFAULT NULL,
      `link_url` VARCHAR(500) DEFAULT NULL,
      `cta_text` VARCHAR(100) DEFAULT NULL,
      `is_active` TINYINT(1) DEFAULT 1,
      `display_order` INT(11) DEFAULT 0,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      KEY `idx_banner_type` (`banner_type`),
      KEY `idx_is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeSql($pdo, $sql, "Created BANNERS table");

    // =====================================================
    // 2. CATEGORIES ICON FIELD
    // =====================================================
    // Check if column exists first
    $stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE 'icon'");
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE `categories` ADD COLUMN `icon` VARCHAR(500) DEFAULT NULL AFTER `name`";
        executeSql($pdo, $sql, "Added ICON field to categories table");
    } else {
        $warnings[] = "⏭️ ICON field in categories (already exists - skipped)";
    }

    // =====================================================
    // 3. PRODUCTS FEATURED FIELDS
    // =====================================================
    // Check is_featured
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'is_featured'");
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE `products` ADD COLUMN `is_featured` TINYINT(1) DEFAULT 0 AFTER `is_active`";
        executeSql($pdo, $sql, "Added IS_FEATURED field to products");
    } else {
        $warnings[] = "⏭️ IS_FEATURED field (already exists - skipped)";
    }

    // Check is_best_seller
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'is_best_seller'");
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE `products` ADD COLUMN `is_best_seller` TINYINT(1) DEFAULT 0 AFTER `is_featured`";
        executeSql($pdo, $sql, "Added IS_BEST_SELLER field to products");
    } else {
        $warnings[] = "⏭️ IS_BEST_SELLER field (already exists - skipped)";
    }

    // Check is_new
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'is_new'");
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE `products` ADD COLUMN `is_new` TINYINT(1) DEFAULT 0 AFTER `is_best_seller`";
        executeSql($pdo, $sql, "Added IS_NEW field to products");
    } else {
        $warnings[] = "⏭️ IS_NEW field (already exists - skipped)";
    }

    // =====================================================
    // 4. PRODUCT_IMAGES TABLE
    // =====================================================
    $sql = "CREATE TABLE IF NOT EXISTS `product_images` (
      `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
      `product_id` INT(11) NOT NULL,
      `image_path` VARCHAR(500) NOT NULL,
      `is_primary` TINYINT(1) DEFAULT 0,
      `sort_order` INT(11) DEFAULT 0,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      KEY `idx_product_id` (`product_id`),
      KEY `idx_is_primary` (`is_primary`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeSql($pdo, $sql, "Created PRODUCT_IMAGES table");

    // =====================================================
    // 5. SETTINGS TABLE
    // =====================================================
    $sql = "CREATE TABLE IF NOT EXISTS `settings` (
      `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
      `setting_key` VARCHAR(255) NOT NULL UNIQUE,
      `value` TEXT,
      `type` VARCHAR(50) DEFAULT 'text',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeSql($pdo, $sql, "Created SETTINGS table");

    // =====================================================
    // 6. PAYMENT_GATEWAY_SETTINGS TABLE
    // =====================================================
    $sql = "CREATE TABLE IF NOT EXISTS `payment_gateway_settings` (
      `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
      `gateway_name` VARCHAR(100) NOT NULL UNIQUE,
      `display_name` VARCHAR(255) NOT NULL,
      `api_key` VARCHAR(500),
      `api_secret` VARCHAR(500),
      `merchant_id` VARCHAR(255),
      `client_id` VARCHAR(500),
      `client_secret` VARCHAR(500),
      `is_production` TINYINT(1) DEFAULT 0,
      `is_active` TINYINT(1) DEFAULT 1,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeSql($pdo, $sql, "Created PAYMENT_GATEWAY_SETTINGS table");

    // Insert default payment gateways
    $sql = "INSERT IGNORE INTO `payment_gateway_settings` (`gateway_name`, `display_name`, `is_active`) VALUES
            ('midtrans', 'Midtrans Payment Gateway', 1),
            ('manual_transfer', 'Manual Bank Transfer', 1)";
    executeSql($pdo, $sql, "Inserted default payment gateways");

    // =====================================================
    // 7. BANK_ACCOUNTS TABLE
    // =====================================================
    $sql = "CREATE TABLE IF NOT EXISTS `bank_accounts` (
      `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
      `bank_name` VARCHAR(255) NOT NULL,
      `bank_code` VARCHAR(50),
      `account_number` VARCHAR(100) NOT NULL,
      `account_name` VARCHAR(255) NOT NULL,
      `branch` VARCHAR(255),
      `is_active` TINYINT(1) DEFAULT 1,
      `display_order` INT(11) DEFAULT 0,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeSql($pdo, $sql, "Created BANK_ACCOUNTS table");

    // Insert sample bank account
    $sql = "INSERT IGNORE INTO `bank_accounts` (`id`, `bank_name`, `account_number`, `account_name`, `is_active`, `display_order`) VALUES
            (1, 'BCA', '1234567890', 'DORVE INDONESIA', 1, 1)";
    executeSql($pdo, $sql, "Inserted sample bank account");

    // Commit all changes
    $pdo->commit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    $errors[] = "❌ Transaction failed: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup Complete - Dorve.id</title>
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
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 32px;
            font-size: 16px;
        }
        .result-box {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 16px;
            border-left: 4px solid;
        }
        .success-box {
            background: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }
        .warning-box {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }
        .error-box {
            background: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }
        .result-item {
            padding: 8px 0;
            font-size: 15px;
            line-height: 1.6;
        }
        .summary {
            background: #e0e7ff;
            padding: 24px;
            border-radius: 8px;
            margin-bottom: 32px;
            border: 2px solid #6366f1;
        }
        .summary h2 {
            color: #3730a3;
            margin-bottom: 16px;
            font-size: 20px;
        }
        .stat {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 15px;
        }
        .stat-label {
            font-weight: 600;
            color: #4c1d95;
        }
        .stat-value {
            font-weight: 700;
            color: #5b21b6;
        }
        .next-steps {
            background: #f3f4f6;
            padding: 24px;
            border-radius: 8px;
            margin-top: 32px;
        }
        .next-steps h3 {
            color: #1f2937;
            margin-bottom: 16px;
        }
        .next-steps ol {
            padding-left: 24px;
        }
        .next-steps li {
            padding: 8px 0;
            color: #4b5563;
            line-height: 1.6;
        }
        .btn-delete {
            display: inline-block;
            margin-top: 24px;
            padding: 12px 32px;
            background: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-delete:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 2px solid #e5e7eb;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <?php echo empty($errors) ? '✅' : '⚠️'; ?>
            Database Setup Complete
        </h1>
        <p class="subtitle">Setup result for Dorve.id complete system</p>

        <div class="summary">
            <h2>📊 Summary</h2>
            <div class="stat">
                <span class="stat-label">✅ Successful:</span>
                <span class="stat-value"><?php echo count($success); ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">⏭️ Skipped:</span>
                <span class="stat-value"><?php echo count($warnings); ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">❌ Errors:</span>
                <span class="stat-value"><?php echo count($errors); ?></span>
            </div>
        </div>

        <?php if (!empty($success)): ?>
        <div class="result-box success-box">
            <strong style="font-size: 18px; margin-bottom: 12px; display: block;">✅ Successful Operations</strong>
            <?php foreach ($success as $msg): ?>
                <div class="result-item"><?php echo htmlspecialchars($msg); ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($warnings)): ?>
        <div class="result-box warning-box">
            <strong style="font-size: 18px; margin-bottom: 12px; display: block;">⏭️ Skipped (Already Exists)</strong>
            <?php foreach ($warnings as $msg): ?>
                <div class="result-item"><?php echo htmlspecialchars($msg); ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="result-box error-box">
            <strong style="font-size: 18px; margin-bottom: 12px; display: block;">❌ Errors</strong>
            <?php foreach ($errors as $msg): ?>
                <div class="result-item"><?php echo htmlspecialchars($msg); ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="next-steps">
            <h3>🚀 Next Steps</h3>
            <ol>
                <li><strong>Delete this file</strong> for security (INSTALL_COMPLETE_SETUP.php)</li>
                <li>Login to admin panel: <a href="/admin/login.php">/admin/login.php</a></li>
                <li>Setup marquee text: Admin → Promosi & Banner → Marquee Text</li>
                <li>Add slider banners: Admin → Promosi & Banner</li>
                <li>Add category icons: Admin → Categories</li>
                <li>Mark featured products: Admin → Products (edit product, check Featured)</li>
                <li>Configure payment: Admin → Settings → Payment Settings</li>
                <li>Add bank accounts: Admin → Settings → Bank Accounts</li>
            </ol>
        </div>

        <div class="footer">
            <p><strong>⚠️ IMPORTANT: Delete this file after setup!</strong></p>
            <p style="margin-top: 8px;">File: <code>INSTALL_COMPLETE_SETUP.php</code></p>
            <p style="margin-top: 16px; color: #9ca3af; font-size: 14px;">
                Setup completed at: <?php echo date('Y-m-d H:i:s'); ?>
            </p>
        </div>
    </div>
</body>
</html>
