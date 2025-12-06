-- =====================================================
-- DORVE.ID - NEW FEATURES DATABASE SETUP
-- Run this SQL in phpMyAdmin or MySQL client
-- =====================================================

-- 1. CREATE BANNERS TABLE (for slider, popup, marquee)
-- =====================================================
CREATE TABLE IF NOT EXISTS `banners` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Data for Banners (Optional)
INSERT INTO `banners` (`banner_type`, `title`, `subtitle`, `image_url`, `link_url`, `cta_text`, `is_active`, `display_order`) VALUES
('slider', 'Koleksi Fashion Terbaru 2024', 'Diskon hingga 50% untuk semua kategori', '/uploads/banners/banner1.jpg', '/pages/all-products.php', 'Belanja Sekarang', 1, 1),
('marquee', 'Selamat Datang di Dorve.id!', 'Gratis Ongkir untuk pembelian di atas Rp 500.000', NULL, NULL, NULL, 1, 1);

-- 2. ADD ICON FIELD TO CATEGORIES TABLE
-- =====================================================
ALTER TABLE `categories` 
ADD COLUMN IF NOT EXISTS `icon` VARCHAR(500) DEFAULT NULL AFTER `name`;

-- Sample Data for Categories Icons (Optional)
-- UPDATE categories SET icon = '👕' WHERE name LIKE '%T-Shirt%';
-- UPDATE categories SET icon = '👗' WHERE name LIKE '%Dress%';
-- UPDATE categories SET icon = '👖' WHERE name LIKE '%Jeans%';

-- 3. ADD FEATURED FIELDS TO PRODUCTS TABLE (if not exists)
-- =====================================================
ALTER TABLE `products` 
ADD COLUMN IF NOT EXISTS `is_featured` TINYINT(1) DEFAULT 0 AFTER `is_active`;

ALTER TABLE `products` 
ADD COLUMN IF NOT EXISTS `is_best_seller` TINYINT(1) DEFAULT 0 AFTER `is_featured`;

ALTER TABLE `products` 
ADD COLUMN IF NOT EXISTS `is_new` TINYINT(1) DEFAULT 0 AFTER `is_best_seller`;

-- Add indexes for performance
ALTER TABLE `products` 
ADD INDEX IF NOT EXISTS `idx_is_featured` (`is_featured`);

ALTER TABLE `products` 
ADD INDEX IF NOT EXISTS `idx_is_best_seller` (`is_best_seller`);

ALTER TABLE `products` 
ADD INDEX IF NOT EXISTS `idx_is_new` (`is_new`);

-- 4. VERIFY PRODUCT_IMAGES TABLE EXISTS
-- =====================================================
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `image_path` VARCHAR(500) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `sort_order` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  KEY `idx_product_id` (`product_id`),
  KEY `idx_is_primary` (`is_primary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- NOTES:
-- =====================================================
-- 
-- BANNERS TABLE:
-- - banner_type: 'slider' (homepage banners 8-10)
-- - banner_type: 'popup' (auto popup banner)
-- - banner_type: 'marquee' (running text below navbar)
--
-- CATEGORIES:
-- - icon: Can be emoji (e.g., '👕') or URL to image
--
-- PRODUCTS:
-- - is_featured: Checkbox "Featured Product" in admin
-- - is_best_seller: Also used for featured (legacy)
-- - is_new: Checkbox "New Collection" in admin
--
-- =====================================================
-- DONE! All tables and fields are ready.
-- =====================================================
