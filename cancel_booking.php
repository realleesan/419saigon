<?php
require_once 'includes/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện thao tác này.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
    exit;
}

try {
    $booking_id = (int)$_POST['booking_id'];
    $user_id = $_SESSION['user_id'];
    
    if (!$booking_id) {
        throw new Exception('ID đặt bàn không hợp lệ.');
    }
    
    $pdo = getDBConnection();
    
    // Check if booking exists and belongs to user
    $check_stmt = $pdo->prepare("
        SELECT id, status FROM bookings 
        WHERE id = ? AND user_id = ?
    ");
    $check_stmt->execute([$booking_id, $user_id]);
    $booking = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        throw new Exception('Không tìm thấy đặt bàn hoặc bạn không có quyền hủy đặt bàn này.');
    }
    
    if ($booking['status'] === 'cancelled') {
        throw new Exception('Đặt bàn này đã được hủy trước đó.');
    }
    
    if ($booking['status'] === 'completed') {
        throw new Exception('Không thể hủy đặt bàn đã hoàn thành.');
    }
    
    // Start transaction to ensure data consistency
    $pdo->beginTransaction();
    
    try {
        // Delete related order items first (due to foreign key constraints)
        $delete_order_items_stmt = $pdo->prepare("
            DELETE oi FROM order_items oi
            INNER JOIN orders o ON oi.order_id = o.id
            WHERE o.booking_id = ?
        ");
        $delete_order_items_stmt->execute([$booking_id]);
        
        // Delete related orders
        $delete_orders_stmt = $pdo->prepare("
            DELETE FROM orders WHERE booking_id = ?
        ");
        $delete_orders_stmt->execute([$booking_id]);
        
        // Delete the booking completely
        $delete_booking_stmt = $pdo->prepare("
            DELETE FROM bookings WHERE id = ?
        ");
        $delete_booking_stmt->execute([$booking_id]);
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Đã hủy đặt bàn thành công.']);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw new Exception('Có lỗi xảy ra khi hủy đặt bàn: ' . $e->getMessage());
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
