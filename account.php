<?php
$page_title = 'Tài Khoản';
require_once 'includes/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$pdo = getDBConnection();
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($first_name) || empty($last_name)) {
        $error = 'Vui lòng nhập đầy đủ họ và tên.';
    } else {
        // Update basic info
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
        if ($stmt->execute([$first_name, $last_name, $phone, $user_id])) {
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $success = 'Cập nhật thông tin thành công!';
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } else {
            $error = 'Có lỗi xảy ra khi cập nhật thông tin.';
        }
    }
    
    // Handle password change
    if (!empty($current_password) && !empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $error = 'Mật khẩu hiện tại không đúng.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Mật khẩu mới và xác nhận không khớp.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_password, $user_id])) {
                $success = 'Đổi mật khẩu thành công!';
            } else {
                $error = 'Có lỗi xảy ra khi đổi mật khẩu.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="account-container">
    <div class="account-header">
        <h1 class="page-title">Tài Khoản Của Tôi</h1>
        <p class="page-subtitle">Quản lý thông tin cá nhân và cài đặt tài khoản</p>
    </div>

    <!-- Success/Error Messages will be handled by JavaScript notification system -->

    <!-- Tab Navigation -->
    <div class="account-tabs">
        <button class="tab-btn active" onclick="switchTab('personal')">Thông Tin Cá Nhân</button>
        <button class="tab-btn" onclick="switchTab('my-drinks')">My Drinks</button>
        <button class="tab-btn" onclick="switchTab('templates')">Template Set Menu</button>
        <button class="tab-btn" onclick="switchTab('bookings')">Đặt Bàn Của Tôi</button>
    </div>

    <div class="account-content">
        <!-- Personal Information Tab -->
        <div id="personal-tab" class="tab-content active">
            <div class="account-section">
                <h2 class="section-title">Thông Tin Cá Nhân</h2>
                
                <!-- Personal Information Form -->
                <div class="form-section">
                    <h3 class="form-section-title">Thông Tin Cá Nhân</h3>
                    <form method="POST" class="account-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">Tên</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Họ</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled class="form-control">
                            <small class="form-text">Email không thể thay đổi</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Số Điện Thoại</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="form-control">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Cập Nhật Thông Tin</button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Form -->
                <div class="form-section">
                    <h3 class="form-section-title">Đổi Mật Khẩu</h3>
                    <form method="POST" class="account-form">
                        <div class="form-group">
                            <label for="current_password">Mật Khẩu Hiện Tại</label>
                            <input type="password" id="current_password" name="current_password" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Mật Khẩu Mới</label>
                            <input type="password" id="new_password" name="new_password" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Xác Nhận Mật Khẩu Mới</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-secondary">Đổi Mật Khẩu</button>
                        </div>
                    </form>
                </div>

                <!-- Account Information -->
                <div class="form-section">
                    <h3 class="form-section-title">Thông Tin Tài Khoản</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Tên đăng nhập:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Ngày tham gia:</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Vai trò:</span>
                            <span class="info-value"><?php echo $user['role'] === 'admin' ? 'Quản trị viên' : 'Người dùng'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Trạng thái:</span>
                            <span class="info-value"><?php echo $user['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Drinks Tab -->
        <div id="my-drinks-tab" class="tab-content">
            <div class="account-section">
                <h2 class="section-title">My Drinks</h2>
                <p class="section-subtitle">Cocktail yêu thích và bespoke drinks của bạn</p>
                
                <?php
                // Get user's saved cocktail drinks
                $saved_drinks_stmt = $pdo->prepare("SELECT * FROM user_saved_sets 
                                                   WHERE user_id = ? AND set_data LIKE '%cocktail%'
                                                   ORDER BY created_at DESC");
                $saved_drinks_stmt->execute([$user_id]);
                $user_saved_drinks = $saved_drinks_stmt->fetchAll();
                ?>
                
                <?php if (empty($user_saved_drinks)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🍸</div>
                        <h3>Chưa có cocktail nào được lưu</h3>
                        <p>Hãy lưu những cocktail yêu thích để dễ dàng tìm lại sau này!</p>
                        <a href="cocktail.php" class="btn btn-primary">Khám Phá Cocktail</a>
                    </div>
                <?php else: ?>
                    <div class="my-drinks-grid">
                        <?php foreach ($user_saved_drinks as $saved_drink): ?>
                            <div class="saved-drink-card">
                                <div class="drink-header">
                                    <h4><?php echo htmlspecialchars($saved_drink['set_name']); ?></h4>
                                    <span class="saved-date"><?php echo date('d/m/Y', strtotime($saved_drink['created_at'])); ?></span>
                                </div>
                                <div class="drink-content">
                                    <?php
                                    $drink_data = json_decode($saved_drink['set_data'], true);
                                    if (isset($drink_data['items'])):
                                        // Get menu items details for cocktails
                                        $item_ids = array_keys($drink_data['items']);
                                        if (!empty($item_ids)) {
                                            $placeholders = str_repeat('?,', count($item_ids) - 1) . '?';
                                            $items_stmt = $pdo->prepare("SELECT id, name, price FROM menu_items WHERE id IN ($placeholders)");
                                            $items_stmt->execute($item_ids);
                                            $menu_items_details = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                                            
                                            $items_by_id = [];
                                            foreach ($menu_items_details as $item) {
                                                $items_by_id[$item['id']] = $item;
                                            }
                                            
                                            foreach ($drink_data['items'] as $item_id => $quantity) {
                                                if (isset($items_by_id[$item_id])) {
                                                    $item = $items_by_id[$item_id];
                                                    echo '<div class="drink-item">';
                                                    echo '<span class="item-name">' . htmlspecialchars($item['name']) . '</span>';
                                                    echo '<span class="item-quantity">x' . $quantity . '</span>';
                                                    echo '</div>';
                                                }
                                            }
                                        }
                                    endif;
                                    ?>
                                </div>
                                <div class="drink-actions">
                                    <button class="btn btn-sm btn-outline" onclick="showToBartender(<?php echo $saved_drink['id']; ?>)">Show to Bartender</button>
                                    <button class="btn btn-sm btn-primary" onclick="orderAgain(<?php echo $saved_drink['id']; ?>)">Order Again</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteSavedDrink(<?php echo $saved_drink['id']; ?>)">Xóa</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>

        <!-- Template Set Menu Tab -->
        <div id="templates-tab" class="tab-content">
            <div class="account-section">
                <h2 class="section-title">Template Set Menu Đã Lưu</h2>
                <?php
                // Get user's saved sets
                $saved_sets_stmt = $pdo->prepare("SELECT * FROM user_saved_sets WHERE user_id = ? ORDER BY created_at DESC");
                $saved_sets_stmt->execute([$user_id]);
                $saved_sets = $saved_sets_stmt->fetchAll();
                ?>
                
                <?php if (empty($saved_sets)): ?>
                    <div class="empty-state">
                        <p>Bạn chưa có template set menu nào được lưu.</p>
                        <a href="cocktail.php" class="btn btn-primary">Tạo Template Mới</a>
                    </div>
                <?php else: ?>
                    <div class="saved-sets-grid">
                        <?php foreach ($saved_sets as $set): ?>
                            <div class="saved-set-item">
                                <div class="set-header">
                                    <h4><?php echo htmlspecialchars($set['set_name']); ?></h4>
                                    <span class="set-date"><?php echo date('d/m/Y', strtotime($set['created_at'])); ?></span>
                                </div>
                                <div class="set-preview">
                                    <?php
                                    $set_data = json_decode($set['set_data'], true);
                                    if ($set_data['type'] === 'preset') {
                                        echo '<span class="set-type preset">Set ' . htmlspecialchars($set_data['set_name']) . '</span>';
                                        echo '<span class="set-price">' . number_format($set_data['price']) . ' VNĐ</span>';
                                    } else {
                                        echo '<span class="set-type custom">Tự tạo</span>';
                                        echo '<span class="set-items">' . count($set_data['items']) . ' món</span>';
                                    }
                                    ?>
                                </div>
                                <div class="set-details">
                                    <?php
                                    if ($set_data['type'] === 'preset') {
                                        // For preset sets, show the set name and price
                                        echo '<div class="detail-item">';
                                        echo '<span class="detail-label">Loại:</span>';
                                        echo '<span class="detail-value">Set ' . htmlspecialchars($set_data['set_name']) . '</span>';
                                        echo '</div>';
                                        echo '<div class="detail-item">';
                                        echo '<span class="detail-label">Giá:</span>';
                                        echo '<span class="detail-value">' . number_format($set_data['price']) . ' VNĐ</span>';
                                        echo '</div>';
                                    } else {
                                        // For custom sets, show individual items
                                        if (!empty($set_data['items'])) {
                                            echo '<div class="detail-item">';
                                            echo '<span class="detail-label">Món ăn:</span>';
                                            echo '<div class="items-list">';
                                            
                                            // Get menu items details
                                            $item_ids = array_keys($set_data['items']);
                                            if (!empty($item_ids)) {
                                                $placeholders = str_repeat('?,', count($item_ids) - 1) . '?';
                                                $items_stmt = $pdo->prepare("SELECT id, name, price FROM menu_items WHERE id IN ($placeholders)");
                                                $items_stmt->execute($item_ids);
                                                $menu_items_details = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                                                
                                                $items_by_id = [];
                                                foreach ($menu_items_details as $item) {
                                                    $items_by_id[$item['id']] = $item;
                                                }
                                                
                                                foreach ($set_data['items'] as $item_id => $quantity) {
                                                    if (isset($items_by_id[$item_id])) {
                                                        $item = $items_by_id[$item_id];
                                                        echo '<div class="item-detail">';
                                                        echo '<span class="item-name">' . htmlspecialchars($item['name']) . ' x' . $quantity . '</span>';
                                                        echo '<span class="item-price">' . number_format($item['price'] * $quantity) . ' VNĐ</span>';
                                                        echo '</div>';
                                                    }
                                                }
                                            }
                                            
                                            echo '</div>';
                                            echo '</div>';
                                            
                                            // Calculate and show total
                                            $total = 0;
                                            foreach ($set_data['items'] as $item_id => $quantity) {
                                                if (isset($items_by_id[$item_id])) {
                                                    $total += $items_by_id[$item_id]['price'] * $quantity;
                                                }
                                            }
                                            echo '<div class="detail-item">';
                                            echo '<span class="detail-label">Tổng:</span>';
                                            echo '<span class="detail-value total-price">' . number_format($total) . ' VNĐ</span>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="set-actions">
                                    <button type="button" class="btn btn-sm btn-primary" onclick="loadSavedSet(<?php echo $set['id']; ?>)">Sử Dụng</button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteSavedSet(<?php echo $set['id']; ?>)">Xóa</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>


        <!-- Bookings Tab -->
        <div id="bookings-tab" class="tab-content">
            <div class="account-section">
                <h2 class="section-title">Đặt Bàn Của Tôi</h2>
                <?php
                // Get user's recent bookings
                $bookings_stmt = $pdo->prepare("
                    SELECT b.*, 
                           CASE 
                             WHEN b.booking_type = 'cinema' THEN 'Cinema'
                             WHEN b.booking_type = 'cocktail' THEN 'Cocktail'
                           END as service_name
                    FROM bookings b 
                    WHERE b.user_id = ? AND b.status != 'cancelled'
                    ORDER BY b.created_at DESC
                    LIMIT 5
                ");
                $bookings_stmt->execute([$user_id]);
                $recent_bookings = $bookings_stmt->fetchAll();
                ?>
                
                <?php if (empty($recent_bookings)): ?>
                    <div class="empty-state">
                        <p>Bạn chưa có đặt bàn nào.</p>
                    <a href="cocktail.php" class="btn btn-primary">Đặt Bàn Ngay</a>
                    </div>
                <?php else: ?>
                    <div class="bookings-preview">
                        <?php foreach ($recent_bookings as $booking): ?>
                            <div class="booking-item">
                                <div class="booking-info">
                                    <div class="booking-service">
                                        <span class="service-badge service-<?php echo $booking['booking_type']; ?>">
                                            <?php echo $booking['service_name']; ?>
                                        </span>
                                    </div>
                                    <div class="booking-details">
                                        <span class="booking-date"><?php echo date('d/m/Y', strtotime($booking['date'])); ?></span>
                                        <span class="booking-time"><?php echo date('H:i', strtotime($booking['time'])); ?></span>
                                        <span class="booking-guests"><?php echo $booking['guests']; ?> người</span>
                                    </div>
                                    <div class="booking-status">
                                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                                            <?php 
                                            switch($booking['status']) {
                                                case 'pending': echo 'Chờ xác nhận'; break;
                                                case 'confirmed': echo 'Đã xác nhận'; break;
                                                case 'completed': echo 'Hoàn thành'; break;
                                                default: echo $booking['status'];
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="view-all-bookings">
                        <a href="my-bookings.php" class="btn btn-outline">Xem Tất Cả Đặt Bàn</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Logout Section - Always visible -->
        <div class="logout-section">
            <a href="logout.php" class="btn btn-danger">Đăng Xuất</a>
        </div>
    </div>
</div>

<?php include 'includes/notification.php'; ?>

<script>
// Show notifications for account page
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($error): ?>
        showAlert('<?php echo addslashes($error); ?>', 'error');
    <?php endif; ?>
    
    <?php if ($success): ?>
        showAlert('<?php echo addslashes($success); ?>', 'success');
    <?php endif; ?>
});

// Tab switching functionality
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

// Load saved set function
function loadSavedSet(setId) {
    // Redirect to cocktail page with set ID parameter and auto-open modal
    window.location.href = 'cocktail.php?load_set=' + setId + '&auto_open=1';
}

// Delete saved set function
function deleteSavedSet(setId) {
    if (confirm('Bạn có chắc chắn muốn xóa template này?')) {
        // Show loading state
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Đang xóa...';
        button.disabled = true;
        
        fetch('delete_saved_set.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                set_id: parseInt(setId)
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                // Reload page to update the list
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert(data.message, 'error');
                // Reset button state
                button.textContent = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Có lỗi xảy ra khi xóa template: ' + error.message, 'error');
            // Reset button state
            button.textContent = originalText;
            button.disabled = false;
        });
    }
}

// My Drinks functions
function deleteSavedDrink(drinkId) {
    if (confirm('Bạn có chắc chắn muốn xóa cocktail này?')) {
        // Show loading state
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Đang xóa...';
        button.disabled = true;
        
        fetch('delete_saved_set.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                set_id: parseInt(drinkId)
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                // Remove the drink card from DOM instead of reloading
                const drinkCard = button.closest('.saved-drink-card');
                if (drinkCard) {
                    drinkCard.classList.add('deleting'); // Add deleting class
                    setTimeout(() => {
                        drinkCard.remove();
                        
                        // Check if no more drinks
                        const remainingDrinks = document.querySelectorAll('.saved-drink-card');
                        if (remainingDrinks.length === 0) {
                            // Show empty state
                            const myDrinksTab = document.getElementById('my-drinks-tab');
                            const drinksGrid = myDrinksTab.querySelector('.my-drinks-grid');
                            const bespokeSection = myDrinksTab.querySelector('.bespoke-section');
                            
                            if (drinksGrid && bespokeSection) {
                                drinksGrid.innerHTML = `
                                    <div class="empty-state">
                                        <div class="empty-icon">🍸</div>
                                        <h3>Chưa có cocktail nào được lưu</h3>
                                        <p>Hãy lưu những cocktail yêu thích để dễ dàng tìm lại sau này!</p>
                                        <a href="cocktail.php" class="btn btn-primary">Khám Phá Cocktail</a>
                                    </div>
                                `;
                            }
                        }
                    }, 300);
                }
            } else {
                showAlert(data.message, 'error');
                // Reset button state
                button.textContent = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Có lỗi xảy ra khi xóa cocktail: ' + error.message, 'error');
            // Reset button state
            button.textContent = originalText;
            button.disabled = false;
        });
    }
}

function showToBartender(drinkId) {
    // In real app, this would generate a QR code or show drink details
    showAlert('Chức năng Show to Bartender sẽ được phát triển sau!', 'info');
}

function orderAgain(drinkId) {
    // In real app, this would add items to cart or redirect to booking
    showAlert('Chức năng Order Again sẽ được phát triển sau!', 'info');
}

// Cinema Booking Functions
function cancelCinemaBooking(bookingId) {
    if (confirm('Bạn có chắc chắn muốn hủy vé này?')) {
        // Show loading state
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Đang hủy...';
        button.disabled = true;
        
        fetch('cancel_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                booking_id: parseInt(bookingId)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                // Reload page to update the list
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert(data.message, 'error');
                // Reset button state
                button.textContent = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Có lỗi xảy ra khi hủy vé: ' + error.message, 'error');
            // Reset button state
            button.textContent = originalText;
            button.disabled = false;
        });
    }
}

function viewCinemaBookingDetails(bookingId) {
    // In real app, this would show a modal with detailed booking information
    showAlert('Chức năng xem chi tiết đặt vé sẽ được phát triển sau!', 'info');
}

function rateCinemaExperience(bookingId) {
    // In real app, this would open a rating modal
    showAlert('Chức năng đánh giá trải nghiệm cinema sẽ được phát triển sau!', 'info');
}








</script>

<style>
/* Tab Navigation Styles */
.account-tabs {
    display: flex;
    justify-content: center;
    margin-bottom: var(--spacing-xl);
    border-bottom: 2px solid rgba(212, 175, 55, 0.2);
    padding-bottom: var(--spacing-md);
}

.tab-btn {
    padding: var(--spacing-md) var(--spacing-lg);
    margin: 0 var(--spacing-sm);
    background: transparent;
    border: 1px solid var(--color-gold);
    color: var(--color-gold);
    border-radius: 8px 8px 0 0;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 0.95rem;
    min-width: 150px;
}

.tab-btn:hover {
    background: rgba(0, 0, 0, 0.05);
    border-color: var(--color-black);
}

.tab-btn.active {
    background: var(--color-white);
    color: var(--color-black);
    border-color: var(--color-black);
    border-width: 2px;
    font-weight: 600;
}

.tab-btn.active:hover {
    background: var(--color-white);
    color: var(--color-black);
}

/* Tab Content Styles */
.tab-content {
    display: none;
    animation: fadeIn 0.3s ease-in-out;
}

.tab-content.active {
    display: block;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Form Section Styles */
.form-section {
    margin-bottom: var(--spacing-xl);
    padding-bottom: var(--spacing-lg);
    border-bottom: 1px solid rgba(212, 175, 55, 0.2);
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section-title {
    color: var(--color-gold);
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-sm);
    border-bottom: 1px solid rgba(212, 175, 55, 0.3);
}

/* Logout Section */
.logout-section {
    text-align: center;
    margin-top: var(--spacing-xl);
    padding-top: var(--spacing-lg);
}

/* Bookings Preview Styles */
.bookings-preview {
    margin-bottom: var(--spacing-lg);
}

.booking-item {
    background: var(--color-white);
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    padding: var(--spacing-md);
    margin-bottom: var(--spacing-sm);
    transition: all 0.3s ease;
}

.booking-item:hover {
    border-color: var(--color-gold);
    transform: translateY(-1px);
}

.booking-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
}

.booking-service {
    flex: 0 0 auto;
}

.service-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
}

.service-legacy {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.service-cinema {
    background: rgba(108, 117, 125, 0.2);
    color: #6c757d;
    border: 1px solid rgba(108, 117, 125, 0.3);
}

.service-cocktail {
    background: rgba(220, 53, 69, 0.2);
    color: #dc3545;
    border: 1px solid rgba(220, 53, 69, 0.3);
}

.booking-details {
    display: flex;
    gap: var(--spacing-md);
    flex: 1;
    justify-content: center;
}

.booking-details span {
    color: var(--color-cream);
    font-size: 0.9rem;
}

.booking-date {
    font-weight: 600;
    color: var(--color-gold);
}

.booking-time {
    color: var(--color-gray);
}

.booking-guests {
    color: var(--color-gray);
}

.booking-status {
    flex: 0 0 auto;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-pending {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.status-confirmed {
    background: rgba(40, 167, 69, 0.2);
    color: #28a745;
    border: 1px solid rgba(40, 167, 69, 0.3);
}

.status-completed {
    background: rgba(23, 162, 184, 0.2);
    color: #17a2b8;
    border: 1px solid rgba(23, 162, 184, 0.3);
}

.view-all-bookings {
    text-align: center;
    margin-top: var(--spacing-lg);
}

.empty-state {
    text-align: center;
    padding: var(--spacing-xl);
    color: var(--color-gray);
}

.empty-state p {
    margin-bottom: var(--spacing-md);
}

/* Saved Sets Styles */
.saved-sets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--spacing-lg);
    margin-top: var(--spacing-md);
}

.saved-set-item {
    background: var(--color-white);
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    padding: var(--spacing-lg);
    transition: all 0.3s ease;
}

.saved-set-item:hover {
    border-color: var(--color-gold);
    transform: translateY(-2px);
}

.set-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-sm);
    border-bottom: 1px solid rgba(212, 175, 55, 0.3);
}

.set-header h4 {
    color: var(--color-gold);
    margin: 0;
    font-size: 1.1rem;
}

.set-date {
    color: var(--color-gray);
    font-size: 0.9rem;
}

.set-preview {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-md);
}

.set-type {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.set-type.preset {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.set-type.custom {
    background: rgba(23, 162, 184, 0.2);
    color: #17a2b8;
    border: 1px solid rgba(23, 162, 184, 0.3);
}

.set-price, .set-items {
    color: var(--color-cream);
    font-weight: 600;
}

.set-details {
    margin-top: var(--spacing-sm);
    padding-top: var(--spacing-sm);
    border-top: 1px solid rgba(212, 175, 55, 0.1);
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-xs);
    font-size: 0.9rem;
}

.detail-label {
    color: var(--color-gray);
    font-weight: 500;
    min-width: 60px;
}

.detail-value {
    color: var(--color-cream);
    text-align: right;
    flex: 1;
}

.total-price {
    color: var(--color-gold);
    font-weight: 600;
}

.items-list {
    flex: 1;
    text-align: right;
}

.item-detail {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2px;
    font-size: 0.8rem;
}

.item-name {
    color: var(--color-cream);
    flex: 1;
    text-align: left;
}

.item-price {
    color: var(--color-gold);
    font-weight: 500;
    margin-left: var(--spacing-sm);
}

.set-actions {
    display: flex;
    gap: var(--spacing-sm);
    justify-content: flex-end;
}

.btn-sm {
    padding: var(--spacing-xs) var(--spacing-sm);
    font-size: 0.8rem;
}

.btn-danger {
    background: #dc3545;
    color: white;
    border: 1px solid #dc3545;
}

.btn-danger:hover {
    background: #c82333;
    border-color: #bd2130;
}

/* Responsive */
@media (max-width: 768px) {
    .account-tabs {
        flex-direction: column;
        align-items: center;
        gap: var(--spacing-sm);
    }
    
    .tab-btn {
        width: 100%;
        max-width: 300px;
        margin: 0;
        border-radius: 8px;
        text-align: center;
    }
    
    .booking-info {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
    
    .booking-details {
        justify-content: flex-start;
        flex-wrap: wrap;
    }
    
    .booking-status {
        align-self: flex-end;
    }
    
    .saved-sets-grid {
        grid-template-columns: 1fr;
    }
    
    .set-actions {
        justify-content: center;
    }
}

/* My Drinks Styles */
.my-drinks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--spacing-lg);
    margin-top: var(--spacing-md);
}

.saved-drink-card {
    background: var(--color-white);
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    padding: var(--spacing-lg);
    transition: all 0.3s ease;
    opacity: 1;
    transform: translateX(0);
}

.saved-drink-card:hover {
    border-color: var(--color-gold);
    transform: translateY(-2px);
}

.saved-drink-card.deleting {
    opacity: 0;
    transform: translateX(-100%);
}

.drink-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-sm);
    border-bottom: 1px solid rgba(212, 175, 55, 0.3);
}

.drink-header h4 {
    color: var(--color-gold);
    margin: 0;
    font-size: 1.1rem;
}

.saved-date {
    color: var(--color-gray);
    font-size: 0.9rem;
}

.drink-content {
    margin-bottom: var(--spacing-md);
}

.drink-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-xs) 0;
    border-bottom: 1px solid rgba(212, 175, 55, 0.1);
}

.drink-item:last-child {
    border-bottom: none;
}

.drink-item .item-name {
    color: var(--color-cream);
    font-size: 0.9rem;
}

.drink-item .item-quantity {
    color: var(--color-gold);
    font-weight: 600;
    font-size: 0.8rem;
}

.drink-actions {
    display: flex;
    gap: var(--spacing-sm);
    justify-content: flex-end;
}



.empty-icon {
    font-size: 3rem;
    margin-bottom: var(--spacing-md);
}

@media (max-width: 768px) {
    .my-drinks-grid {
        grid-template-columns: 1fr;
    }
    

    
    .drink-actions {
        justify-content: center;
    }
}























</style>

<?php include 'includes/footer.php'; ?>

