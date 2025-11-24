-- Script to remove Izakaya-related data and update schema
-- Run on your database when ready. Make a backup before executing.
START TRANSACTION;

-- 1) Delete order items and orders linked to izakaya bookings
DELETE oi FROM order_items oi
JOIN orders o ON oi.order_id = o.id
JOIN bookings b ON o.booking_id = b.id
WHERE b.booking_type = 'izakaya';

DELETE o FROM orders o
JOIN bookings b ON o.booking_id = b.id
WHERE b.booking_type = 'izakaya';

-- 2) Delete feedback entries linked to izakaya bookings
DELETE f FROM feedback f
JOIN bookings b ON f.booking_id = b.id
WHERE b.booking_type = 'izakaya';

-- 3) Delete food images for menu items with 'Izakaya' in name (optional)
DELETE fi FROM food_images fi
JOIN menu_items mi ON fi.menu_item_id = mi.id
WHERE mi.name LIKE '%Izakaya%';

-- 4) Delete menu items that include 'Izakaya' in the name (optional)
DELETE FROM menu_items WHERE name LIKE '%Izakaya%';

-- 5) Delete bookings with booking_type = 'izakaya'
DELETE FROM bookings WHERE booking_type = 'izakaya';

-- 6) If there are any orders that were created separately and reference removed bookings,
-- they have been removed above. If you prefer to change izakaya bookings into another
-- type instead of deleting, run an UPDATE instead.
-- Example: UPDATE bookings SET booking_type = 'cocktail' WHERE booking_type = 'izakaya';

-- 7) Update bookings.booking_type enum to remove 'izakaya'
ALTER TABLE bookings MODIFY booking_type ENUM('cinema','cocktail') NOT NULL;

-- 8) Update any views that reference 'izakaya' in CASE expressions (recreate today_bookings)
DROP VIEW IF EXISTS today_bookings;
CREATE OR REPLACE VIEW today_bookings AS
SELECT b.id AS id,
       b.user_id AS user_id,
       b.booking_type AS booking_type,
       b.name AS name,
       b.email AS email,
       b.phone AS phone,
       b.date AS date,
       b.time AS time,
       b.guests AS guests,
       b.special_requests AS special_requests,
       b.status AS status,
       b.total_amount AS total_amount,
       b.created_at AS created_at,
       b.updated_at AS updated_at,
       CASE
         WHEN b.booking_type = 'cinema' THEN 'Cinema'
         WHEN b.booking_type = 'cocktail' THEN 'Cocktail'
       END AS service_name
FROM bookings b
WHERE b.date = CURDATE() AND b.status <> 'cancelled';

COMMIT;

-- End of script


