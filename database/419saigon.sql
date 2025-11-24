-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th9 03, 2025 lúc 03:03 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `419saigon`
--

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `available_menu_items`
-- (See below for the actual view)
--
CREATE TABLE `available_menu_items` (
`id` int(11)
,`name` varchar(200)
,`description` text
,`price` decimal(10,2)
,`category_id` int(11)
,`image` varchar(255)
,`ingredients` text
,`is_available` tinyint(1)
,`is_featured` tinyint(1)
,`sort_order` int(11)
,`created_at` timestamp
,`updated_at` timestamp
,`category_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `available_menu_sets`
-- (See below for the actual view)
--
CREATE TABLE `available_menu_sets` (
`id` int(11)
,`name` varchar(200)
,`description` text
,`price` decimal(10,2)
,`image` varchar(255)
,`is_available` tinyint(1)
,`sort_order` int(11)
,`created_at` timestamp
,`updated_at` timestamp
,`items_description` mediumtext
);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_type` enum('cinema','cocktail') NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `guests` int(11) NOT NULL,
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `bookings`
--

-- Sample izakaya booking removed per client request

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('food','drink','movie') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `type`, `status`, `created_at`) VALUES
(1, 'Sushi', 'Traditional Japanese sushi', 'food', 'active', '2025-08-21 17:08:30'),
(2, 'Sashimi', 'Fresh raw fish slices', 'food', 'active', '2025-08-21 17:08:30'),
(3, 'Tempura', 'Battered and fried dishes', 'food', 'active', '2025-08-21 17:08:30'),
(4, 'Signature Cocktails', 'Our unique cocktail creations', 'drink', 'active', '2025-08-21 17:08:30'),
(5, 'Classic Cocktails', 'Traditional cocktail recipes', 'drink', 'active', '2025-08-21 17:08:30'),
(6, 'Mocktails', 'Non-alcoholic beverages', 'drink', 'active', '2025-08-21 17:08:30'),
(7, 'Action Movies', 'Action and adventure films', 'movie', 'active', '2025-08-21 17:08:30'),
(8, 'Drama Movies', 'Dramatic and emotional films', 'movie', 'active', '2025-08-21 17:08:30'),
(9, 'Comedy Movies', 'Funny and entertaining films', 'movie', 'active', '2025-08-21 17:08:30'),
(10, 'Khai vị', 'Các món khai vị truyền thống Nhật Bản', 'food', 'active', '2025-08-27 03:56:56'),
(11, 'Sushi', 'Sushi tươi ngon được làm thủ công', 'food', 'active', '2025-08-27 03:56:56'),
(12, 'Sashimi', 'Sashimi tươi ngon từ hải sản cao cấp', 'food', 'active', '2025-08-27 03:56:56'),
(13, 'Món chính', 'Các món chính đặc trưng Nhật Bản', 'food', 'active', '2025-08-27 03:56:56'),
(14, 'Tempura', 'Tempura giòn rụm với bột chiên đặc biệt', 'food', 'active', '2025-08-27 03:56:56'),
(15, 'Tráng miệng', 'Tráng miệng truyền thống Nhật Bản', 'food', 'active', '2025-08-27 03:56:56'),
(16, 'Sake', 'Sake cao cấp từ các vùng nổi tiếng Nhật Bản', 'drink', 'active', '2025-08-27 03:56:56');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cinema_bookings`
--

CREATE TABLE `cinema_bookings` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `movie_preference` varchar(200) DEFAULT NULL,
  `duration_hours` int(11) DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cinema_rooms`
--

CREATE TABLE `cinema_rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_hour` decimal(10,2) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cinema_rooms`
--

INSERT INTO `cinema_rooms` (`id`, `name`, `capacity`, `description`, `price_per_hour`, `is_available`, `created_at`) VALUES
(1, 'Room A', 8, 'Phòng chiếu riêng cao cấp với ghế VIP', 500000.00, 1, '2025-08-21 17:08:31'),
(2, 'Room B', 6, 'Phòng chiếu nhỏ ấm cúng', 400000.00, 1, '2025-08-21 17:08:31'),
(3, 'Room C', 10, 'Phòng chiếu lớn cho nhóm', 600000.00, 1, '2025-08-21 17:08:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `newsletter_subscription` tinyint(1) DEFAULT 0,
  `status` enum('new','read','replied','archived') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `food_images`
--

CREATE TABLE `food_images` (
  `id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `alt_text` varchar(200) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `food_images`
--

INSERT INTO `food_images` (`id`, `menu_item_id`, `image_url`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES
(1, 1, 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=800&h=600&fit=crop', 'Sushi Set Premium - Main Image', 1, 1, '2025-08-31 14:58:31'),
(2, 1, 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=800&h=600&fit=crop', 'Sushi Set Premium - Detail 1', 0, 2, '2025-08-31 14:58:31'),
(3, 1, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop', 'Sushi Set Premium - Detail 2', 0, 3, '2025-08-31 14:58:31'),
(4, 2, 'https://images.unsplash.com/photo-1553621042-f6e147245754?w=800&h=600&fit=crop', 'Sashimi Set Deluxe - Main Image', 1, 1, '2025-08-31 14:58:31'),
(5, 2, 'https://images.unsplash.com/photo-1565299507177-b0ac66763828?w=800&h=600&fit=crop', 'Sashimi Set Deluxe - Detail 1', 0, 2, '2025-08-31 14:58:31'),
(6, 2, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop', 'Sashimi Set Deluxe - Detail 2', 0, 3, '2025-08-31 14:58:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `food_reviews`
--

CREATE TABLE `food_reviews` (
  `id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `food_reviews`
--

INSERT INTO `food_reviews` (`id`, `menu_item_id`, `user_id`, `customer_name`, `rating`, `comment`, `is_approved`, `created_at`) VALUES
(1, 1, NULL, 'Nguyễn Văn A', 5, 'Cá hồi rất tươi, cơm sushi dẻo thơm. Rất đáng giá!', 1, '2025-08-31 14:58:31'),
(2, 1, NULL, 'Trần Thị B', 4, 'Ngon nhưng hơi đắt so với các quán khác.', 1, '2025-08-31 14:58:31'),
(3, 2, NULL, 'Lê Văn C', 5, 'Set sashimi tuyệt vời! Cá rất tươi và đa dạng.', 1, '2025-08-31 14:58:31'),
(4, 2, NULL, 'Phạm Thị D', 5, 'Đắt nhưng xứng đáng. Chất lượng cao cấp!', 1, '2025-08-31 14:58:31'),
(6, 4, NULL, 'Vũ Thị F', 4, 'Cá ngừ tươi, giá hợp lý.', 1, '2025-08-31 14:58:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `ingredients` text DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `menu_items`
--

(INSERT INTO `menu_items` (`id`, `name`, `description`, `price`, `category_id`, `image`, `ingredients`, `is_available`, `is_featured`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Sushi Set Premium', 'Bộ sushi cao cấp với 12 miếng sushi đa dạng', 850000.00, 1, NULL, 'Cá hồi, cá ngừ, tôm, cơm sushi, rong biển', 1, 1, 0, '2025-08-21 17:08:30', '2025-08-21 17:08:30'),
(2, 'Sashimi Set Deluxe', 'Bộ sashimi thượng hạng với 15 miếng sashimi tươi ngon', 1200000.00, 2, NULL, 'Cá hồi, cá ngừ, cá trích, wasabi, gừng', 1, 1, 0, '2025-08-21 17:08:30', '2025-08-21 17:08:30'),
(4, 'Saigon Sunset', 'Sự kết hợp hoàn hảo giữa rum, nước cam tươi và grenadine', 180000.00, 4, NULL, 'Rum, Orange Juice, Grenadine', 1, 1, 0, '2025-08-21 17:08:30', '2025-08-21 17:08:30'),
(6, 'Cinema Martini', 'Martini sang trọng với gin premium và vermouth khô', 200000.00, 4, NULL, 'Premium Gin, Dry Vermouth, Lemon Twist', 1, 1, 0, '2025-08-21 17:08:30', '2025-08-21 17:08:30'),
(7, '419 Special', 'Cocktail độc quyền của 419 Saigon', 190000.00, 4, NULL, 'Vodka, Lychee Liqueur, Pineapple Juice', 1, 1, 0, '2025-08-21 17:08:30', '2025-08-21 17:08:30'),
(8, 'Edamame', 'Đậu nành Nhật luộc với muối biển', 120000.00, 1, NULL, 'Đậu nành Nhật, muối biển', 1, 1, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(9, 'Gyoza', 'Bánh bao chiên nhân thịt heo và rau', 180000.00, 1, NULL, 'Thịt heo, bắp cải, hành lá, bột bánh bao', 1, 1, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(10, 'Miso Soup', 'Súp miso truyền thống với đậu hũ và rong biển', 80000.00, 1, NULL, 'Miso, đậu hũ, rong biển wakame, hành lá', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(11, 'Sushi Set Premium', 'Bộ sushi cao cấp với 12 miếng sushi đa dạng', 850000.00, 2, NULL, 'Cá hồi, cá ngừ, tôm, cơm sushi, rong biển', 1, 1, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(12, 'Salmon Nigiri', 'Sushi cá hồi tươi (2 miếng)', 150000.00, 2, NULL, 'Cá hồi tươi, cơm sushi', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(13, 'Tuna Nigiri', 'Sushi cá ngừ tươi (2 miếng)', 140000.00, 2, NULL, 'Cá ngừ tươi, cơm sushi', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(14, 'Ebi Nigiri', 'Sushi tôm luộc (2 miếng)', 130000.00, 2, NULL, 'Tôm luộc, cơm sushi', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(15, 'California Roll', 'Cuốn California với cua, bơ và dưa leo', 200000.00, 2, NULL, 'Cua, bơ, dưa leo, cơm sushi, rong biển', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(16, 'Sashimi Set Deluxe', 'Bộ sashimi thượng hạng với 15 miếng sashimi tươi ngon', 1200000.00, 3, NULL, 'Cá hồi, cá ngừ, cá trích, wasabi, gừng', 1, 1, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(17, 'Salmon Sashimi', 'Sashimi cá hồi tươi (5 miếng)', 250000.00, 3, NULL, 'Cá hồi tươi, wasabi, gừng', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(18, 'Tuna Sashimi', 'Sashimi cá ngừ tươi (5 miếng)', 220000.00, 3, NULL, 'Cá ngừ tươi, wasabi, gừng', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(19, 'Mixed Sashimi', 'Sashimi hỗn hợp (8 miếng)', 350000.00, 3, NULL, 'Cá hồi, cá ngừ, cá trích, wasabi, gừng', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(20, 'Teriyaki Chicken', 'Gà nướng sốt teriyaki với rau củ', 280000.00, 4, NULL, 'Thịt gà, sốt teriyaki, rau củ', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(21, 'Beef Sukiyaki', 'Lẩu Sukiyaki với thịt bò và rau', 450000.00, 4, NULL, 'Thịt bò, rau cải, nấm, đậu hũ', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(22, 'Yakitori Set', 'Set xiên gà nướng (6 xiên)', 320000.00, 4, NULL, 'Thịt gà, sốt yakitori', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(23, 'Unagi Don', 'Cơm lươn nướng sốt teriyaki', 380000.00, 4, NULL, 'Lươn nướng, cơm, sốt teriyaki', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(24, 'Tempura Set', 'Set tempura hỗn hợp với tôm và rau củ', 420000.00, 5, NULL, 'Tôm, rau củ, bột tempura', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(25, 'Ebi Tempura', 'Tempura tôm (6 con)', 280000.00, 5, NULL, 'Tôm, bột tempura', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(26, 'Vegetable Tempura', 'Tempura rau củ (8 miếng)', 220000.00, 5, NULL, 'Rau củ, bột tempura', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(27, 'Mochi Ice Cream', 'Kem mochi truyền thống (3 viên)', 120000.00, 6, NULL, 'Mochi, kem vanilla, đậu đỏ', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(28, 'Green Tea Ice Cream', 'Kem trà xanh Nhật', 80000.00, 6, NULL, 'Kem trà xanh matcha', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(29, 'Dorayaki', 'Bánh Dorayaki nhân đậu đỏ', 100000.00, 6, NULL, 'Bột bánh, đậu đỏ', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(30, 'Premium Sake', 'Sake cao cấp Junmai Daiginjo (300ml)', 450000.00, 7, NULL, 'Gạo sake, men sake', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(31, 'House Sake', 'Sake nhà hàng (300ml)', 280000.00, 7, NULL, 'Gạo sake, men sake', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56'),
(32, 'Hot Sake', 'Sake nóng (200ml)', 180000.00, 7, NULL, 'Gạo sake, men sake', 1, 0, 0, '2025-08-27 03:56:56', '2025-08-27 03:56:56');

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `menu_item_ratings`
-- (See below for the actual view)
--
CREATE TABLE `menu_item_ratings` (
`id` int(11)
,`name` varchar(200)
,`average_rating` decimal(14,4)
,`review_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `menu_sets`
--

CREATE TABLE `menu_sets` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `menu_sets`
--

INSERT INTO `menu_sets` (`id`, `name`, `description`, `price`, `image`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Set A - Premium', 'Set cao cấp với các món đặc sản Nhật Bản', 1800000.00, NULL, 1, 1, '2025-08-31 14:58:31', '2025-08-31 14:58:31'),
(2, 'Set B - Classic', 'Set truyền thống với hương vị Nhật Bản đích thực', 1500000.00, NULL, 1, 2, '2025-08-31 14:58:31', '2025-08-31 14:58:31'),
(3, 'Set C - Essential', 'Set cơ bản với các món phổ biến', 1200000.00, NULL, 1, 3, '2025-08-31 14:58:31', '2025-08-31 14:58:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `movies`
--

CREATE TABLE `movies` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) NOT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `trailer_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `movies`
--

INSERT INTO `movies` (`id`, `title`, `description`, `duration`, `genre`, `poster`, `trailer_url`, `is_available`, `created_at`) VALUES
(1, 'Avengers: Endgame', 'The epic conclusion to the Infinity Saga', 181, 'Action, Adventure, Drama', NULL, NULL, 1, '2025-08-21 17:08:31'),
(2, 'Inception', 'A thief who steals corporate secrets through dream-sharing technology', 148, 'Action, Adventure, Sci-Fi', NULL, NULL, 1, '2025-08-21 17:08:31'),
(3, 'The Dark Knight', 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham', 152, 'Action, Crime, Drama', NULL, NULL, 1, '2025-08-21 17:08:31'),
(4, 'Interstellar', 'A team of explorers travel through a wormhole in space', 169, 'Adventure, Drama, Sci-Fi', NULL, NULL, 1, '2025-08-21 17:08:31'),
(5, 'Pulp Fiction', 'The lives of two mob hitmen, a boxer, a gangster and his wife intertwine', 154, 'Crime, Drama', NULL, NULL, 1, '2025-08-21 17:08:31'),
(6, 'Forrest Gump', 'The presidencies of Kennedy and Johnson, the Vietnam War, the Watergate scandal', 142, 'Drama, Romance', NULL, NULL, 1, '2025-08-21 17:08:31'),
(7, 'The Shawshank Redemption', 'Two imprisoned men bond over a number of years', 142, 'Drama', NULL, NULL, 1, '2025-08-21 17:08:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `movie_schedules`
--

CREATE TABLE `movie_schedules` (
  `id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `status` enum('active','unsubscribed') DEFAULT 'active',
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_type` enum('dine_in','takeaway') DEFAULT 'dine_in',
  `status` enum('pending','preparing','ready','served','cancelled') DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

-- Sample order linked to removed izakaya booking omitted

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `special_instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `recent_orders`
-- (See below for the actual view)
--
CREATE TABLE `recent_orders` (
`id` int(11)
,`booking_id` int(11)
,`user_id` int(11)
,`order_type` enum('dine_in','takeaway')
,`status` enum('pending','preparing','ready','served','cancelled')
,`total_amount` decimal(10,2)
,`notes` text
,`created_at` timestamp
,`updated_at` timestamp
,`first_name` varchar(50)
,`last_name` varchar(50)
,`email` varchar(100)
);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'site_name', '419 Saigon', 'Tên website', '2025-08-21 17:08:31'),
(2, 'site_description', 'Cocktail & Cinema experiences at 419 Saigon', 'Mô tả website', '2025-08-21 17:08:31'),
(3, 'contact_email', 'info@419saigon.com', 'Email liên hệ chính', '2025-08-21 17:08:31'),
(4, 'contact_phone', '+84 28 1234 5678', 'Số điện thoại liên hệ', '2025-08-21 17:08:31'),
(5, 'opening_hours_weekday', '18:00-02:00', 'Giờ mở cửa ngày thường', '2025-08-21 17:08:31'),
(6, 'opening_hours_weekend', '17:00-03:00', 'Giờ mở cửa cuối tuần', '2025-08-21 17:08:31'),
(7, 'address', '419 Đường ABC, Quận 1, TP.HCM', 'Địa chỉ', '2025-08-21 17:08:31'),
(8, 'google_maps_url', 'https://maps.google.com/?q=419+ABC+Ho+Chi+Minh+City', 'URL Google Maps', '2025-08-21 17:08:31'),
(9, 'facebook_url', 'https://facebook.com/419saigon', 'URL Facebook', '2025-08-21 17:08:31'),
(10, 'instagram_url', 'https://instagram.com/419saigon', 'URL Instagram', '2025-08-21 17:08:31'),
(11, 'youtube_url', 'https://youtube.com/419saigon', 'URL YouTube', '2025-08-21 17:08:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `set_items`
--

CREATE TABLE `set_items` (
  `id` int(11) NOT NULL,
  `set_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `set_items`
--

INSERT INTO `set_items` (`id`, `set_id`, `menu_item_id`, `quantity`, `sort_order`, `created_at`) VALUES
(1, 1, 1, 1, 1, '2025-08-31 14:58:31'),
(2, 1, 2, 1, 2, '2025-08-31 14:58:31'),
(3, 2, 1, 1, 1, '2025-08-31 14:58:31'),
(4, 3, 1, 1, 1, '2025-08-31 14:58:31');

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `today_bookings`
-- (See below for the actual view)
--
CREATE TABLE `today_bookings` (
`id` int(11)
,`user_id` int(11)
,`booking_type` enum('cinema','cocktail')
,`name` varchar(100)
,`email` varchar(100)
,`phone` varchar(20)
,`date` date
,`time` time
,`guests` int(11)
,`special_requests` text
,`status` enum('pending','confirmed','cancelled','completed')
,`total_amount` decimal(10,2)
,`created_at` timestamp
,`updated_at` timestamp
,`service_name` varchar(8)
);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@419saigon.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', NULL, 'admin', 'active', '2025-08-21 17:08:30', '2025-08-21 17:08:30'),
(2, 'lenhat_dev', 'realleesan@gmail.com', '$2y$10$hRexh7ctGOB.kzvMqBItCunJDYYjw4yzKB7dlEFXZMpZLNyTIC0Ki', 'Nhat', 'Le', '0339627211', 'user', 'active', '2025-08-27 04:46:39', '2025-08-30 13:52:10');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_saved_sets`
--

CREATE TABLE `user_saved_sets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `set_name` varchar(255) NOT NULL,
  `set_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`set_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `user_saved_sets`
--

INSERT INTO `user_saved_sets` (`id`, `user_id`, `set_name`, `set_data`, `created_at`, `updated_at`) VALUES
(7, 2, 'ni', '{\"type\":\"custom\",\"items\":{\"2\":1,\"11\":1,\"12\":1}}', '2025-09-03 00:52:28', '2025-09-03 00:52:28');

-- --------------------------------------------------------

--
-- Cấu trúc cho view `available_menu_items`
--
DROP TABLE IF EXISTS `available_menu_items`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `available_menu_items`  AS SELECT `m`.`id` AS `id`, `m`.`name` AS `name`, `m`.`description` AS `description`, `m`.`price` AS `price`, `m`.`category_id` AS `category_id`, `m`.`image` AS `image`, `m`.`ingredients` AS `ingredients`, `m`.`is_available` AS `is_available`, `m`.`is_featured` AS `is_featured`, `m`.`sort_order` AS `sort_order`, `m`.`created_at` AS `created_at`, `m`.`updated_at` AS `updated_at`, `c`.`name` AS `category_name` FROM (`menu_items` `m` join `categories` `c` on(`m`.`category_id` = `c`.`id`)) WHERE `m`.`is_available` = 1 AND `c`.`status` = 'active' ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `available_menu_sets`
--
DROP TABLE IF EXISTS `available_menu_sets`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `available_menu_sets`  AS SELECT `ms`.`id` AS `id`, `ms`.`name` AS `name`, `ms`.`description` AS `description`, `ms`.`price` AS `price`, `ms`.`image` AS `image`, `ms`.`is_available` AS `is_available`, `ms`.`sort_order` AS `sort_order`, `ms`.`created_at` AS `created_at`, `ms`.`updated_at` AS `updated_at`, group_concat(concat(`mi`.`name`,' (x',`si`.`quantity`,')') order by `si`.`sort_order` ASC separator ', ') AS `items_description` FROM ((`menu_sets` `ms` left join `set_items` `si` on(`ms`.`id` = `si`.`set_id`)) left join `menu_items` `mi` on(`si`.`menu_item_id` = `mi`.`id`)) WHERE `ms`.`is_available` = 1 GROUP BY `ms`.`id` ORDER BY `ms`.`sort_order` ASC ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `menu_item_ratings`
--
DROP TABLE IF EXISTS `menu_item_ratings`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `menu_item_ratings`  AS SELECT `mi`.`id` AS `id`, `mi`.`name` AS `name`, avg(`fr`.`rating`) AS `average_rating`, count(`fr`.`id`) AS `review_count` FROM (`menu_items` `mi` left join `food_reviews` `fr` on(`mi`.`id` = `fr`.`menu_item_id` and `fr`.`is_approved` = 1)) GROUP BY `mi`.`id`, `mi`.`name` ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `recent_orders`
--
DROP TABLE IF EXISTS `recent_orders`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `recent_orders`  AS SELECT `o`.`id` AS `id`, `o`.`booking_id` AS `booking_id`, `o`.`user_id` AS `user_id`, `o`.`order_type` AS `order_type`, `o`.`status` AS `status`, `o`.`total_amount` AS `total_amount`, `o`.`notes` AS `notes`, `o`.`created_at` AS `created_at`, `o`.`updated_at` AS `updated_at`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, `u`.`email` AS `email` FROM (`orders` `o` left join `users` `u` on(`o`.`user_id` = `u`.`id`)) WHERE `o`.`created_at` >= current_timestamp() - interval 7 day ORDER BY `o`.`created_at` DESC ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `today_bookings`
--
DROP TABLE IF EXISTS `today_bookings`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `today_bookings`  AS SELECT `b`.`id` AS `id`, `b`.`user_id` AS `user_id`, `b`.`booking_type` AS `booking_type`, `b`.`name` AS `name`, `b`.`email` AS `email`, `b`.`phone` AS `phone`, `b`.`date` AS `date`, `b`.`time` AS `time`, `b`.`guests` AS `guests`, `b`.`special_requests` AS `special_requests`, `b`.`status` AS `status`, `b`.`total_amount` AS `total_amount`, `b`.`created_at` AS `created_at`, `b`.`updated_at` AS `updated_at`, CASE WHEN `b`.`booking_type` = 'cinema' THEN 'Cinema' WHEN `b`.`booking_type` = 'cocktail' THEN 'Cocktail' END AS `service_name` FROM `bookings` AS `b` WHERE `b`.`date` = curdate() AND `b`.`status` <> 'cancelled' ;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_bookings_date` (`date`),
  ADD KEY `idx_bookings_status` (`status`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `cinema_bookings`
--
ALTER TABLE `cinema_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Chỉ mục cho bảng `cinema_rooms`
--
ALTER TABLE `cinema_rooms`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contact_messages_status` (`status`);

--
-- Chỉ mục cho bảng `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_booking_feedback` (`booking_id`),
  ADD KEY `idx_feedback_booking_id` (`booking_id`),
  ADD KEY `idx_feedback_rating` (`rating`);

--
-- Chỉ mục cho bảng `food_images`
--
ALTER TABLE `food_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_food_images_item` (`menu_item_id`),
  ADD KEY `idx_food_images_primary` (`is_primary`);

--
-- Chỉ mục cho bảng `food_reviews`
--
ALTER TABLE `food_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_food_reviews_item` (`menu_item_id`),
  ADD KEY `idx_food_reviews_approved` (`is_approved`);

--
-- Chỉ mục cho bảng `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_menu_items_category` (`category_id`),
  ADD KEY `idx_menu_items_available` (`is_available`);

--
-- Chỉ mục cho bảng `menu_sets`
--
ALTER TABLE `menu_sets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_menu_sets_available` (`is_available`);

--
-- Chỉ mục cho bảng `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `movie_schedules`
--
ALTER TABLE `movie_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `idx_movie_schedules_date` (`date`);

--
-- Chỉ mục cho bảng `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Chỉ mục cho bảng `set_items`
--
ALTER TABLE `set_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_set_item` (`set_id`,`menu_item_id`),
  ADD KEY `menu_item_id` (`menu_item_id`),
  ADD KEY `idx_set_items_set` (`set_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_username` (`username`);

--
-- Chỉ mục cho bảng `user_saved_sets`
--
ALTER TABLE `user_saved_sets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_set` (`user_id`,`set_name`),
  ADD KEY `idx_user_saved_sets_user_id` (`user_id`),
  ADD KEY `idx_user_saved_sets_created_at` (`created_at`),
  ADD KEY `idx_user_saved_sets_user` (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `cinema_bookings`
--
ALTER TABLE `cinema_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `cinema_rooms`
--
ALTER TABLE `cinema_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `food_images`
--
ALTER TABLE `food_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `food_reviews`
--
ALTER TABLE `food_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT cho bảng `menu_sets`
--
ALTER TABLE `menu_sets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `movie_schedules`
--
ALTER TABLE `movie_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `set_items`
--
ALTER TABLE `set_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `user_saved_sets`
--
ALTER TABLE `user_saved_sets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `cinema_bookings`
--
ALTER TABLE `cinema_bookings`
  ADD CONSTRAINT `cinema_bookings_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cinema_bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `cinema_rooms` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `food_images`
--
ALTER TABLE `food_images`
  ADD CONSTRAINT `food_images_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `food_reviews`
--
ALTER TABLE `food_reviews`
  ADD CONSTRAINT `food_reviews_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `food_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `movie_schedules`
--
ALTER TABLE `movie_schedules`
  ADD CONSTRAINT `movie_schedules_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movie_schedules_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `cinema_rooms` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `set_items`
--
ALTER TABLE `set_items`
  ADD CONSTRAINT `set_items_ibfk_1` FOREIGN KEY (`set_id`) REFERENCES `menu_sets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `set_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `user_saved_sets`
--
ALTER TABLE `user_saved_sets`
  ADD CONSTRAINT `user_saved_sets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
