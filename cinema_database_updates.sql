-- Cinema Database Updates for New Features
-- Minimum spend system, combo system, and user history

-- Add minimum spend to movies table
ALTER TABLE `movies` 
ADD COLUMN `minimum_spend_per_person` DECIMAL(10,2) DEFAULT 500000.00 COMMENT 'Minimum spend per person to get free ticket',
ADD COLUMN `language` VARCHAR(50) DEFAULT 'Vietnamese' COMMENT 'Movie language',
ADD COLUMN `subtitle` VARCHAR(50) DEFAULT 'English' COMMENT 'Subtitle language';

-- Create cinema combos table
CREATE TABLE IF NOT EXISTS `cinema_combos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `movie_id` int(11) DEFAULT NULL COMMENT 'Specific movie this combo is for, NULL for general combos',
  `is_available` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cinema_combos_movie` (`movie_id`),
  KEY `idx_cinema_combos_available` (`is_available`),
  CONSTRAINT `cinema_combos_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cinema combo items table
CREATE TABLE IF NOT EXISTS `cinema_combo_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `combo_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_combo_item` (`combo_id`,`menu_item_id`),
  KEY `idx_combo_items_combo` (`combo_id`),
  KEY `idx_combo_items_menu` (`menu_item_id`),
  CONSTRAINT `cinema_combo_items_ibfk_1` FOREIGN KEY (`combo_id`) REFERENCES `cinema_combos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cinema_combo_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user movie history table
CREATE TABLE IF NOT EXISTS `user_movie_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `combo_id` int(11) DEFAULT NULL,
  `watched_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rating` int(1) DEFAULT NULL CHECK (rating >= 1 AND rating <= 5),
  `review` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_movie_history_user` (`user_id`),
  KEY `idx_user_movie_history_booking` (`booking_id`),
  KEY `idx_user_movie_history_movie` (`movie_id`),
  KEY `idx_user_movie_history_combo` (`combo_id`),
  CONSTRAINT `user_movie_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_movie_history_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_movie_history_ibfk_3` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_movie_history_ibfk_4` FOREIGN KEY (`combo_id`) REFERENCES `cinema_combos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cinema orders table for tracking food/drink orders during cinema sessions
CREATE TABLE IF NOT EXISTS `cinema_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `minimum_spend_met` tinyint(1) DEFAULT 0,
  `tickets_free` tinyint(1) DEFAULT 0,
  `status` enum('pending','confirmed','served','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cinema_orders_booking` (`booking_id`),
  KEY `idx_cinema_orders_user` (`user_id`),
  CONSTRAINT `cinema_orders_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cinema_orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cinema order items table
CREATE TABLE IF NOT EXISTS `cinema_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cinema_order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `special_instructions` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cinema_order_items_order` (`cinema_order_id`),
  KEY `idx_cinema_order_items_menu` (`menu_item_id`),
  CONSTRAINT `cinema_order_items_ibfk_1` FOREIGN KEY (`cinema_order_id`) REFERENCES `cinema_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cinema_order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample cinema combos
INSERT INTO `cinema_combos` (`name`, `description`, `price`, `movie_id`, `is_available`, `sort_order`) VALUES
('Combo Classic', 'Popcorn + 2 nước ngọt + bánh snack', 150000.00, NULL, 1, 1),
('Combo Premium', 'Popcorn lớn + 2 cocktail + sushi set nhỏ', 450000.00, NULL, 1, 2),
('Combo VIP', 'Popcorn + 2 cocktail premium + sashimi set', 650000.00, NULL, 1, 3),
('Avengers Special', 'Combo đặc biệt cho phim Avengers với cocktail themed', 500000.00, 1, 1, 4),
('Inception Combo', 'Combo phù hợp với phim Inception - cocktail phức tạp', 480000.00, 2, 1, 5);

-- Insert combo items (assuming some menu items exist)
INSERT INTO `cinema_combo_items` (`combo_id`, `menu_item_id`, `quantity`, `sort_order`) VALUES
-- Combo Classic (assuming menu items with IDs 1-10 exist)
(1, 1, 1, 1), -- Sushi Set Premium
(1, 4, 2, 2), -- Saigon Sunset cocktail x2

-- Combo Premium  
(2, 1, 1, 1), -- Sushi Set Premium
(2, 5, 2, 2), -- Izakaya Old Fashioned x2

-- Combo VIP
(3, 2, 1, 1), -- Sashimi Set Deluxe
(3, 6, 2, 2), -- Cinema Martini x2

-- Avengers Special
(4, 7, 2, 1), -- 419 Special x2
(4, 1, 1, 2), -- Sushi Set Premium

-- Inception Combo
(5, 5, 2, 1), -- Izakaya Old Fashioned x2
(5, 2, 1, 2); -- Sashimi Set Deluxe

-- Update existing movies with minimum spend and language info
UPDATE `movies` SET 
  `minimum_spend_per_person` = 500000.00,
  `language` = 'English',
  `subtitle` = 'Vietnamese'
WHERE `id` IN (1, 2, 3, 4, 5, 6, 7);

-- Update some movies with different minimum spend
UPDATE `movies` SET `minimum_spend_per_person` = 600000.00 WHERE `id` IN (1, 3); -- Avengers and Dark Knight
UPDATE `movies` SET `minimum_spend_per_person` = 400000.00 WHERE `id` IN (6, 7); -- Forrest Gump and Shawshank
