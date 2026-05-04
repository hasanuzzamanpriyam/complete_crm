ALTER TABLE `tblserver_hostings`
  MODIFY `renew` ENUM('manual','automatic') NOT NULL DEFAULT 'manual';
