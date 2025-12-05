<?php
/**
 * DORVE.ID - BITESHIP INTEGRATION MIGRATION INSTALLER
 * 
 * Jalankan file ini SEKALI SAJA di browser Anda:
 * https://dorve.id/INSTALL_MIGRATION.php
 * 
 * PENTING: 
 * - Backup database dulu sebelum jalankan!
 * - Hapus file ini setelah selesai!
 */

require_once __DIR__ . '/config.php';

// Security: Only allow admin
if (!isAdmin()) {
    die('❌ ERROR: Hanya admin yang bisa menjalankan migration ini. Silakan login dulu.');
}

// Prevent multiple runs
$checkStmt = $pdo->query("SHOW TABLES LIKE 'biteship_shipments'");
if ($checkStmt->rowCount() > 0) {
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Migration Already Run</title></head><body>";
    echo "<h1>⚠️ WARNING</h1>";
    echo "<p>Migration sudah pernah dijalankan sebelumnya!</p>";
    echo "<p>Tabel 'biteship_shipments' sudah ada di database.</p>";
    echo "<p><strong>Jangan run lagi!</strong> Jika ada masalah, hubungi developer.</p>";
    echo "</body></html>";
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Biteship Migration Installer</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #F3F4F6;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1F2937;
            margin-bottom: 10px;
        }
        .warning {
            background: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .step {
            background: #F9FAFB;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            border: 1px solid #E5E7EB;
        }
        .step h3 {
            margin: 0 0 12px;
            color: #374151;
        }
        .success {
            color: #10B981;
            font-weight: 600;
        }
        .error {
            color: #EF4444;
            font-weight: 600;
        }
        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: #3B82F6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #2563EB;
        }
        .btn-danger {
            background: #EF4444;
        }
        .btn-danger:hover {
            background: #DC2626;
        }
        .progress {
            background: #E5E7EB;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-bar {
            background: #3B82F6;
            height: 100%;
            transition: width 0.3s;
        }
        pre {
            background: #1F2937;
            color: #F9FAFB;
            padding: 16px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚚 Biteship Integration - Database Migration</h1>
        <p style="color: #6B7280;">Dorve.id Order Management System</p>

        <div class="warning">
            <strong>⚠️ PENTING - BACA DULU!</strong><br>
            <ul style="margin: 8px 0 0 20px;">
                <li>Migration ini akan mengubah struktur database Anda</li>
                <li><strong>Pastikan Anda sudah backup database!</strong></li>
                <li>Jalankan hanya SEKALI</li>
                <li>Hapus file ini setelah selesai</li>
            </ul>
        </div>

        <?php if (!isset($_POST['confirm'])): ?>
            
            <h2 style="margin-top: 30px;">📋 Yang Akan Dilakukan:</h2>
            
            <div class="step">
                <h3>1️⃣ Fix Settings Table</h3>
                <p>Normalize kolom 'value' → 'setting_value'</p>
            </div>

            <div class="step">
                <h3>2️⃣ Update Orders Table</h3>
                <p>Tambah kolom: fulfillment_status, shipping_courier, shipping_service, shipping_cost, tracking_number, notes</p>
            </div>

            <div class="step">
                <h3>3️⃣ Create New Tables</h3>
                <ul style="margin: 8px 0 0 20px;">
                    <li><strong>order_addresses</strong> - Alamat pengiriman & billing</li>
                    <li><strong>biteship_shipments</strong> - Data pengiriman dari Biteship</li>
                    <li><strong>biteship_webhook_logs</strong> - Log webhook dari Biteship</li>
                    <li><strong>print_batches</strong> - Record batch print labels</li>
                </ul>
            </div>

            <div class="step">
                <h3>4️⃣ Insert Configuration</h3>
                <p>Biteship API key dan store settings</p>
            </div>

            <form method="POST" onsubmit="return confirm('Apakah Anda YAKIN sudah backup database?');">
                <label style="display: block; margin: 20px 0;">
                    <input type="checkbox" name="backup_confirm" required>
                    <strong> Saya sudah backup database</strong>
                </label>
                <button type="submit" name="confirm" value="yes" class="btn">
                    ▶️ Jalankan Migration Sekarang
                </button>
            </form>

        <?php else: ?>

            <h2 style="margin-top: 30px;">⚙️ Menjalankan Migration...</h2>
            <div class="progress">
                <div class="progress-bar" style="width: 0%" id="progressBar"></div>
            </div>
            <div id="results"></div>

            <?php
            $results = [];
            $errorCount = 0;
            $successCount = 0;

            // STEP 1: Fix Settings Table
            try {
                $stmt = $pdo->query("DESCRIBE settings");
                $columns = array_column($stmt->fetchAll(), 'Field');
                
                if (in_array('value', $columns) && !in_array('setting_value', $columns)) {
                    $pdo->exec("ALTER TABLE settings CHANGE COLUMN `value` `setting_value` TEXT");
                    $results[] = ['success' => true, 'step' => 'Fix Settings Table', 'message' => 'Renamed column value → setting_value'];
                    $successCount++;
                } elseif (in_array('setting_value', $columns) && in_array('value', $columns)) {
                    $pdo->exec("UPDATE settings SET setting_value = `value` WHERE setting_value IS NULL OR setting_value = ''");
                    $pdo->exec("ALTER TABLE settings DROP COLUMN `value`");
                    $results[] = ['success' => true, 'step' => 'Fix Settings Table', 'message' => 'Merged and dropped old value column'];
                    $successCount++;
                } else {
                    $results[] = ['success' => true, 'step' => 'Fix Settings Table', 'message' => 'Already using setting_value (no changes needed)'];
                    $successCount++;
                }
            } catch (Exception $e) {
                $results[] = ['success' => false, 'step' => 'Fix Settings Table', 'message' => $e->getMessage()];
                $errorCount++;
            }

            // STEP 2: Update Orders Table
            try {
                $stmt = $pdo->query("DESCRIBE orders");
                $orderColumns = array_column($stmt->fetchAll(), 'Field');
                
                $columnsToAdd = [
                    'fulfillment_status' => "ALTER TABLE orders ADD COLUMN fulfillment_status ENUM('new', 'waiting_print', 'waiting_pickup', 'in_transit', 'delivered', 'cancelled', 'returned') DEFAULT 'new' AFTER payment_status",
                    'shipping_courier' => "ALTER TABLE orders ADD COLUMN shipping_courier VARCHAR(100) NULL AFTER fulfillment_status",
                    'shipping_service' => "ALTER TABLE orders ADD COLUMN shipping_service VARCHAR(100) NULL AFTER shipping_courier",
                    'shipping_cost' => "ALTER TABLE orders ADD COLUMN shipping_cost DECIMAL(15,2) DEFAULT 0 AFTER shipping_service",
                    'tracking_number' => "ALTER TABLE orders ADD COLUMN tracking_number VARCHAR(255) NULL AFTER shipping_cost",
                    'notes' => "ALTER TABLE orders ADD COLUMN notes TEXT NULL AFTER tracking_number"
                ];
                
                $addedColumns = 0;
                foreach ($columnsToAdd as $colName => $sql) {
                    if (!in_array($colName, $orderColumns)) {
                        $pdo->exec($sql);
                        $addedColumns++;
                    }
                }
                
                // Add indexes
                if (!in_array('tracking_number', $orderColumns)) {
                    $pdo->exec("ALTER TABLE orders ADD INDEX idx_tracking (tracking_number)");
                    $pdo->exec("ALTER TABLE orders ADD INDEX idx_fulfillment (fulfillment_status)");
                }
                
                $results[] = ['success' => true, 'step' => 'Update Orders Table', 'message' => "Added $addedColumns new columns and indexes"];
                $successCount++;
            } catch (Exception $e) {
                $results[] = ['success' => false, 'step' => 'Update Orders Table', 'message' => $e->getMessage()];
                $errorCount++;
            }

            // STEP 3: Create order_addresses table
            try {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS order_addresses (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        order_id INT NOT NULL,
                        type ENUM('billing', 'shipping') NOT NULL,
                        name VARCHAR(255) NOT NULL,
                        phone VARCHAR(50) NOT NULL,
                        address_line TEXT NOT NULL,
                        district VARCHAR(255) NULL,
                        city VARCHAR(255) NOT NULL,
                        province VARCHAR(255) NOT NULL,
                        postal_code VARCHAR(20) NOT NULL,
                        country VARCHAR(5) DEFAULT 'ID',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_order (order_id),
                        INDEX idx_type (type)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
                $results[] = ['success' => true, 'step' => 'Create order_addresses', 'message' => 'Table created successfully'];
                $successCount++;
            } catch (Exception $e) {
                $results[] = ['success' => false, 'step' => 'Create order_addresses', 'message' => $e->getMessage()];
                $errorCount++;
            }

            // STEP 4: Create biteship_shipments table
            try {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS biteship_shipments (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        order_id INT NOT NULL,
                        biteship_order_id VARCHAR(255) NOT NULL UNIQUE,
                        courier_company VARCHAR(100) NOT NULL,
                        courier_name VARCHAR(100) NOT NULL,
                        courier_service_name VARCHAR(100) NOT NULL,
                        courier_service_code VARCHAR(100) NULL,
                        rate_id VARCHAR(255) NULL,
                        shipping_cost DECIMAL(15,2) NOT NULL DEFAULT 0,
                        insurance_cost DECIMAL(15,2) DEFAULT 0,
                        status VARCHAR(50) DEFAULT 'pending',
                        waybill_id VARCHAR(255) NULL,
                        label_print_batch_id INT NULL,
                        pickup_code VARCHAR(50) NULL,
                        delivery_date DATETIME NULL,
                        pickup_time VARCHAR(100) NULL,
                        destination_province VARCHAR(255) NULL,
                        destination_city VARCHAR(255) NULL,
                        destination_postal_code VARCHAR(20) NULL,
                        origin_province VARCHAR(255) NULL,
                        origin_city VARCHAR(255) NULL,
                        origin_postal_code VARCHAR(20) NULL,
                        weight_kg DECIMAL(10,2) DEFAULT 0,
                        raw_response TEXT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_order (order_id),
                        INDEX idx_biteship_id (biteship_order_id),
                        INDEX idx_waybill (waybill_id),
                        INDEX idx_status (status),
                        INDEX idx_batch (label_print_batch_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
                $results[] = ['success' => true, 'step' => 'Create biteship_shipments', 'message' => 'Table created successfully'];
                $successCount++;
            } catch (Exception $e) {
                $results[] = ['success' => false, 'step' => 'Create biteship_shipments', 'message' => $e->getMessage()];
                $errorCount++;
            }

            // STEP 5: Create biteship_webhook_logs table
            try {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS biteship_webhook_logs (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        event VARCHAR(100) NOT NULL,
                        biteship_order_id VARCHAR(255) NULL,
                        payload TEXT NOT NULL,
                        processed TINYINT(1) DEFAULT 0,
                        error_message TEXT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_event (event),
                        INDEX idx_biteship_id (biteship_order_id),
                        INDEX idx_processed (processed)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
                $results[] = ['success' => true, 'step' => 'Create biteship_webhook_logs', 'message' => 'Table created successfully'];
                $successCount++;
            } catch (Exception $e) {
                $results[] = ['success' => false, 'step' => 'Create biteship_webhook_logs', 'message' => $e->getMessage()];
                $errorCount++;
            }

            // STEP 6: Create print_batches table
            try {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS print_batches (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        batch_code VARCHAR(50) NOT NULL UNIQUE,
                        printed_by_admin_id INT NOT NULL,
                        printed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        total_orders INT DEFAULT 0,
                        notes TEXT NULL,
                        INDEX idx_batch_code (batch_code),
                        INDEX idx_admin (printed_by_admin_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
                $results[] = ['success' => true, 'step' => 'Create print_batches', 'message' => 'Table created successfully'];
                $successCount++;
            } catch (Exception $e) {
                $results[] = ['success' => false, 'step' => 'Create print_batches', 'message' => $e->getMessage()];
                $errorCount++;
            }

            // STEP 7: Insert Biteship Configuration
            try {
                $settings = [
                    'biteship_enabled' => '1',
                    'biteship_api_key' => 'biteship_live.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoiRG9ydmUuaWQiLCJ1c2VySWQiOiI2OTI4NDVhNDM4MzQ5ZjAyZjdhM2VhNDgiLCJpYXQiOjE3NjQ2NTYwMjV9.xmkeeT2ghfHPe7PItX5HJ0KptlC5xbIhL1TlHWn6S1U',
                    'biteship_environment' => 'production',
                    'biteship_webhook_secret' => '',
                    'biteship_default_couriers' => 'jne,jnt,sicepat,anteraja,idexpress',
                    'store_name' => 'Dorve.id Official Store',
                    'store_phone' => '+62-813-7737-8859',
                    'store_address' => 'Jakarta, Indonesia',
                    'store_city' => 'Jakarta Selatan',
                    'store_province' => 'DKI Jakarta',
                    'store_postal_code' => '12345',
                    'store_country' => 'ID'
                ];
                
                foreach ($settings as $key => $value) {
                    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                    $stmt->execute([$key, $value]);
                }
                
                $results[] = ['success' => true, 'step' => 'Insert Configuration', 'message' => 'Biteship settings configured'];
                $successCount++;
            } catch (Exception $e) {
                $results[] = ['success' => false, 'step' => 'Insert Configuration', 'message' => $e->getMessage()];
                $errorCount++;
            }

            // Display results
            echo "<script>document.getElementById('progressBar').style.width = '100%';</script>";
            
            foreach ($results as $result) {
                $icon = $result['success'] ? '✅' : '❌';
                $class = $result['success'] ? 'success' : 'error';
                echo "<div class='step'>";
                echo "<h3>$icon {$result['step']}</h3>";
                echo "<p class='$class'>{$result['message']}</p>";
                echo "</div>";
            }

            if ($errorCount === 0) {
                echo "<div style='background: #D1FAE5; border-left: 4px solid #10B981; padding: 20px; margin: 30px 0; border-radius: 8px;'>";
                echo "<h2 style='color: #065F46; margin: 0 0 12px;'>🎉 Migration Berhasil!</h2>";
                echo "<p style='color: #065F46; margin: 0;'>Semua langkah selesai tanpa error. Total: <strong>$successCount</strong> operasi berhasil.</p>";
                echo "</div>";

                echo "<div style='background: #DBEAFE; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
                echo "<h3 style='color: #1E40AF; margin: 0 0 12px;'>📋 Next Steps:</h3>";
                echo "<ol style='color: #1E40AF; line-height: 1.8;'>";
                echo "<li><strong>Hapus file ini (INSTALL_MIGRATION.php)</strong> dari server</li>";
                echo "<li>Go to <a href='/admin/settings/api-settings.php'>Admin → Settings → API Settings</a></li>";
                echo "<li>Test Biteship API connection</li>";
                echo "<li>Configure webhook di Biteship Dashboard:<br><code style='background: white; padding: 4px 8px; border-radius: 4px;'>https://dorve.id/api/biteship/webhook.php</code></li>";
                echo "<li>Test order flow dari checkout hingga print labels</li>";
                echo "</ol>";
                echo "</div>";

                echo "<a href='/admin/orders/index.php' class='btn'>🎯 Go to Orders Page</a>";
                echo "<a href='/admin/settings/api-settings.php' class='btn' style='background: #10B981; margin-left: 12px;'>⚙️ Go to Settings</a>";
            } else {
                echo "<div style='background: #FEE2E2; border-left: 4px solid #EF4444; padding: 20px; margin: 30px 0; border-radius: 8px;'>";
                echo "<h2 style='color: #991B1B; margin: 0 0 12px;'>⚠️ Migration Selesai dengan Error</h2>";
                echo "<p style='color: #991B1B; margin: 0;'>Success: <strong>$successCount</strong> | Errors: <strong style='color: #DC2626;'>$errorCount</strong></p>";
                echo "<p style='color: #991B1B; margin: 12px 0 0;'>Silakan screenshot hasil ini dan hubungi developer.</p>";
                echo "</div>";
            }
            ?>

            <div style="margin-top: 40px; padding: 20px; background: #FEF3C7; border-radius: 8px;">
                <h3 style="color: #92400E; margin: 0 0 12px;">⚠️ PENTING!</h3>
                <p style="color: #92400E; margin: 0;"><strong>Hapus file INSTALL_MIGRATION.php ini setelah selesai!</strong></p>
                <p style="color: #92400E; margin: 8px 0 0; font-size: 14px;">File ini bisa dijalankan siapa saja yang tahu URLnya. Untuk keamanan, segera hapus.</p>
            </div>

        <?php endif; ?>

    </div>

    <script>
    <?php if (isset($_POST['confirm'])): ?>
        // Animate progress bar
        setTimeout(() => {
            document.getElementById('progressBar').style.width = '100%';
        }, 500);
    <?php endif; ?>
    </script>
</body>
</html>
