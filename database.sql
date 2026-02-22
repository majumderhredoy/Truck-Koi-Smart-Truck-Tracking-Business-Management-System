-- =============================================
-- TRUCK KOI — STRICT 1-TO-1 DATABASE SCHEMA
-- =============================================

USE `tracker`;

-- 1. Create the drivers table
CREATE TABLE IF NOT EXISTS `drivers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `truck_id` INT UNIQUE DEFAULT NULL, -- Enforce 1 Driver = 1 Truck
  `driver_name` VARCHAR(100) NOT NULL,
  `phone_number` VARCHAR(20) NOT NULL,
  `license_number` VARCHAR(50) UNIQUE NOT NULL, -- Strict unique license
  `license_expiry` DATE DEFAULT NULL,
  `driver_image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'on_leave', 'suspended') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_driver` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 2. Create the trucks table
CREATE TABLE IF NOT EXISTS `trucks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `driver_id` INT UNIQUE DEFAULT NULL, -- Enforce 1 Truck = 1 Driver
  `truck_name` VARCHAR(50) NOT NULL,
  `plate_number` VARCHAR(50) UNIQUE NOT NULL, -- Strict unique plate
  `speed` INT DEFAULT 0,
  `fuel` INT DEFAULT 100,
  `location` VARCHAR(255) DEFAULT 'অজানা',
  `lat` DECIMAL(10, 8) DEFAULT 23.8103,
  `lng` DECIMAL(11, 8) DEFAULT 90.4125,
  `status` ENUM('running', 'idle', 'stopped') DEFAULT 'idle',
  `logo_image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_truck` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Add the missing Foreign Key to drivers for full circular mapping
ALTER TABLE `drivers` 
ADD CONSTRAINT `fk_driver_truck` FOREIGN KEY (`truck_id`) REFERENCES `trucks`(`id`) ON DELETE SET NULL;
