-- Cập nhật cơ sở dữ liệu cho trang Cocktail
-- Thêm categories mới cho cocktail

INSERT INTO categories (name, description, type, status) VALUES
('New Cocktails', 'Các cocktail mới được thêm vào menu', 'drink', 'active'),
('Seasonal Cocktails', 'Cocktail theo mùa và theo cảm hứng bartender', 'drink', 'active')
ON DUPLICATE KEY UPDATE 
description = VALUES(description),
status = VALUES(status);

-- Cập nhật categories hiện có
UPDATE categories SET 
name = 'Classic Cocktails',
description = 'Các cocktail cổ điển truyền thống'
WHERE id = 5;

UPDATE categories SET 
name = 'Signature Cocktails',
description = 'Cocktail độc quyền của 419 Saigon'
WHERE id = 4;

-- Thêm cocktail mới
INSERT INTO menu_items (name, description, price, category_id, ingredients, is_available, is_featured, sort_order) VALUES
('Spring Blossom', 'Cocktail mùa xuân với hương hoa anh đào và sake', 250000.00, 4, 'Sake, Cherry Blossom Syrup, Lemon Juice, Egg White', 1, 1, 1),
('Summer Breeze', 'Cocktail mùa hè mát lạnh với dưa hấu và mint', 220000.00, 4, 'Vodka, Watermelon Juice, Mint, Lime, Soda', 1, 1, 2),
('Autumn Harvest', 'Cocktail mùa thu với hương vị táo và quế', 240000.00, 4, 'Bourbon, Apple Cider, Cinnamon Syrup, Lemon', 1, 1, 3),
('Winter Warmth', 'Cocktail mùa đông ấm áp với hương vị gia vị', 260000.00, 4, 'Dark Rum, Spiced Apple Cider, Cinnamon, Nutmeg', 1, 1, 4),
('Lychee Dream', 'Cocktail mới với hương vị vải thiều độc đáo', 200000.00, 17, 'Vodka, Lychee Liqueur, Lychee Juice, Rose Water', 1, 0, 1),
('Passion Fruit Paradise', 'Cocktail nhiệt đới với hương vị chanh dây', 210000.00, 17, 'Rum, Passion Fruit Puree, Lime, Simple Syrup', 1, 0, 2),
('Mango Tango', 'Cocktail mùa hè với hương vị xoài tươi', 190000.00, 17, 'Tequila, Mango Puree, Orange Juice, Agave', 1, 0, 3),
('Berry Blast', 'Cocktail với hỗn hợp quả mọng tươi', 230000.00, 18, 'Gin, Mixed Berries, Lemon, Elderflower Liqueur', 1, 0, 1),
('Pumpkin Spice Latte', 'Cocktail mùa thu với hương vị bí ngô', 250000.00, 18, 'Vanilla Vodka, Pumpkin Puree, Coffee Liqueur, Cream', 1, 0, 2),
('Cranberry Kiss', 'Cocktail Giáng sinh với hương vị nam việt quất', 240000.00, 18, 'Vodka, Cranberry Juice, Orange Liqueur, Lime', 1, 0, 3)
ON DUPLICATE KEY UPDATE 
description = VALUES(description),
price = VALUES(price),
ingredients = VALUES(ingredients),
is_available = VALUES(is_available),
is_featured = VALUES(is_featured),
sort_order = VALUES(sort_order);

-- Cập nhật cocktail hiện có để có thêm thông tin
UPDATE menu_items SET 
ingredients = 'Rum, Orange Juice, Grenadine, Fresh Mint',
description = 'Sự kết hợp hoàn hảo giữa rum, nước cam tươi và grenadine, tạo nên màu sắc rực rỡ như hoàng hôn Sài Gòn. Hoàn thiện với lá bạc hà tươi.'
WHERE id = 4;

UPDATE menu_items SET 
ingredients = 'Japanese Whisky, Bitters, Sugar, Orange Peel, Cherry',
description = 'Biến thể độc đáo của Old Fashioned với whisky Nhật và bitters truyền thống, thêm một chút twist Á Đông. Hoàn thiện với vỏ cam và cherry.'
WHERE id = 5;

UPDATE menu_items SET 
ingredients = 'Premium Gin, Dry Vermouth, Lemon Twist, Olive, Ice',
description = 'Martini sang trọng với gin premium và vermouth khô, hoàn thiện với twist chanh và olive. Pha chế theo công thức cổ điển.'
WHERE id = 6;

UPDATE menu_items SET 
ingredients = 'Vodka, Lychee Liqueur, Pineapple Juice, Fresh Lime, Mint',
description = 'Cocktail độc quyền của 419 Saigon, kết hợp vodka, lychee liqueur và nước ép dứa tươi. Hương vị nhiệt đới độc đáo.'
WHERE id = 7;

-- Thêm mocktail mới
INSERT INTO menu_items (name, description, price, category_id, ingredients, is_available, is_featured, sort_order) VALUES
('Virgin Pina Colada', 'Mocktail dứa và dừa không cồn', 150000.00, 6, 'Pineapple Juice, Coconut Cream, Fresh Lime, Ice', 1, 0, 1),
('Blue Lagoon Mocktail', 'Mocktail xanh dương với hương vị dâu xanh', 160000.00, 6, 'Blue Curacao Syrup, Lemon Juice, Soda, Fresh Berries', 1, 0, 2),
('Tropical Paradise', 'Mocktail nhiệt đới với xoài và đu đủ', 170000.00, 6, 'Mango Juice, Papaya Juice, Orange Juice, Grenadine', 1, 0, 3)
ON DUPLICATE KEY UPDATE 
description = VALUES(description),
price = VALUES(price),
ingredients = VALUES(ingredients),
is_available = VALUES(is_available),
is_featured = VALUES(is_featured),
sort_order = VALUES(sort_order);

-- Cập nhật sort_order cho các cocktail hiện có
UPDATE menu_items SET sort_order = 1 WHERE id = 4; -- Saigon Sunset
UPDATE menu_items SET sort_order = 2 WHERE id = 5; -- Izakaya Old Fashioned  
UPDATE menu_items SET sort_order = 3 WHERE id = 6; -- Cinema Martini
UPDATE menu_items SET sort_order = 4 WHERE id = 7; -- 419 Special

-- Thêm comment để giải thích cấu trúc
-- Categories:
-- 4: Signature Cocktails (cocktail độc quyền)
-- 5: Classic Cocktails (cocktail cổ điển)
-- 6: Mocktails (không cồn)
-- 17: New Cocktails (cocktail mới)
-- 18: Seasonal Cocktails (cocktail theo mùa)

-- Cập nhật database cho cocktail
-- Thêm bảng cocktail_reviews để lưu đánh giá và bình luận

-- Tạo bảng cocktail_reviews
CREATE TABLE IF NOT EXISTS `cocktail_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_item_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `rating` int(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
  `comment` text DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cocktail_reviews_item` (`menu_item_id`),
  KEY `idx_cocktail_reviews_approved` (`is_approved`),
  CONSTRAINT `cocktail_reviews_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cocktail_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu cho cocktail reviews
-- Sử dụng user_id = NULL để tránh lỗi foreign key constraint
INSERT INTO `cocktail_reviews` (`menu_item_id`, `user_id`, `customer_name`, `rating`, `comment`, `is_approved`) VALUES
(1, NULL, 'Nguyễn Văn A', 5, 'Cocktail rất ngon, hương vị độc đáo!', 1),
(1, NULL, 'Trần Thị B', 4, 'Phong cách pha chế chuyên nghiệp', 1),
(2, NULL, 'Lê Văn C', 5, 'Hương vị hoàn hảo, sẽ quay lại!', 1),
(2, NULL, 'Phạm Thị D', 4, 'Cocktail đẹp mắt và ngon miệng', 1),
(3, NULL, 'Hoàng Văn E', 5, 'Rất thích hương vị này!', 1);

-- Hoặc nếu muốn sử dụng user_id có sẵn, chỉ sử dụng ID 1 và 2
-- INSERT INTO `cocktail_reviews` (`menu_item_id`, `user_id`, `customer_name`, `rating`, `comment`, `is_approved`) VALUES
-- (1, 1, 'Nguyễn Văn A', 5, 'Cocktail rất ngon, hương vị độc đáo!', 1),
-- (1, 2, 'Trần Thị B', 4, 'Phong cách pha chế chuyên nghiệp', 1),
-- (2, 1, 'Lê Văn C', 5, 'Hương vị hoàn hảo, sẽ quay lại!', 1),
-- (2, 2, 'Phạm Thị D', 4, 'Cocktail đẹp mắt và ngon miệng', 1),
-- (3, 1, 'Hoàng Văn E', 5, 'Rất thích hương vị này!', 1);

-- Thêm cột images cho menu_items nếu chưa có
ALTER TABLE `menu_items` 
ADD COLUMN IF NOT EXISTS `images` TEXT DEFAULT NULL COMMENT 'JSON array of image URLs';

-- Cập nhật một số cocktail với ảnh mẫu
UPDATE `menu_items` 
SET `images` = '["assets/images/cocktail-1.jpg", "assets/images/cocktail-1-2.jpg", "assets/images/cocktail-1-3.jpg"]'
WHERE `id` = 1 AND `name` LIKE '%cocktail%';

UPDATE `menu_items` 
SET `images` = '["assets/images/cocktail-2.jpg", "assets/images/cocktail-2-2.jpg"]'
WHERE `id` = 2 AND `name` LIKE '%cocktail%';

UPDATE `menu_items` 
SET `images` = '["assets/images/cocktail-3.jpg"]'
WHERE `id` = 3 AND `name` LIKE '%cocktail%';
