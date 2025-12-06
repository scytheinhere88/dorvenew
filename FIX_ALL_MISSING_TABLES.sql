-- =====================================================
-- DORVE.ID - COMPLETE DATABASE MIGRATION SQL
-- Run this SQL di phpMyAdmin untuk fix semua issues
-- =====================================================

-- 1. TABLE: settings (untuk admin settings page)
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(50) DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLE: payment_methods (untuk payment settings page)
-- Drop and recreate untuk memastikan struktur yang benar
DROP TABLE IF EXISTS `payment_methods`;

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `method_name` varchar(50) NOT NULL,
  `type` enum('bank_transfer','qris','e_wallet','cod') NOT NULL DEFAULT 'bank_transfer',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `method_name` (`method_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default payment methods
INSERT INTO `payment_methods` (`id`, `name`, `method_name`, `type`, `is_active`, `display_order`) VALUES
(1, 'Transfer Bank Manual', 'bank_transfer', 'bank_transfer', 1, 1),
(2, 'QRIS (via Midtrans)', 'qris', 'qris', 1, 2),
(3, 'E-Wallet (via Midtrans)', 'e_wallet', 'e_wallet', 1, 3),
(4, 'Bayar di Tempat (COD)', 'cod', 'cod', 0, 4);

-- 3. TABLE: payment_gateway_settings (untuk gateway config)
CREATE TABLE IF NOT EXISTS `payment_gateway_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gateway_name` varchar(50) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `merchant_id` varchar(100) DEFAULT NULL,
  `client_id` varchar(255) DEFAULT NULL,
  `client_secret` varchar(255) DEFAULT NULL,
  `is_production` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `gateway_name` (`gateway_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default gateways jika belum ada
INSERT IGNORE INTO `payment_gateway_settings` (`id`, `gateway_name`, `is_production`, `is_active`) VALUES
(1, 'midtrans', 0, 0),
(2, 'paypal', 0, 0);

-- 4. TABLE: system_settings (untuk payment settings additional config)
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default system settings
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('whatsapp_admin', '628123456789', 'WhatsApp admin number for payment confirmations'),
('min_topup_amount', '10000', 'Minimum topup amount in IDR'),
('unique_code_min', '100', 'Minimum unique code for bank transfers'),
('unique_code_max', '999', 'Maximum unique code for bank transfers'),
('whatsapp_message', 'Halo Admin, saya sudah melakukan transfer untuk topup wallet. Mohon di cek ya!', 'WhatsApp message template');

-- 5. TABLE: bank_accounts (sudah ada tapi pastikan strukturnya benar)
CREATE TABLE IF NOT EXISTS `bank_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_name` varchar(100) NOT NULL,
  `bank_code` varchar(20) DEFAULT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABLE: banners (untuk homepage banners & marquee)
CREATE TABLE IF NOT EXISTS `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `banner_type` enum('hero','popup','marquee') NOT NULL DEFAULT 'hero',
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `cta_text` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Add missing columns to products table
ALTER TABLE `products` 
ADD COLUMN IF NOT EXISTS `is_featured` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`,
ADD COLUMN IF NOT EXISTS `is_best_seller` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_featured`,
ADD COLUMN IF NOT EXISTS `is_new` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_best_seller`;

-- 8. Add missing columns to categories table
ALTER TABLE `categories` 
ADD COLUMN IF NOT EXISTS `icon` varchar(255) DEFAULT NULL AFTER `image`;

-- 9. TABLE: product_images (untuk multiple product images)
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- VERIFICATION QUERIES
-- Run these to check if tables are created correctly
-- =====================================================

-- Check if all tables exist
SELECT 'settings' as table_name, COUNT(*) as row_count FROM settings
UNION ALL
SELECT 'payment_methods', COUNT(*) FROM payment_methods
UNION ALL
SELECT 'payment_gateway_settings', COUNT(*) FROM payment_gateway_settings
UNION ALL
SELECT 'system_settings', COUNT(*) FROM system_settings
UNION ALL
SELECT 'bank_accounts', COUNT(*) FROM bank_accounts
UNION ALL
SELECT 'banners', COUNT(*) FROM banners
UNION ALL
SELECT 'product_images', COUNT(*) FROM product_images;

-- =====================================================
-- SELESAI! Semua table sudah di-create dengan safe
-- Tidak akan error jika table sudah ada sebelumnya
-- =====================================================
