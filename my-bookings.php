<?php
$page_title = 'Đặt Bàn Của Tôi';
require_once 'includes/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$pdo = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get user's bookings with order details (exclude cancelled bookings)
$stmt = $pdo->prepare("
    SELECT b.*, 
           CASE 
             WHEN b.booking_type = 'cinema' THEN 'Cinema'
             WHEN b.booking_type = 'cocktail' THEN 'Cocktail'
           END as service_name,
           o.id as order_id,
           o.total_amount as order_total,
           o.notes as order_notes,
           cb.movie_preference,
           cb.combo_name,
           cb.combo_price,
           cb.duration_hours,
           cb.room_id
    FROM bookings b 
    LEFT JOIN orders o ON b.id = o.booking_id
    LEFT JOIN cinema_bookings cb ON b.id = cb.booking_id
    WHERE b.user_id = ? AND b.status != 'cancelled'
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

// Get order items for each booking
$bookings_with_items = [];
foreach ($bookings as $booking) {
    $booking['order_items'] = [];
    $booking['cinema_combo'] = null;
    
    if ($booking['order_id']) {
        $items_stmt = $pdo->prepare("
            SELECT oi.*, mi.name as item_name, mi.description as item_description
            FROM order_items oi
            JOIN menu_items mi ON oi.menu_item_id = mi.id
            WHERE oi.order_id = ?
            ORDER BY mi.name
        ");
        $items_stmt->execute([$booking['order_id']]);
        $booking['order_items'] = $items_stmt->fetchAll();
    }
    
    // Get cinema combo info if it's a cinema booking
    if ($booking['booking_type'] === 'cinema' && $booking['movie_preference']) {
        // Get combo info from cinema_bookings table
        if ($booking['combo_name']) {
            $booking['cinema_combo'] = $booking['combo_name'];
            if ($booking['combo_price'] > 0) {
                $booking['cinema_combo'] .= " (" . number_format($booking['combo_price'], 0, ',', '.') . " VNĐ)";
            }
        }
    }
    
    $bookings_with_items[] = $booking;
}

$bookings = $bookings_with_items;

include 'includes/header.php';
?>

<div class="bookings-container">
    <div class="bookings-header">
        <div class="header-top">
            <a href="account.php" class="back-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                Quay lại
            </a>
        </div>
        <h1 class="page-title">Đặt Bàn Của Tôi</h1>
        <p class="page-subtitle">Xem lịch sử đặt bàn và trạng thái hiện tại</p>
        
        <!-- Filter Buttons -->
        <div class="booking-filters">
            <button class="filter-btn active" onclick="filterBookings('all')">Tất Cả</button>
            <button class="filter-btn" onclick="filterBookings('cinema')">Cinema</button>
            <!-- Izakaya filter removed -->
            <button class="filter-btn" onclick="filterBookings('cocktail')">Cocktail</button>
        </div>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="empty-state">
            <div class="empty-icon">📅</div>
            <h3>Chưa có đặt bàn nào</h3>
            <p>Bạn chưa có lịch sử đặt bàn nào. Hãy đặt bàn để trải nghiệm dịch vụ của chúng tôi!</p>
            <div class="empty-actions">
                <a href="cocktail.php" class="btn btn-primary">Đặt Bàn Cocktail</a>
                <a href="cinema.php" class="btn btn-secondary">Đặt Phòng Cinema</a>
            </div>
        </div>
    <?php else: ?>
        <div class="bookings-list">
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card" data-booking-type="<?php echo $booking['booking_type']; ?>">
                    <div class="booking-header">
                        <div class="booking-type">
                            <span class="type-badge type-<?php echo $booking['booking_type']; ?>">
                                <?php echo $booking['service_name']; ?>
                            </span>
                        </div>
                        <div class="booking-status">
                            <span class="status-badge status-<?php echo $booking['status']; ?>">
                                <?php 
                                switch($booking['status']) {
                                    case 'pending': echo 'Chờ xác nhận'; break;
                                    case 'confirmed': echo 'Đã xác nhận'; break;
                                    case 'cancelled': echo 'Đã hủy'; break;
                                    case 'completed': echo 'Hoàn thành'; break;
                                    default: echo $booking['status'];
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="booking-details">
                        <div class="detail-row">
                            <div class="detail-item">
                                <span class="detail-label">Ngày đặt:</span>
                                <span class="detail-value"><?php echo date('d/m/Y', strtotime($booking['date'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Giờ:</span>
                                <span class="detail-value"><?php echo date('H:i', strtotime($booking['time'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-item">
                                <span class="detail-label">Số người:</span>
                                <span class="detail-value"><?php echo $booking['guests']; ?> người</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Ngày tạo:</span>
                                <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-item">
                                <span class="detail-label">Người đặt:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($booking['name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></span>
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-item">
                                <span class="detail-label">Số điện thoại:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($booking['phone']); ?></span>
                            </div>
                        </div>
                        
                        <?php if ($booking['special_requests']): ?>
                            <div class="detail-row">
                                <div class="detail-item full-width">
                                    <span class="detail-label">Yêu cầu đặc biệt:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($booking['special_requests']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($booking['order_total'] || $booking['total_amount']): ?>
                            <div class="detail-row">
                                <div class="detail-item">
                                    <span class="detail-label">Tổng tiền:</span>
                                    <span class="detail-value amount"><?php echo number_format($booking['order_total'] ?: $booking['total_amount'], 0, ',', '.'); ?> VNĐ</span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($booking['booking_type'] === 'cinema' && $booking['movie_preference']): ?>
                            <div class="detail-row">
                                <div class="detail-item full-width">
                                    <span class="detail-label">🎬 Thông tin Cinema:</span>
                                    <div class="cinema-details">
                                        <div class="cinema-item">
                                            <span class="cinema-label">Phim:</span>
                                            <span class="cinema-value"><?php echo htmlspecialchars($booking['movie_preference']); ?></span>
                                        </div>
                                        <div class="cinema-item">
                                            <span class="cinema-label">Thời gian:</span>
                                            <span class="cinema-value"><?php echo $booking['duration_hours'] ?: 3; ?> giờ</span>
                                        </div>
                                        <?php if ($booking['combo_name']): ?>
                                            <div class="cinema-item">
                                                <span class="cinema-label">Combo:</span>
                                                <span class="cinema-value">
                                                    <?php echo htmlspecialchars($booking['combo_name']); ?>
                                                    <?php if ($booking['combo_price'] > 0): ?>
                                                        (<?php echo number_format($booking['combo_price'], 0, ',', '.'); ?> VNĐ)
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($booking['order_items'])): ?>
                            <div class="detail-row">
                                <div class="detail-item full-width">
                                    <span class="detail-label">Set Menu đã chọn:</span>
                                    <div class="menu-items-list">
                                        <?php 
                                        foreach ($booking['order_items'] as $item): ?>
                                            <div class="menu-item-detail">
                                                <div class="item-info">
                                                    <span class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></span>
                                                    <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
                                                </div>
                                                <div class="item-price">
                                                    <?php echo number_format($item['total_price'], 0, ',', '.'); ?> VNĐ
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="booking-actions">
                        <?php if ($booking['status'] === 'pending'): ?>
                            <button class="btn btn-outline btn-sm" onclick="cancelBooking(<?php echo $booking['id']; ?>)">Hủy Đặt Bàn</button>
                            <?php if ($booking['order_total'] || $booking['total_amount']): ?>
                                <a href="#" class="btn btn-primary btn-sm" onclick="processPayment(<?php echo $booking['id']; ?>)">Thanh Toán</a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if ($booking['status'] === 'confirmed'): ?>
                            <a href="tel:+84123456789" class="btn btn-primary btn-sm">Gọi Xác Nhận</a>
                            <?php if ($booking['order_total'] || $booking['total_amount']): ?>
                                <a href="#" class="btn btn-success btn-sm" onclick="processPayment(<?php echo $booking['id']; ?>)">Thanh Toán</a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if ($booking['status'] === 'completed'): ?>
                            <a href="feedback.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-warning btn-sm">Đánh Giá Dịch Vụ</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Back button styling */
.header-top {
    margin-bottom: var(--spacing-md);
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-xs);
    color: var(--color-gold);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    padding: var(--spacing-xs) var(--spacing-sm);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: 6px;
    background: rgba(212, 175, 55, 0.05);
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: rgba(212, 175, 55, 0.1);
    border-color: var(--color-gold);
    color: var(--color-cream);
    transform: translateX(-2px);
}

.back-btn svg {
    transition: transform 0.3s ease;
}

.back-btn:hover svg {
    transform: translateX(-2px);
}

/* Booking Filters */
.booking-filters {
    display: flex;
    gap: var(--spacing-sm);
    margin-top: var(--spacing-lg);
    justify-content: center;
    flex-wrap: wrap;
}

.filter-btn {
    background: var(--color-black);
    border: 1px solid rgba(212, 175, 55, 0.3);
    color: var(--color-light-gray);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.filter-btn:hover {
    border-color: var(--color-gold);
    color: var(--color-gold);
    transform: translateY(-1px);
}

.filter-btn.active {
    background: var(--color-gold);
    border-color: var(--color-gold);
    color: var(--color-black);
    font-weight: 600;
}

.filter-btn.active:hover {
    background: var(--color-gold);
    color: var(--color-black);
    transform: translateY(-1px);
}

/* Cinema details styling */
.cinema-details {
    margin-top: var(--spacing-sm);
    background: rgba(212, 175, 55, 0.05);
    border-radius: 6px;
    padding: var(--spacing-sm);
    border: 1px solid rgba(212, 175, 55, 0.1);
}

.cinema-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-xs) 0;
    border-bottom: 1px solid rgba(212, 175, 55, 0.1);
}

.cinema-item:last-child {
    border-bottom: none;
}

.cinema-label {
    color: var(--color-light-gray);
    font-size: 0.9rem;
    font-weight: 500;
}

.cinema-value {
    color: var(--color-gold);
    font-weight: 600;
    font-size: 0.9rem;
}

/* Menu items styling */
.menu-items-list {
    margin-top: var(--spacing-sm);
    background: rgba(212, 175, 55, 0.05);
    border-radius: 6px;
    padding: var(--spacing-sm);
    border: 1px solid rgba(212, 175, 55, 0.1);
}

.menu-item-detail {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-xs) 0;
    border-bottom: 1px solid rgba(212, 175, 55, 0.1);
}

.menu-item-detail:last-child {
    border-bottom: none;
}

.item-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.item-name {
    color: var(--color-cream);
    font-weight: 500;
}

.item-quantity {
    color: var(--color-gold);
    font-size: 0.9rem;
    background: rgba(212, 175, 55, 0.1);
    padding: 2px 6px;
    border-radius: 4px;
}

.item-price {
    color: var(--color-gold);
    font-weight: 600;
    font-size: 0.9rem;
}

.detail-item.full-width {
    width: 100%;
}

.detail-item.full-width .detail-label {
    display: block;
    margin-bottom: var(--spacing-xs);
    color: var(--color-gold);
    font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .menu-item-detail {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-xs);
    }
    
    .item-info {
        width: 100%;
        justify-content: space-between;
    }
    
    .item-price {
        align-self: flex-end;
    }
}

.btn-warning {
    background: #ffa500;
    color: var(--color-black);
    border: 1px solid #ffa500;
}

.btn-warning:hover {
    background: #ff8c00;
    border-color: #ff8c00;
}
</style>

<script>
function cancelBooking(bookingId) {
    showConfirmDialog(
        'Bạn có chắc chắn muốn hủy đặt bàn này?',
        function() {
            // AJAX call to cancel booking
            fetch('cancel_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'booking_id=' + bookingId
            })
            .then(response => response.json())
            .then(data => {
                            if (data.success) {
                showAlert('Đã hủy đặt bàn!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('Có lỗi xảy ra!', 'error');
            }
            })
                    .catch(error => {
            console.error('Error:', error);
            showAlert('Có lỗi xảy ra!', 'error');
        });
        }
    );
}

function processPayment(bookingId) {
    // Redirect to payment page (to be implemented)
    showAlert('Chức năng thanh toán sẽ được phát triển sau.', 'info');
    // window.location.href = 'payment.php?booking_id=' + bookingId;
}
</script>

<style>
/* Filter Animation */
.booking-card {
    transition: all 0.3s ease;
}

.booking-card.hidden {
    opacity: 0;
    transform: scale(0.95);
    pointer-events: none;
    max-height: 0;
    margin: 0;
    padding: 0;
    overflow: hidden;
}
</style>

<script>
// Booking Filter Functionality
function filterBookings(type) {
    const cards = document.querySelectorAll('.booking-card');
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    // Update active button
    filterBtns.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filter cards
    cards.forEach(card => {
        const cardType = card.getAttribute('data-booking-type');
        
        if (type === 'all' || cardType === type) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
    
    // Show/hide empty state
    const visibleCards = document.querySelectorAll('.booking-card:not(.hidden)');
    const emptyState = document.querySelector('.empty-state');
    
    if (visibleCards.length === 0 && type !== 'all') {
        if (!emptyState) {
            const bookingsList = document.querySelector('.bookings-list');
            const emptyDiv = document.createElement('div');
            emptyDiv.className = 'empty-state';
            emptyDiv.innerHTML = `
                <div class="empty-icon">🔍</div>
                <h3>Không có đặt bàn ${type}</h3>
                <p>Bạn chưa có đặt bàn nào cho dịch vụ ${type}.</p>
            `;
            bookingsList.appendChild(emptyDiv);
        }
    } else if (emptyState && type !== 'all') {
        emptyState.remove();
    }
}
</script>

<?php include 'includes/notification.php'; ?>

<?php include 'includes/footer.php'; ?>
