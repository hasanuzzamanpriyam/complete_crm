-- Migration for Automatic Renewal support
-- 1. Add renewal columns to hosting and domain tables
ALTER TABLE `tblhostings`
  ADD COLUMN `renew` ENUM('manual','automatic') NOT NULL DEFAULT 'manual' AFTER `status`,
  ADD COLUMN `last_renewed_at` DATETIME NULL AFTER `renew`;

ALTER TABLE `tbldomains`
  ADD COLUMN `renew` ENUM('manual','automatic') NOT NULL DEFAULT 'manual' AFTER `auto_renewal`,
  ADD COLUMN `last_renewed_at` DATETIME NULL AFTER `renew`;

-- 2. Optional indexes to speed up renewal scans
CREATE INDEX idx_hosting_renew_expiry ON tblhostings (renew, expiry_date);
CREATE INDEX idx_domain_renew_expiry ON tbldomains (renew, expiry_date);

-- 3. Populate existing rows
UPDATE tbldomains SET renew = CASE WHEN auto_renewal = 1 THEN 'automatic' ELSE 'manual' END;
UPDATE tblhostings SET renew = 'manual';
