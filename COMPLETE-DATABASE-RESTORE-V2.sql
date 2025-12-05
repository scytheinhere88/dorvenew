-- =====================================================
-- DORVE HOUSE E-COMMERCE - COMPLETE DATABASE RESTORATION V2
-- =====================================================
-- Version: 5.0 SAFE MODE
-- Strategy: Create tables first, then add constraints
-- Date: 2025-12-05
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- =====================================================
-- STEP 1: DROP ALL EXISTING OBJECTS
-- =====================================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `tier_upgrades`;
DROP TABLE IF EXISTS `order_vouchers`;
DROP TABLE IF EXISTS `referral_rewards`;
DROP TABLE IF EXISTS `referral_settings`;
DROP TABLE IF EXISTS `topups`;
DROP TABLE IF EXISTS `order_timeline`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart_items`;
DROP TABLE IF EXISTS `addresses`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `product_variants`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `vouchers`;
DROP TABLE IF EXISTS `shipping_methods`;
DROP TABLE IF EXISTS `wallet_transactions`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `cms_pages`;
DROP TABLE IF EXISTS `users`;

DROP VIEW IF EXISTS `order_tracking_view`;
DROP VIEW IF EXISTS `product_inventory_view`;
DROP PROCEDURE IF EXISTS `update_user_tier`;
DROP PROCEDURE IF EXISTS `process_referral_reward`;
DROP TRIGGER IF EXISTS `after_order_status_update`;
DROP TRIGGER IF EXISTS `after_variant_stock_update`;
DROP TRIGGER IF EXISTS `after_variant_insert`;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- STEP 2: CREATE ALL TABLES (NO FOREIGN KEYS YET)
-- =====================================================

-- 1. USERS TABLE
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `phone_verified` tinyint(1) DEFAULT 0,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `wallet_balance` decimal(15,2) DEFAULT 0.00,
  `referral_code` varchar(20) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `total_referrals` int(11) DEFAULT 0,
  `tier` enum('bronze','silver','gold','platinum','vvip') DEFAULT 'bronze',
  `total_topup_amount` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `referral_code` (`referral_code`),
  KEY `idx_role` (`role`),
  KEY `idx_tier` (`tier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CATEGORIES TABLE
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_active` (`is_active`),
  KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. PRODUCTS TABLE
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `gender` enum('men','women','unisex') DEFAULT 'unisex',
  `is_new` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `stock` int(11) DEFAULT 0,
  `sold_count` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `images` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_category` (`category_id`),
  KEY `idx_gender` (`gender`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. PRODUCT VARIANTS TABLE
CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_stock` (`stock`),
  UNIQUE KEY `unique_variant` (`product_id`, `color`, `size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. ORDERS TABLE
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipping','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'wallet',
  `payment_status` varchar(50) DEFAULT 'pending',
  `shipping_method` varchar(100) DEFAULT NULL,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `courier` varchar(50) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `estimated_delivery_days` int(11) DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL,
  `shipping_notes` text DEFAULT NULL,
  `cancelled_reason` text DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ORDER ITEMS TABLE
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_variant` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. ORDER TIMELINE TABLE
CREATE TABLE `order_timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. CART ITEMS TABLE
CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_variant` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. ADDRESSES TABLE
CREATE TABLE `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `full_address` text NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. VOUCHERS TABLE
CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `type` enum('percentage','fixed','free_shipping') DEFAULT 'percentage',
  `category` enum('discount','free_shipping') DEFAULT 'discount',
  `value` decimal(10,2) NOT NULL,
  `min_purchase` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `target_tier` enum('all','bronze','silver','gold','platinum','vvip') DEFAULT 'all',
  `is_referral_reward` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. ORDER VOUCHERS TABLE
CREATE TABLE `order_vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `voucher_code` varchar(50) NOT NULL,
  `voucher_type` enum('percentage','fixed','free_shipping') NOT NULL,
  `voucher_category` enum('discount','free_shipping') NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_voucher` (`voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. SHIPPING METHODS TABLE
CREATE TABLE `shipping_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estimated_days` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. WALLET TRANSACTIONS TABLE
CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('topup','payment','refund') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `balance_before` decimal(10,2) NOT NULL,
  `balance_after` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. TOPUPS TABLE
CREATE TABLE `topups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. REFERRAL REWARDS TABLE
CREATE TABLE `referral_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `referrer_id` int(11) NOT NULL,
  `referred_id` int(11) NOT NULL,
  `topup_id` int(11) DEFAULT NULL,
  `topup_amount` decimal(15,2) DEFAULT 0.00,
  `commission_percent` decimal(5,2) DEFAULT 5.00,
  `reward_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_referrer` (`referrer_id`),
  KEY `idx_referred` (`referred_id`),
  KEY `idx_topup` (`topup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. REFERRAL SETTINGS TABLE
CREATE TABLE `referral_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. TIER UPGRADES TABLE
CREATE TABLE `tier_upgrades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `from_tier` enum('bronze','silver','gold','platinum','vvip') NOT NULL,
  `to_tier` enum('bronze','silver','gold','platinum','vvip') NOT NULL,
  `total_topup_at_upgrade` decimal(15,2) NOT NULL,
  `upgraded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. SETTINGS TABLE
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(50) DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. REVIEWS TABLE
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. CMS PAGES TABLE
CREATE TABLE `cms_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 3: INSERT DEFAULT DATA
-- =====================================================

-- Admin Users
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `wallet_balance`, `tier`) VALUES
(1, 'Admin Dorve 1', 'admin1@dorve.co', '$2y$10$YLGKjvKFZ8sEWRx1s.q8eORnXKzQZEZ8pYH5KQxC3qvKJ6PFYz9yq', 'admin', 0.00, 'vvip'),
(2, 'Admin Dorve 2', 'admin2@dorve.co', '$2y$10$AboKGbzWQCHx1lLLrTaRu.1aSLPxz8p3yxJKRqLZT8dxVmTq0AKwy', 'admin', 0.00, 'vvip');

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `sort_order`, `is_active`) VALUES
(1, 'T-Shirts', 't-shirts', 'Comfortable and stylish t-shirts', 1, 1),
(2, 'Hoodies', 'hoodies', 'Cozy hoodies and sweatshirts', 2, 1),
(3, 'Jeans', 'jeans', 'Trendy jeans and denim', 3, 1),
(4, 'Dresses', 'dresses', 'Beautiful dresses for all occasions', 4, 1),
(5, 'Jackets', 'jackets', 'Stylish jackets and outerwear', 5, 1),
(6, 'Accessories', 'accessories', 'Fashion accessories', 6, 1),
(7, 'Shoes', 'shoes', 'Comfortable and trendy footwear', 7, 1),
(8, 'Bags', 'bags', 'Stylish bags and backpacks', 8, 1);

-- Shipping Methods
INSERT INTO `shipping_methods` (`id`, `name`, `description`, `cost`, `estimated_days`, `sort_order`, `is_active`) VALUES
(1, 'Regular Shipping', 'Standard delivery 3-5 hari kerja', 15000.00, '3-5 days', 1, 1),
(2, 'Express Shipping', 'Fast delivery 1-2 hari kerja', 25000.00, '1-2 days', 2, 1),
(3, 'Free Shipping', 'Gratis ongkir untuk pembelian di atas Rp 500,000', 0.00, '5-7 days', 3, 1),
(4, 'Same Day Delivery', 'Pengiriman di hari yang sama (Jakarta only)', 50000.00, 'Same day', 4, 1);

-- Vouchers
INSERT INTO `vouchers` (`code`, `type`, `category`, `value`, `min_purchase`, `target_tier`, `valid_from`, `valid_until`, `is_active`) VALUES
('WELCOME10', 'percentage', 'discount', 10.00, 100000.00, 'all', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1),
('NEWUSER50K', 'fixed', 'discount', 50000.00, 500000.00, 'all', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1),
('FREESHIP50K', 'free_shipping', 'free_shipping', 0, 50000.00, 'all', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1),
('SILVER20', 'percentage', 'discount', 20.00, 200000.00, 'silver', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1),
('GOLD25', 'percentage', 'discount', 25.00, 300000.00, 'gold', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1),
('PLATINUM30', 'percentage', 'discount', 30.00, 500000.00, 'platinum', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1),
('VVIP40', 'percentage', 'discount', 40.00, 1000000.00, 'vvip', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1),
('VVIPFREE', 'free_shipping', 'free_shipping', 0, 0.00, 'vvip', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1);

-- Referral Settings
INSERT INTO `referral_settings` (`setting_key`, `setting_value`, `description`) VALUES
('referral_enabled', '1', 'Enable/disable referral system'),
('commission_percent', '5.00', 'Commission percentage for referrals'),
('min_topup_for_reward', '100000', 'Minimum topup amount to trigger referral reward'),
('referral_code_prefix', 'DRV', 'Prefix for referral codes');

-- System Settings
INSERT INTO `settings` (`setting_key`, `value`, `type`) VALUES
('store_name', 'Dorve House', 'text'),
('store_email', 'info@dorve.co', 'text'),
('store_phone', '081377378859', 'text'),
('store_address', 'Jakarta, Indonesia', 'text'),
('currency', 'IDR', 'text'),
('currency_symbol', 'Rp', 'text'),
('marquee_text', '🎉 Welcome to Dorve House! Free shipping for orders above Rp 500,000! 🚚', 'text'),
('marquee_enabled', '1', 'boolean'),
('whatsapp_number', '6281377378859', 'text');

-- CMS Pages
INSERT INTO `cms_pages` (`title`, `slug`, `content`, `meta_title`, `is_active`) VALUES
('About Us', 'about-us', '<h1>Tentang Dorve House</h1><p>Dorve House adalah brand fashion lokal yang menghadirkan produk berkualitas.</p>', 'About Dorve House', 1),
('Privacy Policy', 'privacy-policy', '<h1>Kebijakan Privasi</h1><p>Kami menghargai privasi Anda.</p>', 'Privacy Policy', 1),
('Terms & Conditions', 'terms-conditions', '<h1>Syarat & Ketentuan</h1><p>Syarat dan ketentuan penggunaan website.</p>', 'Terms & Conditions', 1),
('Shipping Policy', 'shipping-policy', '<h1>Kebijakan Pengiriman</h1><p>Informasi tentang pengiriman.</p>', 'Shipping Policy', 1),
('FAQ', 'faq', '<h1>FAQ</h1><p>Pertanyaan yang sering diajukan.</p>', 'FAQ', 1);

-- =====================================================
-- STEP 4: ADD FOREIGN KEY CONSTRAINTS
-- =====================================================

ALTER TABLE `products` 
  ADD CONSTRAINT `fk_products_category` 
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

ALTER TABLE `product_variants` 
  ADD CONSTRAINT `fk_variants_product` 
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

ALTER TABLE `orders` 
  ADD CONSTRAINT `fk_orders_user` 
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `order_items` 
  ADD CONSTRAINT `fk_order_items_order` 
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` 
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_variant` 
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

ALTER TABLE `order_timeline` 
  ADD CONSTRAINT `fk_timeline_order` 
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_timeline_user` 
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `cart_items` 
  ADD CONSTRAINT `fk_cart_user` 
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_product` 
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_variant` 
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

ALTER TABLE `addresses` 
  ADD CONSTRAINT `fk_addresses_user` 
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `order_vouchers` 
  ADD CONSTRAINT `fk_order_vouchers_order` 
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_vouchers_voucher` 
  FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE;

ALTER TABLE `wallet_transactions` 
  ADD CONSTRAINT `fk_wallet_user` 
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `topups` 
  ADD CONSTRAINT `fk_topups_user` 
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `referral_rewards` 
  ADD CONSTRAINT `fk_referral_referrer` 
  FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_referral_referred` 
  FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_referral_topup` 
  FOREIGN KEY (`topup_id`) REFERENCES `topups` (`id`) ON DELETE SET NULL;

ALTER TABLE `tier_upgrades` 
  ADD CONSTRAINT `fk_tier_user` 
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `reviews` 
  ADD CONSTRAINT `fk_reviews_product` 
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` 
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_order` 
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

-- =====================================================
-- STEP 5: CREATE STORED PROCEDURES
-- =====================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `update_user_tier`$$
CREATE PROCEDURE `update_user_tier`(IN user_id_param INT)
BEGIN
    DECLARE total_topup DECIMAL(15,2);
    DECLARE current_tier VARCHAR(20);
    DECLARE new_tier VARCHAR(20);

    SELECT COALESCE(SUM(amount), 0) INTO total_topup
    FROM topups
    WHERE user_id = user_id_param AND status = 'completed';

    SELECT tier INTO current_tier FROM users WHERE id = user_id_param;

    SET new_tier = CASE
        WHEN total_topup >= 20000000 THEN 'vvip'
        WHEN total_topup >= 10000000 THEN 'platinum'
        WHEN total_topup >= 3000000 THEN 'gold'
        WHEN total_topup >= 1000000 THEN 'silver'
        ELSE 'bronze'
    END;

    UPDATE users
    SET tier = new_tier, total_topup_amount = total_topup
    WHERE id = user_id_param;

    IF current_tier != new_tier THEN
        INSERT INTO tier_upgrades (user_id, from_tier, to_tier, total_topup_at_upgrade)
        VALUES (user_id_param, current_tier, new_tier, total_topup);
    END IF;
END$$

DROP PROCEDURE IF EXISTS `process_referral_reward`$$
CREATE PROCEDURE `process_referral_reward`(IN topup_id_param INT)
BEGIN
    DECLARE user_id_param INT;
    DECLARE topup_amount_param DECIMAL(15,2);
    DECLARE referrer_id_param INT;
    DECLARE commission_percent_val DECIMAL(5,2);
    DECLARE reward_amount DECIMAL(15,2);
    DECLARE first_topup_count INT;
    DECLARE min_topup_required DECIMAL(15,2);

    SELECT user_id, amount INTO user_id_param, topup_amount_param
    FROM topups
    WHERE id = topup_id_param AND status = 'completed';

    SELECT COUNT(*) INTO first_topup_count
    FROM topups
    WHERE user_id = user_id_param AND status = 'completed';

    IF first_topup_count = 1 THEN
        SELECT referred_by INTO referrer_id_param
        FROM users
        WHERE id = user_id_param;

        IF referrer_id_param IS NOT NULL THEN
            SELECT CAST(setting_value AS DECIMAL(5,2)) INTO commission_percent_val
            FROM referral_settings
            WHERE setting_key = 'commission_percent';

            SELECT CAST(setting_value AS DECIMAL(15,2)) INTO min_topup_required
            FROM referral_settings
            WHERE setting_key = 'min_topup_for_reward';

            IF topup_amount_param >= min_topup_required THEN
                SET reward_amount = (topup_amount_param * commission_percent_val / 100);

                UPDATE referral_rewards
                SET status = 'completed',
                    topup_id = topup_id_param,
                    topup_amount = topup_amount_param,
                    commission_percent = commission_percent_val,
                    reward_value = reward_amount,
                    completed_at = NOW()
                WHERE referred_id = user_id_param AND status = 'pending';

                UPDATE users
                SET wallet_balance = wallet_balance + reward_amount
                WHERE id = referrer_id_param;
            END IF;
        END IF;
    END IF;

    CALL update_user_tier(user_id_param);
END$$

DELIMITER ;

-- =====================================================
-- STEP 6: CREATE TRIGGERS
-- =====================================================

DELIMITER $$

DROP TRIGGER IF EXISTS `after_order_status_update`$$
CREATE TRIGGER `after_order_status_update`
AFTER UPDATE ON `orders`
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO order_timeline (order_id, status, title, description)
        VALUES (
            NEW.id,
            NEW.status,
            CASE NEW.status
                WHEN 'pending' THEN 'Order Placed'
                WHEN 'processing' THEN 'Order Processing'
                WHEN 'shipping' THEN 'Order Shipped'
                WHEN 'delivered' THEN 'Order Delivered'
                WHEN 'cancelled' THEN 'Order Cancelled'
            END,
            CASE NEW.status
                WHEN 'pending' THEN 'Pesanan Anda telah diterima'
                WHEN 'processing' THEN 'Pesanan Anda sedang diproses'
                WHEN 'shipping' THEN CONCAT('Pesanan dikirim via ', COALESCE(NEW.courier, 'kurir'))
                WHEN 'delivered' THEN 'Pesanan telah diterima'
                WHEN 'cancelled' THEN COALESCE(NEW.cancelled_reason, 'Pesanan dibatalkan')
            END
        );
    END IF;
END$$

DROP TRIGGER IF EXISTS `after_variant_stock_update`$$
CREATE TRIGGER `after_variant_stock_update`
AFTER UPDATE ON `product_variants`
FOR EACH ROW
BEGIN
    UPDATE products
    SET stock = (
        SELECT COALESCE(SUM(stock), 0)
        FROM product_variants
        WHERE product_id = NEW.product_id AND is_active = 1
    )
    WHERE id = NEW.product_id;
END$$

DROP TRIGGER IF EXISTS `after_variant_insert`$$
CREATE TRIGGER `after_variant_insert`
AFTER INSERT ON `product_variants`
FOR EACH ROW
BEGIN
    UPDATE products
    SET stock = (
        SELECT COALESCE(SUM(stock), 0)
        FROM product_variants
        WHERE product_id = NEW.product_id AND is_active = 1
    )
    WHERE id = NEW.product_id;
END$$

DELIMITER ;

-- =====================================================
-- STEP 7: CREATE VIEWS
-- =====================================================

CREATE OR REPLACE VIEW `order_tracking_view` AS
SELECT
    o.id,
    o.user_id,
    o.order_number,
    o.total_amount,
    o.status,
    o.courier,
    o.tracking_number,
    o.created_at as order_date,
    u.name as customer_name,
    u.email as customer_email,
    o.shipping_address
FROM orders o
LEFT JOIN users u ON o.user_id = u.id;

CREATE OR REPLACE VIEW `product_inventory_view` AS
SELECT
    p.id as product_id,
    p.name as product_name,
    p.slug,
    p.gender,
    c.name as category_name,
    pv.id as variant_id,
    pv.color,
    pv.size,
    pv.stock,
    pv.sku,
    (p.price + pv.price_adjustment) as variant_price
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN product_variants pv ON p.id = pv.product_id
WHERE p.is_active = 1;

COMMIT;

-- =====================================================
-- SUCCESS!
-- =====================================================

SELECT 
    '✅ DATABASE RESTORATION COMPLETE!' as STATUS,
    '20 Tables Created' as TABLES,
    '2 Stored Procedures' as PROCEDURES,
    '3 Triggers' as TRIGGERS,
    '2 Views' as VIEWS,
    'All Foreign Keys Added' as CONSTRAINTS,
    'Ready to Use!' as READY;
