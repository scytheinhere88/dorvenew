-- =====================================================
-- ADDITIONAL TABLES - Run this in phpMyAdmin
-- For Settings, Payment Gateway, Bank Accounts
-- =====================================================

-- 1. SETTINGS TABLE (for store config)
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
  `setting_key` VARCHAR(255) NOT NULL UNIQUE,
  `value` TEXT,
  `type` VARCHAR(50) DEFAULT 'text',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. PAYMENT_GATEWAY_SETTINGS TABLE
CREATE TABLE IF NOT EXISTS `payment_gateway_settings` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default payment gateways
INSERT IGNORE INTO `payment_gateway_settings` (`gateway_name`, `display_name`, `is_active`) VALUES
('midtrans', 'Midtrans Payment Gateway', 1),
('manual_transfer', 'Manual Bank Transfer', 1);

-- 3. BANK_ACCOUNTS TABLE
CREATE TABLE IF NOT EXISTS `bank_accounts` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample bank account (optional)
INSERT IGNORE INTO `bank_accounts` (`bank_name`, `account_number`, `account_name`, `is_active`, `display_order`) VALUES
('BCA', '1234567890', 'DORVE INDONESIA', 1, 1);

-- =====================================================
-- DONE! Settings tables ready.
-- =====================================================
