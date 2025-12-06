-- =====================================================
-- DORVE.ID - SAFE DATABASE FIX (Step by Step)
-- Run this SQL di phpMyAdmin untuk fix semua issues
-- =====================================================

-- STEP 1: Fix banners table - add DEFAULT for image_url
ALTER TABLE `banners` 
MODIFY COLUMN `image_url` VARCHAR(255) DEFAULT NULL;

-- STEP 2: Fix categories table - add icon columns
ALTER TABLE `categories` 
ADD COLUMN IF NOT EXISTS `icon` VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `icon_type` ENUM('emoji','image','svg') DEFAULT 'emoji';

-- STEP 3: Fix wallet_transactions - remove wrong columns if exist
SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'wallet_transactions' AND COLUMN_NAME = 'payment_status');
SET @sqlstmt := IF(@exist > 0, 'ALTER TABLE `wallet_transactions` DROP COLUMN `payment_status`', 'SELECT "Column does not exist"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;

-- STEP 4: Add missing columns to wallet_transactions
ALTER TABLE `wallet_transactions`
ADD COLUMN IF NOT EXISTS `amount_original` DECIMAL(10,2) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `unique_code` INT(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `bank_name` VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `account_number` VARCHAR(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `account_name` VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `proof_of_payment` VARCHAR(255) DEFAULT NULL;

-- STEP 5: Recreate payment_methods table
DROP TABLE IF EXISTS `payment_methods`;

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `method_code` varchar(50) NOT NULL,
  `type` enum('bank_transfer','midtrans','wallet','cod') NOT NULL DEFAULT 'bank_transfer',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `icon` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `method_code` (`method_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment_methods` (`name`, `method_code`, `type`, `is_active`, `display_order`, `description`) VALUES
('Transfer Bank Manual', 'bank_transfer', 'bank_transfer', 1, 1, 'Transfer ke rekening bank kami'),
('Midtrans Payment Gateway', 'midtrans', 'midtrans', 1, 2, 'Bayar dengan QRIS, E-Wallet, atau Kartu Kredit'),
('Saldo Wallet', 'wallet', 'wallet', 1, 3, 'Bayar menggunakan saldo wallet'),
('Bayar di Tempat (COD)', 'cod', 'cod', 0, 4, 'Bayar saat barang diterima');

-- STEP 6: Recreate payment_gateway_settings
DROP TABLE IF EXISTS `payment_gateway_settings`;

CREATE TABLE `payment_gateway_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gateway_name` varchar(50) NOT NULL,
  `server_key` varchar(255) DEFAULT NULL,
  `client_key` varchar(255) DEFAULT NULL,
  `merchant_id` varchar(100) DEFAULT NULL,
  `is_production` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `config_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `gateway_name` (`gateway_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment_gateway_settings` (`gateway_name`, `is_production`, `is_active`) VALUES
('midtrans', 0, 0);

-- STEP 7: Recreate system_settings
DROP TABLE IF EXISTS `system_settings`;

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(100) NOT NULL,
  `key_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type` enum('text','number','boolean','json') DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` (`key_name`, `key_value`, `description`, `type`) VALUES
('whatsapp_admin', '6281377378859', 'WhatsApp admin untuk notifikasi', 'text'),
('min_topup_amount', '10000', 'Minimum amount untuk topup wallet', 'number'),
('max_deduct_amount', '999999999', 'Maximum amount untuk deduct balance', 'number'),
('site_name', 'Dorve.id', 'Nama website', 'text'),
('site_description', 'Fashion Online Indonesia', 'Deskripsi website', 'text');

-- STEP 8: Add referral columns to users
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `referral_code` VARCHAR(20) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `referred_by` INT(11) DEFAULT NULL;

UPDATE `users` SET `referral_code` = CONCAT('DORVE', LPAD(id, 6, '0')) 
WHERE `referral_code` IS NULL OR `referral_code` = '';

-- STEP 9: Create referral_program table
CREATE TABLE IF NOT EXISTS `referral_program` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `referrer_id` int(11) NOT NULL,
  `referred_id` int(11) NOT NULL,
  `referral_code` varchar(20) NOT NULL,
  `reward_amount` decimal(10,2) DEFAULT 0.00,
  `reward_status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `first_purchase_date` timestamp NULL DEFAULT NULL,
  `reward_paid_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `referrer_id` (`referrer_id`),
  KEY `referred_id` (`referred_id`),
  KEY `referral_code` (`referral_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STEP 10: Add order columns
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `delivery_status` VARCHAR(50) DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS `completed_at` TIMESTAMP NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `can_review` TINYINT(1) DEFAULT 1;

-- =====================================================
-- DONE! Database fix complete
-- =====================================================
