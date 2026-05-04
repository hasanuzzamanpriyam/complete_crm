ALTER TABLE `tblserver_hostings`
  ADD COLUMN `renew` ENUM('manual','automatic') NOT NULL DEFAULT 'manual' AFTER `status`,
  ADD COLUMN `last_renewed_at` DATETIME NULL AFTER `renew`;
