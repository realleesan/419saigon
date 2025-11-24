<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện thao tác này']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu JSON không hợp lệ: ' . json_last_error_msg()]);
    exit;
}

$set_id = intval($input['set_id'] ?? 0);

if ($set_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID template không hợp lệ: ' . $set_id]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Check if the set belongs to the current user
    $check_stmt = $pdo->prepare("SELECT id FROM user_saved_sets WHERE id = ? AND user_id = ?");
    $check_stmt->execute([$set_id, $_SESSION['user_id']]);
    
    if (!$check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Template không tồn tại hoặc không thuộc về bạn']);
        exit;
    }
    
    // Delete the saved set
    $delete_stmt = $pdo->prepare("DELETE FROM user_saved_sets WHERE id = ? AND user_id = ?");
    $result = $delete_stmt->execute([$set_id, $_SESSION['user_id']]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Đã xóa template thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa template']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
?>
