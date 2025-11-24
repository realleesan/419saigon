<?php
require_once 'includes/config.php';
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect based on service type or default to cocktail
    $service_type = isset($_GET['service']) ? $_GET['service'] : 'cocktail';
    header('Location: ' . $service_type . '.php');
    exit;
}

try {
    // Validate required fields based on service type
    $required_fields = ['name', 'email', 'phone', 'date', 'time', 'guests', 'service_type'];
    if ($_POST['service_type'] === 'cinema') {
        $required_fields[] = 'selected_movie_id';
    }
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Trường $field là bắt buộc");
        }
    }
    
    // Sanitize input
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $date = sanitize($_POST['date']);
    $time = sanitize($_POST['time']);
    $guests = (int)$_POST['guests'];
    $service_type = sanitize($_POST['service_type']);
    $special_requests = isset($_POST['special_requests']) ? sanitize($_POST['special_requests']) : '';
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Cinema specific fields
    $selected_movie_id = isset($_POST['selected_movie_id']) ? (int)$_POST['selected_movie_id'] : null;
    $selected_combo_id = isset($_POST['selected_combo_id']) ? (int)$_POST['selected_combo_id'] : null;
    $movie_title = isset($_POST['movie_title']) ? sanitize($_POST['movie_title']) : '';
    $combo_name = isset($_POST['combo_name']) ? sanitize($_POST['combo_name']) : '';
    $combo_price = isset($_POST['combo_price']) ? (float)$_POST['combo_price'] : 0;
    
    // Get set menu data
    $selected_items_raw = isset($_POST['selected_items']) ? json_decode($_POST['selected_items'], true) : [];
    $total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
    
    // For cinema bookings, don't mix combo info with special_requests
    // Combo info will be stored separately in cinema_bookings table
    
    // Extract selected items based on menu type
    $selected_items = [];
    if (!empty($selected_items_raw)) {
        if (isset($selected_items_raw['custom_items']) && !empty($selected_items_raw['custom_items'])) {
            // Custom items: {item_id: quantity}
            $selected_items = $selected_items_raw['custom_items'];
        } elseif (isset($selected_items_raw['preset_set_id']) && !empty($selected_items_raw['preset_set_id'])) {
            // Preset set: get items from set
            $set_id = $selected_items_raw['preset_set_id'];
            
            $set_items_stmt = $pdo->prepare("
                SELECT si.menu_item_id, si.quantity 
                FROM set_items si 
                WHERE si.set_id = ?
            ");
            $set_items_stmt->execute([$set_id]);
            $set_items = $set_items_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($set_items as $set_item) {
                $selected_items[$set_item['menu_item_id']] = $set_item['quantity'];
            }
        }
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email không hợp lệ');
    }
    

    
    // Validate date (only check date, not time)
    $booking_date = new DateTime($date);
    $today = new DateTime();
    $today->setTime(0, 0, 0); // Set to start of day
    $booking_date->setTime(0, 0, 0); // Set to start of day
    
    if ($booking_date < $today) {
        throw new Exception('Không thể đặt bàn cho ngày trong quá khứ');
    }
    
    // Check if booking is too far in advance (max 7 days)
    $max_advance_date = clone $today;
    $max_advance_date->modify('+7 days');
    
    if ($booking_date > $max_advance_date) {
        throw new Exception('Chỉ có thể đặt bàn tối đa 7 ngày trước. Vui lòng chọn ngày gần hơn.');
    }
    
    // Validate guests based on service type
    if ($service_type === 'cinema') {
        if ($guests < 1 || $guests > 8) {
            throw new Exception('Số khách phải từ 1 đến 8 người cho phòng chiếu riêng');
        }
    } else {
        if ($guests < 1 || $guests > 20) {
            throw new Exception('Số khách phải từ 1 đến 20 người');
        }
    }
    
    // Check capacity for the date based on service type
    if ($service_type === 'cinema') {
        // Check if cinema room is available for the time slot
        $cinema_query = "SELECT COUNT(*) as bookings_count 
                        FROM bookings 
                        WHERE date = ? AND time = ? AND booking_type = 'cinema'";
        $cinema_stmt = $pdo->prepare($cinema_query);
        $cinema_stmt->execute([$date, $time]);
        $cinema_result = $cinema_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cinema_result['bookings_count'] > 0) {
            throw new Exception('Xin lỗi, phòng chiếu đã được đặt cho khung giờ này');
        }
    } else {
        // Capacity check for selected service
        $capacity_query = "SELECT SUM(guests) as total_guests 
                          FROM bookings 
                          WHERE date = ? AND booking_type = ?";
        $capacity_stmt = $pdo->prepare($capacity_query);
        $capacity_stmt->execute([$date, $service_type]);
        $capacity_result = $capacity_stmt->fetch(PDO::FETCH_ASSOC);
        
        $current_guests = $capacity_result['total_guests'] ?? 0;
        $max_capacity = 50;
        
        if ($current_guests + $guests > $max_capacity) {
            throw new Exception('Xin lỗi, không đủ chỗ cho số khách này vào ngày đã chọn');
        }
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Calculate total amount for cinema
    if ($service_type === 'cinema') {
        // Get room price (default to room 1)
        $room_id = 1;
        $room_stmt = $pdo->prepare("SELECT price_per_hour FROM cinema_rooms WHERE id = ?");
        $room_stmt->execute([$room_id]);
        $room = $room_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Cinema booking: 3 hours * price_per_hour
        $total_amount = $room ? $room['price_per_hour'] * 3 : 1500000; // Default 1.5M if room not found
        
        // Add combo price if selected
        if ($selected_combo_id) {
            $combo_stmt = $pdo->prepare("SELECT price FROM cinema_combos WHERE id = ?");
            $combo_stmt->execute([$selected_combo_id]);
            $combo = $combo_stmt->fetch(PDO::FETCH_ASSOC);
            if ($combo) {
                $total_amount += $combo['price'];
            }
        }
    } else {
        // For dining/cocktail services, use the total_amount from form
        $total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
    }
    
    // Insert booking
    $booking_stmt = $pdo->prepare("
        INSERT INTO bookings (user_id, booking_type, name, email, phone, date, time, guests, special_requests, total_amount, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $booking_stmt->execute([
        $user_id, $service_type, $name, $email, $phone, $date, $time, $guests, $special_requests, $total_amount
    ]);
    
    $booking_id = $pdo->lastInsertId();
    
    // Save cinema specific data
    if ($service_type === 'cinema' && $selected_movie_id) {
        // Insert cinema booking details
        $cinema_booking_stmt = $pdo->prepare("
            INSERT INTO cinema_bookings (booking_id, room_id, movie_preference, combo_name, combo_price, duration_hours) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        // Use room_id = 1 as default (main cinema room)
        $room_id = 1;
        $movie_preference = $movie_title;
        $duration_hours = 3; // Default 3 hours
        
        $cinema_booking_stmt->execute([
            $booking_id, $room_id, $movie_preference, $combo_name, $combo_price, $duration_hours
        ]);
    }
    
    // Save set menu if items are selected
    if (!empty($selected_items) && $total_amount > 0) {
        // Create order
        $order_stmt = $pdo->prepare("
            INSERT INTO orders (user_id, booking_id, order_type, status, total_amount, notes, created_at) 
            VALUES (?, ?, 'dine_in', 'pending', ?, ?, NOW())
        ");
        
        $notes = "Set menu cho ngày " . date('d/m/Y', strtotime($date));
        $order_stmt->execute([$user_id, $booking_id, $total_amount, $notes]);
        $order_id = $pdo->lastInsertId();
        
        // Add order items
        $item_stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, total_price) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($selected_items as $item_id => $quantity) {
            // Get menu item price
            $menu_stmt = $pdo->prepare("SELECT price FROM menu_items WHERE id = ?");
            $menu_stmt->execute([$item_id]);
            $menu_item = $menu_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($menu_item) {
                $unit_price = $menu_item['price'];
                $total_price = $unit_price * $quantity;
                
                $item_stmt->execute([$order_id, $item_id, $quantity, $unit_price, $total_price]);
            }
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Send confirmation email (in real implementation)
    // sendConfirmationEmail($email, $name, $date, $time, $guests);
    
    // Redirect with success message
    $_SESSION['booking_success'] = true;
    $_SESSION['booking_id'] = $booking_id;
    $redirect_page = $service_type === 'cinema' ? 'cinema.php' : 'cocktail.php';
    header('Location: ' . $redirect_page . '?success=1');
    exit;
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['booking_error'] = $e->getMessage();
    $service_type = isset($_POST['service_type']) ? $_POST['service_type'] : 'cocktail';
    $redirect_page = $service_type === 'cinema' ? 'cinema.php' : 'cocktail.php';
    header('Location: ' . $redirect_page . '?error=1');
    exit;
}
?>
