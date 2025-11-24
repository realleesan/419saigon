<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để lưu template set']);
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

$set_name = trim($input['set_name'] ?? '');
$set_data = $input['set_data'] ?? null;

if (empty($set_name)) {
    echo json_encode(['success' => false, 'message' => 'Tên template set không được để trống']);
    exit;
}

if (!$set_data) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu template set không hợp lệ']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Check if set name already exists for this user
    $check_stmt = $pdo->prepare("SELECT id FROM user_saved_sets WHERE user_id = ? AND set_name = ?");
    $check_stmt->execute([$_SESSION['user_id'], $set_name]);
    
    if ($check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Tên template set đã tồn tại']);
        exit;
    }
    
    // Insert new saved set
    $insert_stmt = $pdo->prepare("
        INSERT INTO user_saved_sets (user_id, set_name, set_data, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    
    $result = $insert_stmt->execute([
        $_SESSION['user_id'],
        $set_name,
        json_encode($set_data)
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Đã lưu template set thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi lưu template set']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi lưu template set: ' . $e->getMessage()]);
}
?>
