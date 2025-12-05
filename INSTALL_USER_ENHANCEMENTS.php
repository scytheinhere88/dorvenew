<?php
/**
 * USER ENHANCEMENTS - DATABASE MIGRATION
 * 
 * Features:
 * - Password Reset System
 * - Address Book with Google Maps
 * 
 * Jalankan file ini SEKALI SAJA di browser:
 * https://dorve.id/INSTALL_USER_ENHANCEMENTS.php
 * 
 * HAPUS file ini setelah selesai!
 */

require_once __DIR__ . '/config.php';

if (!isAdmin()) {
    die('❌ ERROR: Hanya admin yang bisa menjalankan migration ini. Silakan login dulu.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>User Enhancements Migration</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 User Enhancements - Database Migration</h1>
        <p style="color: #6B7280;">Password Reset & Address Book Features</p>

        <div class="warning">
            <strong>⚠️ PENTING!</strong><br>
            <ul style="margin: 8px 0 0 20px;">
                <li>Migration ini akan membuat/update tables untuk fitur baru</li>
                <li>Jalankan hanya SEKALI</li>
                <li>Hapus file ini setelah selesai</li>
            </ul>
        </div>

        <?php if (!isset($_POST['confirm'])): ?>
            
            <h2 style="margin-top: 30px;">📋 Yang Akan Dibuat/Update:</h2>
            
            <div class="step">
                <h3>1️⃣ Update users Table</h3>
                <p>Tambah kolom untuk Password Reset: password_reset_token, password_reset_expiry, password_reset_attempts, last_password_reset_request</p>
            </div>

            <div class="step">
                <h3>2️⃣ Create user_addresses Table</h3>
                <p>Table untuk Address Book dengan Google Maps integration (latitude, longitude)</p>
            </div>

            <form method="POST" onsubmit="return confirm('Apakah Anda YAKIN akan menjalankan migration?');">
                <label style="display: block; margin: 20px 0;">
                    <input type="checkbox" name="backup_confirm" required>
                    <strong> Saya siap menjalankan migration</strong>
                </label>
                <button type="submit" name="confirm" value="yes" class="btn">
                    ▶️ Jalankan Migration Sekarang
                </button>
            </form>

        <?php else: ?>

            <h2 style="margin-top: 30px;">⚙️ Menjalankan Migration...</h2>

            <?php
            $results = [];
            $errorCount = 0;
            $successCount = 0;

            // STEP 1: Update users table for password reset
            try {
                // Check which columns are missing
                $stmt = $pdo->query("DESCRIBE users");
                $columns = array_column($stmt->fetchAll(), 'Field');
                
                $columnsToAdd = [];
                if (!in_array('password_reset_token', $columns)) {
                    $columnsToAdd[] = "ADD COLUMN password_reset_token VARCHAR(255) NULL";
                }
                if (!in_array('password_reset_expiry', $columns)) {
                    $columnsToAdd[] = "ADD COLUMN password_reset_expiry DATETIME NULL";
                }
                if (!in_array('password_reset_attempts', $columns)) {
                    $columnsToAdd[] = "ADD COLUMN password_reset_attempts INT DEFAULT 0";
                }
                if (!in_array('last_password_reset_request', $columns)) {
                    $columnsToAdd[] = "ADD COLUMN last_password_reset_request DATETIME NULL";
                }
                
                if (count($columnsToAdd) > 0) {
                    $sql = "ALTER TABLE users " . implode(", ", $columnsToAdd);
                    $pdo->exec($sql);
                    $results[] = ['success' => true, 'step' => 'Update users table', 'message' => 'Added ' . count($columnsToAdd) . ' columns for password reset'];
                    $successCount++;
                } else {
                    $results[] = ['success' => true, 'step' => 'Update users table', 'message' => 'All columns already exist (skipped)'];
                    $successCount++;
                }
            } catch (Exception $e) {
                $results[] = ['success' => false, 'step' => 'Update users table', 'message' => $e->getMessage()];
                $errorCount++;
            }

            // STEP 2: Create user_addresses table
            try {
                $sql = "CREATE TABLE IF NOT EXISTS `user_addresses` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `label` VARCHAR(100) NOT NULL COMMENT 'e.g., Home, Office',
                    `recipient_name` VARCHAR(255) NOT NULL,
                    `phone` VARCHAR(20) NOT NULL,
                    `address` TEXT NOT NULL,
                    `latitude` DECIMAL(10, 8) NULL,
                    `longitude` DECIMAL(11, 8) NULL,
                    `is_default` TINYINT(1) DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_user_id` (`user_id`),
                    INDEX `idx_default` (`user_id`, `is_default`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                $pdo->exec($sql);
                $results[] = ['success' => true, 'step' => 'Create user_addresses table', 'message' => 'Table created successfully'];
                $successCount++;
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    $results[] = ['success' => true, 'step' => 'Create user_addresses table', 'message' => 'Table already exists (skipped)'];
                    $successCount++;
                } else {
                    $results[] = ['success' => false, 'step' => 'Create user_addresses table', 'message' => $e->getMessage()];
                    $errorCount++;
                }
            }

            // Display results
            foreach ($results as $result) {
                $icon = $result['success'] ? '✅' : '❌';
                $class = $result['success'] ? 'success' : 'error';
                echo "<div class='step'>";
                echo "<h3>{$icon} {$result['step']}</h3>";
                echo "<p class='{$class}'>{$result['message']}</p>";
                echo "</div>";
            }
            ?>

            <div style="margin-top: 30px; padding: 20px; background: <?= $errorCount > 0 ? '#FEE2E2' : '#D1FAE5' ?>; border-radius: 8px;">
                <h2 style="margin-bottom: 12px;">📊 Summary</h2>
                <p><strong>✅ Success:</strong> <?= $successCount ?> steps</p>
                <p><strong>❌ Failed:</strong> <?= $errorCount ?> steps</p>
                
                <?php if ($errorCount === 0): ?>
                    <div style="margin-top: 20px; padding: 16px; background: white; border-radius: 6px;">
                        <strong>🎉 Migration Completed Successfully!</strong>
                        <p style="margin-top: 8px;">Features yang sekarang aktif:</p>
                        <ul style="margin-top: 8px;">
                            <li>✅ Forgot Password / Reset Password</li>
                            <li>✅ Address Book dengan Maps Support</li>
                        </ul>
                        <p style="margin-top: 16px; color: #DC2626; font-weight: 600;">
                            ⚠️ PENTING: Hapus file INSTALL_USER_ENHANCEMENTS.php sekarang!
                        </p>
                    </div>
                <?php else: ?>
                    <p style="margin-top: 12px; color: #991B1B;">
                        Ada error saat migration. Silakan check error message di atas atau hubungi developer.
                    </p>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>
