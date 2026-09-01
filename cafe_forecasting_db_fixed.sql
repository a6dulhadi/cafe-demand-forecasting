/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- 1. users (no dependencies)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','owner','staff') COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `status`, `created_at`) VALUES
  (1, 'System Admin', 'admin@cafe.com', '$2y$10$VnArZQn3Qn6reFlKktcldeofVX6KBQqSkc31CReq1nqYKL1uZp8rW', 'admin', 'active', '2026-06-27 04:44:22'),
  (2, 'Cafe Owner', 'owner@cafe.com', '$2y$10$8xMHYhHIGDgRlyGf1TpOAukf5mIDwXxodpen9kgEorCmHdexfWLle', 'owner', 'active', '2026-06-27 04:44:22'),
  (3, 'Cafe Staff', 'staff@cafe.com', '$2y$10$Z.5IHnfkCX0B5lzZ2jKzqeuULhN/VK02JZY9XV1Ycxqcf0X3O3awm', 'staff', 'active', '2026-06-27 04:44:22'),
  (4, 'ali', 'staff1@cafe.com', '$2y$10$mbqpvZSm9gprpQdNuIDTZuAYJDz34tP3rWFznS/XvZmDcXUjLUopa', 'staff', 'active', '2026-07-16 06:17:41');

-- 2. menu_items (no dependencies)
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `menu_items` (`id`, `item_name`, `category`, `price`, `status`, `created_at`) VALUES
  (2, 'nasi lemak', 'Food', 5.00, 'active', '2026-06-28 00:23:48'),
  (3, 'chicken chop', 'Food', 12.00, 'active', '2026-06-28 00:24:03'),
  (4, 'milo ais', 'Drink', 3.50, 'inactive', '2026-06-28 00:24:20'),
  (5, 'french fries', 'Snack', 6.01, 'active', '2026-06-28 00:24:36'),
  (6, 'ice cream', 'Dessert', 5.00, 'active', '2026-07-09 03:54:37');

-- 3. ingredients (no dependencies)
CREATE TABLE IF NOT EXISTS `ingredients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ingredient_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `current_stock` decimal(10,2) DEFAULT '0.00',
  `minimum_stock` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ingredients` (`id`, `ingredient_name`, `unit`, `current_stock`, `minimum_stock`, `created_at`) VALUES
  (1, 'coffee powder', 'g', 5000.00, 1000.00, '2026-06-28 00:34:51'),
  (2, 'milk', 'ml', 20000.00, 3000.00, '2026-06-28 00:35:14'),
  (3, 'rice', 'kg', 15.00, 3.00, '2026-06-28 00:35:46'),
  (4, 'milo powder', 'g', 3000.00, 500.00, '2026-06-28 00:36:06'),
  (5, 'sugar', 'g', 5000.00, 1000.00, '2026-06-28 00:36:25'),
  (6, 'cooking oil', 'L', 10.00, 2.00, '2026-06-28 00:36:41');

-- 4. activity_logs (needs: users)
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `details` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. sales_uploads (needs: users)
CREATE TABLE IF NOT EXISTS `sales_uploads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uploaded_by` int NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `total_rows` int DEFAULT '0',
  `valid_rows` int DEFAULT '0',
  `invalid_rows` int DEFAULT '0',
  `upload_status` enum('success','failed','partial') COLLATE utf8mb4_general_ci DEFAULT 'success',
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `sales_uploads_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sales_uploads` (`id`, `uploaded_by`, `file_name`, `total_rows`, `valid_rows`, `invalid_rows`, `upload_status`, `uploaded_at`) VALUES
  (1, 2, 'Test.csv', 10, 0, 10, 'failed', '2026-07-15 08:30:41'),
  (2, 2, 'Test.csv', 10, 0, 10, 'failed', '2026-07-15 08:36:17'),
  (3, 2, 'Test.csv', 10, 0, 10, 'failed', '2026-07-15 08:36:40'),
  (4, 2, 'Test.csv', 10, 0, 10, 'failed', '2026-07-15 08:40:26'),
  (5, 2, 'Test.csv', 10, 0, 10, 'failed', '2026-07-15 08:40:44'),
  (6, 2, 'Test.csv', 10, 0, 10, 'failed', '2026-07-15 08:43:17'),
  (7, 2, 'Test.csv', 0, 0, 0, 'success', '2026-07-16 01:13:44'),
  (8, 2, 'Test.csv', 10, 3, 7, 'partial', '2026-07-16 01:16:48');

-- 6. sales_records (needs: sales_uploads, menu_items)
CREATE TABLE IF NOT EXISTS `sales_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `upload_id` int DEFAULT NULL,
  `sale_date` date NOT NULL,
  `menu_item_id` int NOT NULL,
  `item_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `quantity_sold` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_sales` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `upload_id` (`upload_id`),
  KEY `menu_item_id` (`menu_item_id`),
  CONSTRAINT `sales_records_ibfk_1` FOREIGN KEY (`upload_id`) REFERENCES `sales_uploads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_records_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sales_records` (`id`, `upload_id`, `sale_date`, `menu_item_id`, `item_name`, `category`, `quantity_sold`, `unit_price`, `total_sales`, `created_at`) VALUES
  (1, 8, '2025-01-01', 2, 'Nasi Lemak', 'Food', 10, 6.50, 65.00, '2026-07-16 01:16:48'),
  (2, 8, '2025-02-01', 4, 'Milo Ais', 'Drink', 9, 3.80, 34.20, '2026-07-16 01:16:48'),
  (3, 8, '2025-04-01', 2, 'Nasi Lemak', 'Food', 16, 6.50, 104.00, '2026-07-16 01:16:48');

-- 7. forecast_results (needs: menu_items)
CREATE TABLE IF NOT EXISTS `forecast_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `menu_item_id` int NOT NULL,
  `item_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `forecast_month` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `predicted_quantity` int NOT NULL,
  `model_used` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `menu_item_id` (`menu_item_id`),
  CONSTRAINT `forecast_results_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. ingredient_forecasts (needs: forecast_results, ingredients)
CREATE TABLE IF NOT EXISTS `ingredient_forecasts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `forecast_id` int NOT NULL,
  `ingredient_id` int NOT NULL,
  `ingredient_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `required_quantity` decimal(10,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `forecast_id` (`forecast_id`),
  KEY `ingredient_id` (`ingredient_id`),
  CONSTRAINT `ingredient_forecasts_ibfk_1` FOREIGN KEY (`forecast_id`) REFERENCES `forecast_results` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ingredient_forecasts_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 9. recipes (needs: menu_items, ingredients)
CREATE TABLE IF NOT EXISTS `recipes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `menu_item_id` int NOT NULL,
  `ingredient_id` int NOT NULL,
  `quantity_required` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `menu_item_id` (`menu_item_id`),
  KEY `ingredient_id` (`ingredient_id`),
  CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `recipes` (`id`, `menu_item_id`, `ingredient_id`, `quantity_required`, `created_at`) VALUES
  (1, 3, 1, 0.15, '2026-07-05 06:30:53'),
  (3, 3, 6, 5.00, '2026-07-17 13:19:08'),
  (4, 4, 2, 4.00, '2026-07-17 13:32:08'),
  (5, 2, 2, 1.00, '2026-07-17 13:32:26'),
  (6, 2, 3, 1.00, '2026-07-17 13:32:39');

-- 10. model_results (no dependencies)
CREATE TABLE IF NOT EXISTS `model_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `mae` decimal(10,4) NOT NULL,
  `rmse` decimal(10,4) NOT NULL,
  `r2_score` decimal(10,4) NOT NULL,
  `is_best_model` tinyint(1) DEFAULT '0',
  `trained_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 11. training_history (no dependencies)
CREATE TABLE IF NOT EXISTS `training_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `training_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `total_records` int NOT NULL,
  `training_records` int NOT NULL,
  `testing_records` int NOT NULL,
  `best_model` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `recommendation` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
