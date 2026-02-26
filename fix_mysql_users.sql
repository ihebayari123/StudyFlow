-- Fix MySQL Connection Issues
-- Run this script in MySQL command line or phpMyAdmin SQL tab

-- Option 1: Allow root user to connect without password (for development only)
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;

-- Option 2: Set a password for root user (more secure)
-- Uncomment the lines below and replace 'YourSecurePassword' with your chosen password
-- ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'YourSecurePassword';
-- FLUSH PRIVILEGES;

-- Option 3: Create a new user for your application
-- Uncomment the lines below to create a dedicated user
-- CREATE USER IF NOT EXISTS 'studyflow_user'@'localhost' IDENTIFIED BY 'studyflow_pass';
-- GRANT ALL PRIVILEGES ON studyflow.* TO 'studyflow_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Fix for 'pma' user (phpMyAdmin control user)
-- Only needed if you want phpMyAdmin advanced features
-- CREATE USER IF NOT EXISTS 'pma'@'localhost' IDENTIFIED BY '';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON phpmyadmin.* TO 'pma'@'localhost';
-- FLUSH PRIVILEGES;

-- Verify users
SELECT User, Host, plugin FROM mysql.user WHERE User IN ('root', 'pma');
