-- Migration 0011: Password reset code table (6-digit verification codes).
-- The forgot/reset password flow uses this table.

CREATE TABLE IF NOT EXISTS Password_Reset_Code (
    code_ID INT AUTO_INCREMENT PRIMARY KEY,
    user_ID INT NOT NULL,
    code_hash CHAR(64) NOT NULL,
    expires_At DATETIME NOT NULL,
    requested_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    attempts INT NOT NULL DEFAULT 0,
    used_At DATETIME NULL,
    UNIQUE KEY uq_password_reset_code_user (user_ID),
    FOREIGN KEY (user_ID) REFERENCES User(user_ID) ON DELETE CASCADE
) ENGINE=InnoDB;
