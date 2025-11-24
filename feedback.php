<?php 
$page_title = "Đánh Giá Dịch Vụ";
include 'includes/header.php';

// Kết nối database
require_once 'includes/config.php';
$pdo = getDBConnection();

// Lấy booking ID từ URL
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if (!$booking_id) {
    header('Location: index.php');
    exit;
}

// Lấy thông tin booking
$booking_query = "SELECT b.*, u.name as user_name, u.email as user_email 
                 FROM bookings b 
                 LEFT JOIN users u ON b.user_id = u.id 
                 WHERE b.id = ?";
$booking_stmt = $pdo->prepare($booking_query);
$booking_stmt->execute([$booking_id]);
$booking = $booking_stmt->fetch();

if (!$booking) {
    header('Location: index.php');
    exit;
}

// Xử lý form feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    
    if ($rating >= 1 && $rating <= 5) {
        try {
            // Insert feedback
            $feedback_stmt = $pdo->prepare("
                INSERT INTO feedback (booking_id, rating, comment, created_at) 
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                rating = VALUES(rating), 
                comment = VALUES(comment), 
                updated_at = NOW()
            ");
            
            $result = $feedback_stmt->execute([$booking_id, $rating, $comment]);
            
            if ($result) {
                $success_message = "Cảm ơn bạn đã đánh giá dịch vụ!";
            } else {
                $error_message = "Có lỗi xảy ra khi gửi đánh giá!";
            }
        } catch (Exception $e) {
            $error_message = "Có lỗi xảy ra: " . $e->getMessage();
        }
    } else {
        $error_message = "Vui lòng chọn điểm đánh giá từ 1-5 sao!";
    }
}

// Kiểm tra xem đã có feedback chưa
$existing_feedback = null;
$feedback_stmt = $pdo->prepare("SELECT * FROM feedback WHERE booking_id = ?");
$feedback_stmt->execute([$booking_id]);
$existing_feedback = $feedback_stmt->fetch();
?>

<div class="container" style="max-width: 800px; margin: 2rem auto; padding: 2rem;">
    <div class="feedback-container">
        <h1>Đánh Giá Dịch Vụ</h1>
        
        <div class="booking-info">
            <h3>Thông Tin Đặt Bàn</h3>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Ngày:</strong> <?php echo date('d/m/Y', strtotime($booking['date'])); ?>
                </div>
                <div class="info-item">
                    <strong>Giờ:</strong> <?php echo $booking['time']; ?>
                </div>
                <div class="info-item">
                    <strong>Số khách:</strong> <?php echo $booking['guests']; ?> người
                </div>
                <div class="info-item">
                    <strong>Tên:</strong> <?php echo htmlspecialchars($booking['name']); ?>
                </div>
            </div>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($existing_feedback): ?>
            <div class="existing-feedback">
                <h3>Đánh Giá Của Bạn</h3>
                <div class="rating-display">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo $i <= $existing_feedback['rating'] ? 'filled' : ''; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-text"><?php echo $existing_feedback['rating']; ?>/5 sao</span>
                </div>
                <?php if ($existing_feedback['comment']): ?>
                    <div class="comment">
                        <strong>Nhận xét:</strong>
                        <p><?php echo nl2br(htmlspecialchars($existing_feedback['comment'])); ?></p>
                    </div>
                <?php endif; ?>
                <p><em>Bạn có thể cập nhật đánh giá bằng cách gửi lại form bên dưới.</em></p>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="feedback-form">
            <div class="form-group">
                <label>Đánh giá dịch vụ *</label>
                <div class="rating-input">
                    <input type="radio" name="rating" value="1" id="star1" <?php echo ($existing_feedback['rating'] ?? 0) == 1 ? 'checked' : ''; ?>>
                    <label for="star1">★</label>
                    <input type="radio" name="rating" value="2" id="star2" <?php echo ($existing_feedback['rating'] ?? 0) == 2 ? 'checked' : ''; ?>>
                    <label for="star2">★</label>
                    <input type="radio" name="rating" value="3" id="star3" <?php echo ($existing_feedback['rating'] ?? 0) == 3 ? 'checked' : ''; ?>>
                    <label for="star3">★</label>
                    <input type="radio" name="rating" value="4" id="star4" <?php echo ($existing_feedback['rating'] ?? 0) == 4 ? 'checked' : ''; ?>>
                    <label for="star4">★</label>
                    <input type="radio" name="rating" value="5" id="star5" <?php echo ($existing_feedback['rating'] ?? 0) == 5 ? 'checked' : ''; ?>>
                    <label for="star5">★</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="comment">Nhận xét (tùy chọn)</label>
                <textarea id="comment" name="comment" rows="4" placeholder="Chia sẻ trải nghiệm của bạn..."><?php echo htmlspecialchars($existing_feedback['comment'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Gửi Đánh Giá</button>
                <a href="my-bookings.php" class="btn btn-secondary">Quay Lại</a>
            </div>
        </form>
    </div>
</div>

<style>
.feedback-container {
    background: var(--color-dark-gray);
    padding: var(--spacing-xl);
    border-radius: 12px;
    border: 2px solid var(--color-gold);
}

.booking-info {
    background: var(--color-black);
    padding: var(--spacing-lg);
    border-radius: 8px;
    margin-bottom: var(--spacing-lg);
    border: 1px solid rgba(212, 175, 55, 0.2);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-md);
}

.info-item {
    color: var(--color-cream);
}

.info-item strong {
    color: var(--color-gold);
}

.existing-feedback {
    background: rgba(212, 175, 55, 0.1);
    padding: var(--spacing-lg);
    border-radius: 8px;
    margin-bottom: var(--spacing-lg);
    border: 1px solid rgba(212, 175, 55, 0.3);
}

.rating-display {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
}

.stars {
    display: flex;
    gap: 2px;
}

.star {
    font-size: 1.5rem;
    color: #ddd;
}

.star.filled {
    color: #ffd700;
}

.rating-text {
    color: var(--color-gold);
    font-weight: 600;
}

.comment {
    margin-top: var(--spacing-md);
}

.comment p {
    color: var(--color-cream);
    margin-top: var(--spacing-sm);
    line-height: 1.6;
}

.feedback-form {
    background: var(--color-black);
    padding: var(--spacing-lg);
    border-radius: 8px;
    border: 1px solid rgba(212, 175, 55, 0.2);
}

.form-group {
    margin-bottom: var(--spacing-lg);
}

.form-group label {
    display: block;
    margin-bottom: var(--spacing-sm);
    color: var(--color-cream);
    font-weight: 500;
}

.rating-input {
    display: flex;
    gap: var(--spacing-sm);
    align-items: center;
}

.rating-input input[type="radio"] {
    display: none;
}

.rating-input label {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s ease;
    margin: 0;
}

.rating-input label:hover,
.rating-input input[type="radio"]:checked + label,
.rating-input input[type="radio"]:checked ~ label {
    color: #ffd700;
}

.rating-input input[type="radio"]:checked + label {
    color: #ffd700;
}

textarea {
    width: 100%;
    padding: var(--spacing-sm);
    border: 1px solid var(--color-gray);
    border-radius: 4px;
    background: var(--color-dark-gray);
    color: var(--color-cream);
    font-size: 1rem;
    resize: vertical;
}

textarea:focus {
    outline: none;
    border-color: var(--color-gold);
    box-shadow: 0 0 5px rgba(212, 175, 55, 0.3);
}

.form-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: flex-end;
}

.alert {
    padding: var(--spacing-md);
    border-radius: 4px;
    margin-bottom: var(--spacing-lg);
}

.alert-success {
    background: rgba(0, 255, 0, 0.1);
    border: 1px solid rgba(0, 255, 0, 0.3);
    color: #51cf66;
}

.alert-error {
    background: rgba(255, 0, 0, 0.1);
    border: 1px solid rgba(255, 0, 0, 0.3);
    color: #ff6b6b;
}

@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .rating-input label {
        font-size: 1.5rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
