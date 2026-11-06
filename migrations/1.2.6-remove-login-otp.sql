-- 1.2.6 — Remove the login OTP settings and columns.
--
-- The login OTP step is gone. It saved a code but never sent it, so no
-- instance used it. The config list showed these settings, including the
-- WhatsApp token, so the rows go as well.
--
-- Safe to re-run: the DELETE matches nothing the second time, and bin/migrate
-- skips a DROP COLUMN whose column is already gone.

DELETE FROM `global_config` WHERE `global_name` IN ('login_otp', 'whatsapp_sid', 'whatsapp_token');

ALTER TABLE `users` DROP COLUMN `otp`;
ALTER TABLE `users` DROP COLUMN `otp_generated_datetime`;
