-- Fix PipraPay Icon Path
-- This updates the icon path to correctly point to the piprapay.png image

UPDATE `tbl_online_payment` 
SET `icon` = 'asset/images/payment_logo/piprapay.png' 
WHERE `gateway_name` = 'PipraPay';
