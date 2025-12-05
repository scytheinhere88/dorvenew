<?php
/**
 * REVIEW & RATING SYSTEM - DATABASE MIGRATION
 * 
 * Features:
 * - Product Reviews dengan Rating (1-5 bintang)
 * - Upload 3 foto + 1 video per review
 * - Auto voucher reward untuk rating >= 4
 * - Admin review management
 * - Biteship tracking status integration
 * 
 * Jalankan file ini SEKALI SAJA di browser:
 * https://dorve.id/INSTALL_REVIEW_SYSTEM.php
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
    <title>Review System Migration</title>
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
        <h1>⭐ Review & Rating System - Database Migration</h1>
        <p style="color: #6B7280;">Professional Review System with Photo/Video Upload & Rewards</p>

        <div class="warning">
            <strong>⚠️ PENTING!</strong><br>
            <ul style="margin: 8px 0 0 20px;">
                <li>Migration ini akan membuat tables untuk review system</li>
                <li>Jalankan hanya SEKALI</li>
                <li>Hapus file ini setelah selesai</li>
            </ul>
        </div>

        <?php if (!isset($_POST['confirm'])): ?>
            
            <h2 style="margin-top: 30px;">📋 Yang Akan Dibuat:</h2>
            
            <div class="step">
                <h3>1️⃣ Create product_reviews Table</h3>
                <p>Store semua reviews dari member & admin (rating, text, nama reviewer)</p>
            </div>

            <div class="step">
                <h3>2️⃣ Create review_media Table</h3>
                <p>Store photos (3) & video (1) untuk setiap review</p>
            </div>

            <div class="step">
                <h3>3️⃣ Update orders Table</h3>
                <p>Tambah kolom: delivery_status (Biteship tracking), completed_at, can_review</p>
            </div>

            <div class="step">
                <h3>4️⃣ Update products Table</h3>
                <p>Tambah kolom: average_rating, total_reviews (auto-calculated dari reviews)</p>
            </div>

            <div class="step">
                <h3>5️⃣ Create Uploads Directory</h3>
                <p>Folder untuk simpan review photos & videos</p>
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

            // STEP 1: Create product_reviews table
            try {
                $sql = "CREATE TABLE IF NOT EXISTS `product_reviews` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `order_id` INT NULL COMMENT 'NULL jika review dari admin',
                    `order_item_id` INT NULL,
                    `product_id` INT NOT NULL,
                    `user_id` INT NULL COMMENT 'NULL jika admin review',
                    `rating` TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
                    `review_text` TEXT NOT NULL,
                    `reviewer_name` VARCHAR(255) NOT NULL,
                    `is_verified_purchase` TINYINT(1) DEFAULT 1 COMMENT '1 = real order, 0 = admin review',
                    `created_by_admin` TINYINT(1) DEFAULT 0,
                    `status` ENUM('published', 'hidden') DEFAULT 'published',
                    `admin_reply` TEXT NULL,
                    `replied_at` DATETIME NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_product` (`product_id`, `status`),
                    INDEX `idx_user` (`user_id`),
                    INDEX `idx_order` (`order_id`),
                    INDEX `idx_rating` (`rating`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                $pdo->exec($sql);
                $results[] = ['success' => true, 'step' => 'Create product_reviews table', 'message' => 'Table created successfully'];
                $successCount++;
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    $results[] = ['success' => true, 'step' => 'Create product_reviews table', 'message' => 'Table already exists (skipped)'];
                    $successCount++;
                } else {
                    $results[] = ['success' => false, 'step' => 'Create product_reviews table', 'message' => $e->getMessage()];
                    $errorCount++;
                }
            }

            // STEP 2: Create review_media table
            try {
                $sql = "CREATE TABLE IF NOT EXISTS `review_media` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `review_id` INT NOT NULL,
                    `media_type` ENUM('image', 'video') NOT NULL,
                    `file_path` VARCHAR(500) NOT NULL,
                    `file_size` INT NOT NULL COMMENT 'in bytes',
                    `duration` INT NULL COMMENT 'video duration in seconds',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_review` (`review_id`),
                    FOREIGN KEY (`review_id`) REFERENCES `product_reviews`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                $pdo->exec($sql);
                $results[] = ['success' => true, 'step' => 'Create review_media table', 'message' => 'Table created successfully'];
                $successCount++;
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    $results[] = ['success' => true, 'step' => 'Create review_media table', 'message' => 'Table already exists (skipped)'];
                    $successCount++;
                } else {
                    $results[] = ['success' => false, 'step' => 'Create review_media table', 'message' => $e->getMessage()];
                    $errorCount++;
                }
            }

            // STEP 3: Update orders table
            try {
                $stmt = $pdo->query("DESCRIBE orders");
                $columns = array_column($stmt->fetchAll(), 'Field');
                
                $columnsToAdd = [];
                if (!in_array('delivery_status', $columns)) {
                    $columnsToAdd[] = "ADD COLUMN delivery_status VARCHAR(50) NULL COMMENT 'Biteship tracking status'";
                }
                if (!in_array('completed_at', $columns)) {
                    $columnsToAdd[] = "ADD COLUMN completed_at DATETIME NULL COMMENT 'When user clicked Terima Pesanan'";
                }
                if (!in_array('can_review', $columns)) {
                    $columnsToAdd[] = "ADD COLUMN can_review TINYINT(1) DEFAULT 0 COMMENT '1 = user can write review'";
                }
                
                if (count($columnsToAdd) > 0) {
                    $sql = "ALTER TABLE orders " . implode(", ", $columnsToAdd);
                    $pdo->exec($sql);
                    $results[] = ['success' => true, 'step' => 'Update orders table', 'message' => 'Added ' . count($columnsToAdd) . ' columns'];
                    $successCount++;
                } else {
                    $results[] = ['success' => true, 'step' => 'Update orders table', 'message' => 'All columns already exist (skipped)'];
                    $successCount++;
                }
            } catch (Exception $e) {
                $results[] = ['success' => false, 'step' => 'Update orders table', 'message' => $e->getMessage()];
                $errorCount++;
            }

            // STEP 4: Update products table
            try {
                $stmt = $pdo->query("DESCRIBE products");
                $columns = array_column($stmt->fetchAll(), 'Field');
                
                $columnsToAdd = [];
                if (!in_array('average_rating', $columns)) {
                    $columnsToAdd[] = "ADD COLUMN average_rating DECIMAL(2,1) DEFAULT 0.0 COMMENT 'Average rating from reviews'";
                }
                if (!in_array('total_reviews', $columns)) {
                    $columnsToAdd[] = "ADD COLUMN total_reviews INT DEFAULT 0 COMMENT 'Total number of reviews'";
                }
                
                if (count($columnsToAdd) > 0) {
                    $sql = "ALTER TABLE products " . implode(", ", $columnsToAdd);
                    $pdo->exec($sql);
                    $results[] = ['success' => true, 'step' => 'Update products table', 'message' => 'Added ' . count($columnsToAdd) . ' columns'];
                    $successCount++;
                } else {
                    $results[] = ['success' => true, 'step' => 'Update products table', 'message' => 'All columns already exist (skipped)'];
                    $successCount++;
                }
            } catch (Exception $e) {
                $results[] = ['success' => false, 'step' => 'Update products table', 'message' => $e->getMessage()];
                $errorCount++;
            }

            // STEP 5: Create upload directories
            try {
                $reviewDir = __DIR__ . '/uploads/reviews';
                $photosDir = $reviewDir . '/photos';
                $videosDir = $reviewDir . '/videos';
                
                if (!file_exists($reviewDir)) {
                    mkdir($reviewDir, 0755, true);
                }
                if (!file_exists($photosDir)) {
                    mkdir($photosDir, 0755, true);
                }
                if (!file_exists($videosDir)) {
                    mkdir($videosDir, 0755, true);
                }
                
                // Create .htaccess for videos (streaming)
                $htaccess = $videosDir . '/.htaccess';
                if (!file_exists($htaccess)) {
                    file_put_contents($htaccess, "AddType video/mp4 .mp4\nAddType video/webm .webm");
                }
                
                $results[] = ['success' => true, 'step' => 'Create upload directories', 'message' => 'Directories created: /uploads/reviews/photos & /videos'];
                $successCount++;
            } catch (Exception $e) {
                $results[] = ['success' => false, 'step' => 'Create upload directories', 'message' => $e->getMessage()];
                $errorCount++;
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
                        <p style="margin-top: 8px;">Database ready untuk:</p>
                        <ul style="margin-top: 8px;">
                            <li>✅ Product Reviews & Ratings</li>
                            <li>✅ Photo & Video Uploads (3 photos + 1 video)</li>
                            <li>✅ Order Tracking & Completion</li>
                            <li>✅ Auto Voucher Rewards</li>
                            <li>✅ Admin Review Management</li>
                        </ul>
                        <p style="margin-top: 16px; color: #DC2626; font-weight: 600;">
                            ⚠️ PENTING: Hapus file INSTALL_REVIEW_SYSTEM.php sekarang!
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
