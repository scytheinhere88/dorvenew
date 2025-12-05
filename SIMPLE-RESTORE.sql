-- =====================================================
-- SUPER SIMPLE DORVE DATABASE RESTORATION
-- No procedures, no triggers first - just tables
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- Drop everything
DROP TABLE IF EXISTS tier_upgrades;
DROP TABLE IF EXISTS order_vouchers;
DROP TABLE IF EXISTS referral_rewards;
DROP TABLE IF EXISTS referral_settings;
DROP TABLE IF EXISTS topups;
DROP TABLE IF EXISTS order_timeline;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS vouchers;
DROP TABLE IF EXISTS shipping_methods;
DROP TABLE IF EXISTS wallet_transactions;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS cms_pages;
DROP TABLE IF EXISTS users;

-- 1. USERS
CREATE TABLE users (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  phone varchar(20) DEFAULT NULL,
  phone_verified tinyint(1) DEFAULT 0,
  address text,
  role enum('customer','admin') DEFAULT 'customer',
  wallet_balance decimal(15,2) DEFAULT 0.00,
  referral_code varchar(20) DEFAULT NULL,
  referred_by int(11) DEFAULT NULL,
  total_referrals int(11) DEFAULT 0,
  tier enum('bronze','silver','gold','platinum','vvip') DEFAULT 'bronze',
  total_topup_amount decimal(15,2) DEFAULT 0.00,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY email (email),
  UNIQUE KEY referral_code (referral_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CATEGORIES
CREATE TABLE categories (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  slug varchar(255) NOT NULL,
  description text,
  image varchar(255),
  parent_id int(11),
  is_active tinyint(1) DEFAULT 1,
  sort_order int(11) DEFAULT 0,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. PRODUCTS (NO FOREIGN KEYS!)
CREATE TABLE products (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  slug varchar(255) NOT NULL,
  description text,
  price decimal(10,2) NOT NULL,
  discount_price decimal(10,2),
  category_id int(11),
  gender enum('men','women','unisex') DEFAULT 'unisex',
  is_new tinyint(1) DEFAULT 0,
  is_featured tinyint(1) DEFAULT 0,
  is_active tinyint(1) DEFAULT 1,
  stock int(11) DEFAULT 0,
  sold_count int(11) DEFAULT 0,
  image varchar(255),
  images text,
  meta_title varchar(255),
  meta_description text,
  meta_keywords varchar(255),
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. PRODUCT VARIANTS
CREATE TABLE product_variants (
  id int(11) NOT NULL AUTO_INCREMENT,
  product_id int(11) NOT NULL,
  color varchar(50),
  size varchar(20),
  sku varchar(100),
  stock int(11) DEFAULT 0,
  price_adjustment decimal(10,2) DEFAULT 0.00,
  is_active tinyint(1) DEFAULT 1,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. ORDERS
CREATE TABLE orders (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  order_number varchar(50),
  total_amount decimal(10,2) NOT NULL,
  status enum('pending','processing','shipping','delivered','cancelled') DEFAULT 'pending',
  payment_method varchar(50) DEFAULT 'wallet',
  payment_status varchar(50) DEFAULT 'pending',
  shipping_method varchar(100),
  shipping_cost decimal(10,2) DEFAULT 0.00,
  courier varchar(50),
  tracking_number varchar(100),
  estimated_delivery_days int(11),
  estimated_delivery_date date,
  shipping_notes text,
  cancelled_reason text,
  cancelled_at timestamp NULL,
  shipping_address text,
  customer_name varchar(255),
  customer_phone varchar(20),
  customer_email varchar(255),
  notes text,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ORDER ITEMS
CREATE TABLE order_items (
  id int(11) NOT NULL AUTO_INCREMENT,
  order_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  variant_id int(11),
  product_name varchar(255) NOT NULL,
  size varchar(20),
  color varchar(50),
  qty int(11) NOT NULL,
  price decimal(10,2) NOT NULL,
  total decimal(10,2) NOT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. ORDER TIMELINE
CREATE TABLE order_timeline (
  id int(11) NOT NULL AUTO_INCREMENT,
  order_id int(11) NOT NULL,
  status varchar(50) NOT NULL,
  title varchar(255) NOT NULL,
  description text,
  created_by int(11),
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. CART ITEMS
CREATE TABLE cart_items (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11),
  session_id varchar(255),
  product_id int(11) NOT NULL,
  variant_id int(11),
  qty int(11) DEFAULT 1,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. ADDRESSES
CREATE TABLE addresses (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  recipient_name varchar(255) NOT NULL,
  phone varchar(20) NOT NULL,
  full_address text NOT NULL,
  city varchar(100),
  province varchar(100),
  postal_code varchar(10),
  is_default tinyint(1) DEFAULT 0,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. VOUCHERS
CREATE TABLE vouchers (
  id int(11) NOT NULL AUTO_INCREMENT,
  code varchar(50) NOT NULL,
  type enum('percentage','fixed','free_shipping') DEFAULT 'percentage',
  category enum('discount','free_shipping') DEFAULT 'discount',
  value decimal(10,2) NOT NULL,
  min_purchase decimal(10,2) DEFAULT 0.00,
  max_discount decimal(10,2),
  usage_limit int(11),
  used_count int(11) DEFAULT 0,
  valid_from date,
  valid_until date,
  target_tier enum('all','bronze','silver','gold','platinum','vvip') DEFAULT 'all',
  is_referral_reward tinyint(1) DEFAULT 0,
  is_active tinyint(1) DEFAULT 1,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. ORDER VOUCHERS
CREATE TABLE order_vouchers (
  id int(11) NOT NULL AUTO_INCREMENT,
  order_id int(11) NOT NULL,
  voucher_id int(11) NOT NULL,
  voucher_code varchar(50) NOT NULL,
  voucher_type enum('percentage','fixed','free_shipping') NOT NULL,
  voucher_category enum('discount','free_shipping') NOT NULL,
  discount_amount decimal(10,2) DEFAULT 0.00,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. SHIPPING METHODS
CREATE TABLE shipping_methods (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  description text,
  cost decimal(10,2) DEFAULT 0.00,
  estimated_days varchar(50),
  is_active tinyint(1) DEFAULT 1,
  sort_order int(11) DEFAULT 0,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. WALLET TRANSACTIONS
CREATE TABLE wallet_transactions (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  type enum('topup','payment','refund') NOT NULL,
  amount decimal(10,2) NOT NULL,
  balance_before decimal(10,2) NOT NULL,
  balance_after decimal(10,2) NOT NULL,
  description text,
  reference_id int(11),
  status varchar(50) DEFAULT 'completed',
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. TOPUPS
CREATE TABLE topups (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  amount decimal(15,2) NOT NULL,
  payment_method varchar(50),
  transaction_id varchar(255),
  status enum('pending','completed','failed','cancelled') DEFAULT 'pending',
  notes text,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  completed_at timestamp NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. REFERRAL REWARDS
CREATE TABLE referral_rewards (
  id int(11) NOT NULL AUTO_INCREMENT,
  referrer_id int(11) NOT NULL,
  referred_id int(11) NOT NULL,
  topup_id int(11),
  topup_amount decimal(15,2) DEFAULT 0.00,
  commission_percent decimal(5,2) DEFAULT 5.00,
  reward_value decimal(15,2) DEFAULT 0.00,
  status enum('pending','completed','cancelled') DEFAULT 'pending',
  completed_at timestamp NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. REFERRAL SETTINGS
CREATE TABLE referral_settings (
  id int(11) NOT NULL AUTO_INCREMENT,
  setting_key varchar(100) NOT NULL UNIQUE,
  setting_value text NOT NULL,
  description text,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. TIER UPGRADES
CREATE TABLE tier_upgrades (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  from_tier enum('bronze','silver','gold','platinum','vvip') NOT NULL,
  to_tier enum('bronze','silver','gold','platinum','vvip') NOT NULL,
  total_topup_at_upgrade decimal(15,2) NOT NULL,
  upgraded_at timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. SETTINGS
CREATE TABLE settings (
  id int(11) NOT NULL AUTO_INCREMENT,
  setting_key varchar(255) NOT NULL,
  value text,
  type varchar(50) DEFAULT 'text',
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. REVIEWS
CREATE TABLE reviews (
  id int(11) NOT NULL AUTO_INCREMENT,
  product_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  order_id int(11),
  rating int(11) NOT NULL,
  comment text,
  is_approved tinyint(1) DEFAULT 0,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. CMS PAGES
CREATE TABLE cms_pages (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  slug varchar(255) NOT NULL,
  content longtext,
  meta_title varchar(255),
  meta_description text,
  meta_keywords varchar(255),
  is_active tinyint(1) DEFAULT 1,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT DATA
INSERT INTO users (id, name, email, password, role, wallet_balance, tier) VALUES
(1, 'Admin Dorve 1', 'admin1@dorve.co', '$2y$10$YLGKjvKFZ8sEWRx1s.q8eORnXKzQZEZ8pYH5KQxC3qvKJ6PFYz9yq', 'admin', 0.00, 'vvip'),
(2, 'Admin Dorve 2', 'admin2@dorve.co', '$2y$10$AboKGbzWQCHx1lLLrTaRu.1aSLPxz8p3yxJKRqLZT8dxVmTq0AKwy', 'admin', 0.00, 'vvip');

INSERT INTO categories (id, name, slug, description, sort_order, is_active) VALUES
(1, 'T-Shirts', 't-shirts', 'Comfortable t-shirts', 1, 1),
(2, 'Hoodies', 'hoodies', 'Cozy hoodies', 2, 1),
(3, 'Jeans', 'jeans', 'Trendy jeans', 3, 1),
(4, 'Dresses', 'dresses', 'Beautiful dresses', 4, 1),
(5, 'Jackets', 'jackets', 'Stylish jackets', 5, 1),
(6, 'Accessories', 'accessories', 'Fashion accessories', 6, 1),
(7, 'Shoes', 'shoes', 'Trendy footwear', 7, 1),
(8, 'Bags', 'bags', 'Stylish bags', 8, 1);

INSERT INTO shipping_methods (id, name, description, cost, estimated_days, sort_order, is_active) VALUES
(1, 'Regular Shipping', 'Standard delivery 3-5 days', 15000.00, '3-5 days', 1, 1),
(2, 'Express Shipping', 'Fast delivery 1-2 days', 25000.00, '1-2 days', 2, 1),
(3, 'Free Shipping', 'Free for orders above Rp 500,000', 0.00, '5-7 days', 3, 1),
(4, 'Same Day Delivery', 'Same day (Jakarta only)', 50000.00, 'Same day', 4, 1);

INSERT INTO vouchers (code, type, category, value, min_purchase, target_tier, valid_from, valid_until, is_active) VALUES
('WELCOME10', 'percentage', 'discount', 10.00, 100000.00, 'all', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1),
('FREESHIP50K', 'free_shipping', 'free_shipping', 0, 50000.00, 'all', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1),
('SILVER20', 'percentage', 'discount', 20.00, 200000.00, 'silver', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1),
('GOLD25', 'percentage', 'discount', 25.00, 300000.00, 'gold', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1),
('VVIP40', 'percentage', 'discount', 40.00, 1000000.00, 'vvip', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1);

INSERT INTO referral_settings (setting_key, setting_value, description) VALUES
('referral_enabled', '1', 'Enable/disable referral system'),
('commission_percent', '5.00', 'Commission percentage'),
('min_topup_for_reward', '100000', 'Minimum topup amount'),
('referral_code_prefix', 'DRV', 'Prefix for referral codes');

INSERT INTO settings (setting_key, value, type) VALUES
('store_name', 'Dorve House', 'text'),
('store_email', 'info@dorve.co', 'text'),
('store_phone', '081377378859', 'text'),
('store_address', 'Jakarta, Indonesia', 'text'),
('currency', 'IDR', 'text'),
('currency_symbol', 'Rp', 'text'),
('whatsapp_number', '6281377378859', 'text');

INSERT INTO cms_pages (title, slug, content, meta_title, is_active) VALUES
('About Us', 'about-us', '<h1>Tentang Dorve House</h1>', 'About Us', 1),
('Privacy Policy', 'privacy-policy', '<h1>Privacy Policy</h1>', 'Privacy Policy', 1),
('Terms & Conditions', 'terms-conditions', '<h1>Terms</h1>', 'Terms', 1);

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'SUCCESS: All 20 tables created!' as message;
