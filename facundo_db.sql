-- FACUNDO AUTOMOTIVE SYSTEM 
-- Database Export File
-- Target: MySQL/MariaDB

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET TIME_ZONE = "+08:00";

--
-- Database: `facundo_db`
--
CREATE DATABASE IF NOT EXISTS `facundo_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `facundo_db`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','staff','buyer') DEFAULT 'buyer',
  `phone_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dummy Admin (Password is: admin123)
--
INSERT INTO `users` (`full_name`, `email`, `password_hash`, `role`) VALUES
('Facundo Admin', 'admin@facundo.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year_produced` int(11) NOT NULL,
  `price_php` decimal(15,2) NOT NULL,
  `mileage_km` int(11) NOT NULL,
  `transmission` enum('MT','AT') DEFAULT 'AT',
  `fuel_type` enum('Gasoline','Diesel','Hybrid','Electric') DEFAULT 'Diesel',
  `status` enum('Available','Reserved','Sold','Maintenance') DEFAULT 'Available',
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Sample Inventory
--
INSERT INTO `vehicles` (`make`, `model`, `year_produced`, `price_php`, `mileage_km`, `status`) VALUES
('Toyota', 'Land Cruiser 300', 2024, 5200000.00, 12400, 'Available'),
('Ford', 'Ranger Raptor', 2024, 2350000.00, 5100, 'Reserved');

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `temp` enum('Hot','Warm','Cold') DEFAULT 'Warm',
  `source` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `last_contacted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `vehicle_id` (`vehicle_id`),
  CONSTRAINT `leads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;