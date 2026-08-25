ALTER TABLE users
ADD COLUMN blocked_at DATETIME NULL AFTER last_login;
