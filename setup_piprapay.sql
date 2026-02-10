-- Manual SQL Setup for PipraPay Integration
-- Run this in your MySQL/MariaDB database
-- This adds PipraPay to your CRM system

-- 1. Add PipraPay to online payment table
INSERT INTO `tbl_online_payment` (`online_payment_id`, `gateway_name`, `icon`, `field_1`, `field_2`, `field_3`, `field_4`, `field_5`, `link`, `modal`) VALUES 
(NULL, 'PipraPay', 'piprapay.png', 'piprapay_api_url', 'piprapay_api_key', 'piprapay_api_secret', 'piprapay_merchant_id', '', 'payment/piprapay', 'Yes');

-- 2. Add PipraPay column to invoices table (if not exists)
ALTER TABLE `tbl_invoices` ADD `allow_piprapay` ENUM('Yes','No') NULL DEFAULT 'Yes' AFTER `allow_tappayment`;

-- 3. Add PipraPay configuration items to config table
INSERT INTO `tbl_config` (`config_key`, `value`, `label`, `type`, `options`, `description`) VALUES 
('piprapay_enabled', 'FALSE', 'PipraPay Enabled', 'checkbox', '', 'Enable or disable PipraPay payment gateway'),
('piprapay_api_url', 'https://payment.yourdomain.com/api/v1', 'PipraPay API URL', 'text', '', 'PipraPay API endpoint URL'),
('piprapay_api_key', '', 'PipraPay API Key', 'text', '', 'Your PipraPay API key'),
('piprapay_api_secret', '', 'PipraPay API Secret', 'text', '', 'Your PipraPay API secret'),
('piprapay_merchant_id', '', 'PipraPay Merchant ID', 'text', '', 'Your PipraPay merchant ID'),
('piprapay_webhook_secret', '', 'PipraPay Webhook Secret', 'text', '', 'Your PipraPay webhook secret for verifying callbacks'),
('piprapay_test_mode', 'TRUE', 'PipraPay Test Mode', 'checkbox', '', 'Enable test mode for PipraPay'),
('piprapay_default_gateway', 'bkash', 'Default Payment Gateway', 'select', 'bkash,bKash,nagad,Nagad,stripe,Stripe', 'Default gateway for PipraPay payments');

-- 4. Update version (optional, if you track version in config)
UPDATE `tbl_config` SET `value` = '6.1.0' WHERE `tbl_config`.`config_key` = 'version';

-- Note: If any of the columns/rows already exist, you may get errors.
-- In that case, use UPDATE instead of INSERT for duplicates.
