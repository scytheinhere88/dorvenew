-- =====================================================
-- DORVE.ID - FIX ALL CRITICAL BUGS
-- Run this SQL di phpMyAdmin untuk fix semua database issues
-- =====================================================

-- 1. Fix banners table - add image_url default untuk marquee
ALTER TABLE `banners` 
MODIFY COLUMN `image_url` VARCHAR(255) DEFAULT NULL;

-- 2. Fix wallet_transactions table - remove payment_status if exists (tidak diperlukan di wallet)
ALTER TABLE `wallet_transactions` 
DROP COLUMN IF EXISTS `payment_status`;

-- Make sure wallet_transactions has correct structure
ALTER TABLE `wallet_transactions`
ADD COLUMN IF NOT EXISTS `amount_original` DECIMAL(10,2) DEFAULT NULL AFTER `amount`,
ADD COLUMN IF NOT EXISTS `unique_code` INT(11) DEFAULT NULL AFTER `amount_original`,
ADD COLUMN IF NOT EXISTS `bank_name` VARCHAR(100) DEFAULT NULL AFTER `unique_code`,
ADD COLUMN IF NOT EXISTS `account_number` VARCHAR(50) DEFAULT NULL AFTER `bank_name`,
ADD COLUMN IF NOT EXISTS `account_name` VARCHAR(100) DEFAULT NULL AFTER `account_number`,
ADD COLUMN IF NOT EXISTS `proof_of_payment` VARCHAR(255) DEFAULT NULL AFTER `account_name`;

-- 3. Fix payment_methods table structure
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

-- Insert default payment methods
INSERT INTO `payment_methods` (`name`, `method_code`, `type`, `is_active`, `display_order`, `description`) VALUES
('Transfer Bank Manual', 'bank_transfer', 'bank_transfer', 1, 1, 'Transfer ke rekening bank kami'),
('Midtrans Payment Gateway', 'midtrans', 'midtrans', 1, 2, 'Bayar dengan QRIS, E-Wallet, atau Kartu Kredit'),
('Saldo Wallet', 'wallet', 'wallet', 1, 3, 'Bayar menggunakan saldo wallet'),
('Bayar di Tempat (COD)', 'cod', 'cod', 0, 4, 'Bayar saat barang diterima');

-- 4. Fix payment_gateway_settings table
DROP TABLE IF EXISTS `payment_gateway_settings`;

CREATE TABLE `payment_gateway_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gateway_name` varchar(50) NOT NULL,
  `server_key` varchar(255) DEFAULT NULL,
  `client_key` varchar(255) DEFAULT NULL,
  `merchant_id` varchar(100) DEFAULT NULL,
  `is_production` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `config_data` text DEFAULT NULL COMMENT 'JSON for additional configs',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `gateway_name` (`gateway_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default gateway settings
INSERT INTO `payment_gateway_settings` (`gateway_name`, `is_production`, `is_active`) VALUES
('midtrans', 0, 0) ON DUPLICATE KEY UPDATE gateway_name=gateway_name;

-- 5. Fix system_settings table
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

-- Insert default settings
INSERT INTO `system_settings` (`key_name`, `key_value`, `description`, `type`) VALUES
('whatsapp_admin', '6281377378859', 'WhatsApp admin untuk notifikasi', 'text'),
('min_topup_amount', '10000', 'Minimum amount untuk topup wallet', 'number'),
('max_deduct_amount', '999999999', 'Maximum amount untuk deduct balance', 'number'),
('site_name', 'Dorve.id', 'Nama website', 'text'),
('site_description', 'Fashion Online Indonesia', 'Deskripsi website', 'text') 
ON DUPLICATE KEY UPDATE key_name=key_name;

-- 6. Fix categories table - make sure icon column exists
ALTER TABLE `categories` 
ADD COLUMN IF NOT EXISTS `icon` VARCHAR(255) DEFAULT NULL AFTER `image_url`,
ADD COLUMN IF NOT EXISTS `icon_type` ENUM('emoji','image','svg') DEFAULT 'emoji' AFTER `icon`;

-- 7. Make sure product_images FK is correct
-- First, clean up any orphaned records
DELETE FROM `product_images` WHERE `product_id` NOT IN (SELECT `id` FROM `products`);

-- Drop existing FK if exists
ALTER TABLE `product_images` DROP FOREIGN KEY IF EXISTS `fk_product_images_product`;

-- Add correct FK with CASCADE
ALTER TABLE `product_images`
ADD CONSTRAINT `fk_product_images_product` 
FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- 8. Create referral_program table if not exists
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
  KEY `referral_code` (`referral_code`),
  CONSTRAINT `fk_referral_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_referral_referred` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Add referral_code to users if not exists
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `referral_code` VARCHAR(20) DEFAULT NULL AFTER `email`,
ADD COLUMN IF NOT EXISTS `referred_by` INT(11) DEFAULT NULL AFTER `referral_code`;

-- Generate referral codes for existing users if NULL
UPDATE `users` SET `referral_code` = CONCAT('DORVE', LPAD(id, 6, '0')) WHERE `referral_code` IS NULL OR `referral_code` = '';

-- 10. Make sure orders table has all needed columns
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `delivery_status` VARCHAR(50) DEFAULT 'pending' AFTER `shipping_status`,
ADD COLUMN IF NOT EXISTS `completed_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`,
ADD COLUMN IF NOT EXISTS `can_review` TINYINT(1) DEFAULT 1 AFTER `completed_at`;

-- =====================================================
-- DONE! All critical database issues should be fixed
-- =====================================================
