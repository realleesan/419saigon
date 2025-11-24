<?php 
$page_title = "Cinema";
require_once 'includes/config.php';

// Lấy thông tin user nếu đã đăng nhập
$user = null;
if (isLoggedIn()) {
    $pdo = getDBConnection();
    $user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->execute([$_SESSION['user_id']]);
    $user = $user_stmt->fetch();
}

// Kết nối database
$pdo = getDBConnection();

// Lấy danh sách phim với thông tin minimum spend
$movies_query = "SELECT * FROM movies WHERE is_available = 1 ORDER BY created_at DESC";
$movies_stmt = $pdo->query($movies_query);
$movies = $movies_stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách combo cinema
$combos_query = "SELECT cc.*, 
                        GROUP_CONCAT(CONCAT(mi.name, ' (x', cci.quantity, ')') ORDER BY cci.sort_order SEPARATOR ', ') as items_description
                 FROM cinema_combos cc 
                 LEFT JOIN cinema_combo_items cci ON cc.id = cci.combo_id 
                 LEFT JOIN menu_items mi ON cci.menu_item_id = mi.id 
                 WHERE cc.is_available = 1 
                 GROUP BY cc.id 
                 ORDER BY cc.sort_order";
$combos_stmt = $pdo->query($combos_query);
$combos = $combos_stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy lịch sử xem phim của user nếu đã đăng nhập
$user_movie_history = [];
if (isLoggedIn()) {
    $history_query = "SELECT umh.*, m.title, m.poster, cc.name as combo_name
                      FROM user_movie_history umh 
                      JOIN movies m ON umh.movie_id = m.id 
                      LEFT JOIN cinema_combos cc ON umh.combo_id = cc.id 
                      WHERE umh.user_id = ? 
                      ORDER BY umh.watched_at DESC 
                      LIMIT 10";
    $history_stmt = $pdo->prepare($history_query);
    $history_stmt->execute([$_SESSION['user_id']]);
    $user_movie_history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy tháng hiện tại cho calendar
$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Lấy thông tin booking cinema cho tháng hiện tại
$cinema_booking_query = "SELECT date, time, COUNT(*) as booking_count, SUM(guests) as total_guests 
                        FROM bookings 
                        WHERE booking_type = 'cinema' 
                        AND DATE_FORMAT(date, '%Y-%m') = :month 
                        GROUP BY date, time";
$cinema_booking_stmt = $pdo->prepare($cinema_booking_query);
$cinema_booking_stmt->bindParam(':month', $current_month);
$cinema_booking_stmt->execute();
$cinema_booking_result = $cinema_booking_stmt->fetchAll(PDO::FETCH_ASSOC);

$daily_cinema_bookings = [];
foreach ($cinema_booking_result as $row) {
    if (!isset($daily_cinema_bookings[$row['date']])) {
        $daily_cinema_bookings[$row['date']] = [];
    }
    $daily_cinema_bookings[$row['date']][$row['time']] = $row;
}

include 'includes/header.php'; 

// Handle booking success/error messages
if (isset($_SESSION['booking_success']) && $_SESSION['booking_success']) {
    echo '<div class="notification success">Đặt phòng thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.</div>';
    unset($_SESSION['booking_success']);
}

if (isset($_SESSION['booking_error'])) {
    echo '<div class="notification error">' . htmlspecialchars($_SESSION['booking_error']) . '</div>';
    unset($_SESSION['booking_error']);
}
?>

<!-- Hero Section -->
<section class="hero cinema-hero">
    <div class="hero-content">
        <h1 class="hero-title">419 Cinema</h1>
        <p class="hero-subtitle">Rạp chiếu phim riêng tư - Đặt riêng theo yêu cầu</p>
        <div class="hero-buttons">
            <a href="#schedule" class="btn btn-primary">Xem Lịch Chiếu</a>
            <a href="#private-booking" class="btn btn-secondary">Đặt Phòng Chiếu Riêng</a>
        </div>
    </div>
</section>

<!-- About Cinema Section -->
<section class="section">
    <div class="container">
        <div class="grid grid-2">
            <div class="cinema-content">
                <h2>Rạp Chiếu Phim Riêng</h2>
                <p><strong>Concept rạp phim:</strong> Đặt riêng theo yêu cầu - hẹn hò, nhóm bạn, tổ chức sinh nhật... Không gian riêng tư, ghế sofa êm, màn chiếu lớn.</p>
                <p><strong>Có 2 phần:</strong></p>
                <ul>
                    <li>Lịch phim cố định trong tuần</li>
                    <li>Form đặt lịch riêng</li>
                </ul>
                <p>Với công nghệ chiếu phim hiện đại, hệ thống âm thanh chất lượng cao và dịch vụ phục vụ tận tình, mỗi buổi xem phim tại 419 Cinema đều trở thành một trải nghiệm đáng nhớ.</p>
                <div class="cinema-features">
                    <div class="feature-item">
                        <span class="feature-icon"></span>
                        <span>4K Projector</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon"></span>
                        <span>Dolby Surround</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon"></span>
                        <span>Ghế Sofa VIP</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon"></span>
                        <span>Dịch vụ đồ ăn/uống</span>
                    </div>
                </div>
            </div>
            <div class="cinema-image">
                <img src="assets/images/cinema-room.jpg" alt="Cinema Room" class="lazy" data-src="assets/images/cinema-room.jpg">
            </div>
        </div>
    </div>
</section>

<!-- Calendar Section -->
<section id="schedule" class="section" style="background: var(--color-dark-gray);">
    <div class="container">
        <h2 class="section-title">Lịch Chiếu Phim Tháng <?php echo date('m/Y', strtotime($current_month)); ?></h2>
        <div class="calendar-container">
            <div class="calendar-nav">
                <a href="?month=<?php echo date('Y-m', strtotime($current_month . ' -1 month')); ?>" class="btn btn-small">&lt; Tháng trước</a>
                <span class="current-month"><?php echo date('F Y', strtotime($current_month)); ?></span>
                <a href="?month=<?php echo date('Y-m', strtotime($current_month . ' +1 month')); ?>" class="btn btn-small">Tháng sau &gt;</a>
            </div>
            
            
            <div class="calendar-grid">
                <div class="calendar-header">
                    <div>CN</div>
                    <div>T2</div>
                    <div>T3</div>
                    <div>T4</div>
                    <div>T5</div>
                    <div>T6</div>
                    <div>T7</div>
                        </div>
                        
                <?php
                $first_day = new DateTime($current_month . '-01');
                $last_day = new DateTime($current_month . '-' . $first_day->format('t'));
                $start_date = clone $first_day;
                $start_date->modify('-' . $first_day->format('w') . ' days');
                
                $current_date = clone $start_date;
                $end_date = clone $last_day;
                $end_date->modify('+' . (6 - $last_day->format('w')) . ' days');
                
                while ($current_date <= $end_date) {
                    if ($current_date->format('w') == 0) {
                        echo '<div class="calendar-week">';
                    }
                    
                    $date_str = $current_date->format('Y-m-d');
                    $is_current_month = $current_date->format('Y-m') === date('Y-m', strtotime($current_month));
                    $is_today = $date_str === date('Y-m-d');
                    $is_past = $date_str < date('Y-m-d');
                    
                    // Check if date is too far in advance (max 7 days)
                    $today = new DateTime();
                    $max_advance_date = clone $today;
                    $max_advance_date->modify('+7 days');
                    $is_too_far = $current_date > $max_advance_date;
                    
                    // Kiểm tra trạng thái booking cinema
                    $cinema_bookings = isset($daily_cinema_bookings[$date_str]) ? $daily_cinema_bookings[$date_str] : [];
                    $total_bookings = count($cinema_bookings);
                    $max_daily_slots = 3; // 3 suất chiếu mỗi ngày
                    $availability = 'available';
                    
                    if ($is_past) {
                        $availability = 'closed';
                    } elseif ($is_too_far) {
                        $availability = 'too_far';
                    } elseif ($total_bookings >= $max_daily_slots) {
                        $availability = 'full';
                    }
                    
                    $class_names = ['calendar-day'];
                    if (!$is_current_month) $class_names[] = 'other-month';
                    if ($is_today) $class_names[] = 'today';
                    if ($availability === 'closed') $class_names[] = 'closed';
                    if ($availability === 'too_far') $class_names[] = 'too-far';
                    if ($availability === 'full') $class_names[] = 'full';
                    
                    echo '<div class="' . implode(' ', $class_names) . '" data-date="' . $date_str . '">';
                    echo '<div class="day-number">' . $current_date->format('j') . '</div>';
                    
                    if ($is_current_month && !$is_past && !$is_too_far) {
                        echo '<div class="day-status">';
                        if ($availability === 'available') {
                            $remaining_slots = $max_daily_slots - $total_bookings;
                            echo '<span class="status available">Còn chỗ</span>';
                            echo '<span class="menu-type">Phòng chiếu riêng</span>';
                            echo '<div class="capacity-info">Còn ' . $remaining_slots . ' suất</div>';
                        } elseif ($availability === 'full') {
                            echo '<span class="status full">Hết chỗ</span>';
                        }
                        echo '</div>';
                        
                        if ($total_bookings > 0) {
                            echo '<div class="booking-count">' . $total_bookings . ' suất đã đặt</div>';
                        }
                        
                        // Add time slots info
                        echo '<div class="time-slots">';
                        echo '<small>Suất: 18:00, 20:30, 23:00</small>';
                        echo '</div>';
                    } elseif ($is_too_far) {
                        echo '<div class="day-status">';
                        echo '<span class="status too-far">Quá xa</span>';
                        echo '<div class="capacity-info">Chỉ đặt trước 7 ngày</div>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                    
                    if ($current_date->format('w') == 6) {
                        echo '</div>';
                    }
                    
                    $current_date->modify('+1 day');
                }
                ?>
                    </div>
                </div>
                
</section>

<!-- Movie Selection Modal -->
<div id="movieModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeMovieModal()">&times;</span>
        <h2>Chọn Phim Cho Ngày <span id="selectedDate"></span></h2>
        
        <div class="movie-selection">
            <!-- Movie Selection Options -->
            <div class="movie-selection-options">
                <h3>Chọn Loại Phim</h3>
                <div class="movie-options-tabs">
                    <button class="tab-btn active" onclick="switchMovieType('available')">Phim Có Sẵn</button>
                    <button class="tab-btn" onclick="switchMovieType('custom')">Mang Phim Riêng</button>
                </div>
                
                <!-- Available Movies -->
                <div id="available-movies" class="movie-type-content active">
                    <!-- Filter Bar -->
                    <div class="filter-bar">
                        <h4>Lọc phim:</h4>
                        <div class="filter-buttons">
                            <button class="filter-btn active" data-filter="all">Tất cả</button>
                            <button class="filter-btn" data-filter="action">Hành động</button>
                            <button class="filter-btn" data-filter="comedy">Hài</button>
                            <button class="filter-btn" data-filter="drama">Drama</button>
                            <button class="filter-btn" data-filter="horror">Kinh dị</button>
                            <button class="filter-btn" data-filter="romance">Lãng mạn</button>
                        </div>
                    </div>
                    
                    <div class="movies-grid-modal">
                        <?php foreach ($movies as $movie): ?>
                            <div class="movie-card-modal" data-movie-id="<?php echo $movie['id']; ?>" data-genre="<?php echo strtolower($movie['genre']); ?>" onclick="selectMovie(<?php echo $movie['id']; ?>)">
                                <div class="movie-poster-modal">
                                    <img src="<?php echo $movie['poster'] ?: 'assets/images/movie-default.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($movie['title']); ?>">
                                    <div class="movie-overlay">
                                        <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); selectMovie(<?php echo $movie['id']; ?>)">Chọn phim</button>
                                    </div>
                                </div>
                                <div class="movie-info-modal">
                                    <h4><?php echo htmlspecialchars($movie['title']); ?></h4>
                                    <p class="movie-genre"><?php echo htmlspecialchars($movie['genre']); ?></p>
                                    <p class="movie-duration"><?php echo $movie['duration']; ?> phút</p>
                                    
                                    <!-- Movie Rating -->
                                    <div class="movie-rating">
                                        <?php 
                                        $rating = 4.2; // Default rating, can be from database
                                        for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $rating ? '' : 'empty'; ?>">★</span>
                                        <?php endfor; ?>
                                        <span class="rating-text"><?php echo $rating; ?></span>
                                    </div>
                                    
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Custom Movie Option -->
                <div id="custom-movie" class="movie-type-content">
                    <div class="custom-movie-info">
                        <h3>Mang Phim Riêng</h3>
                        <p>Bạn có thể mang phim riêng của mình (USB, DVD, hoặc file video). Chúng tôi sẽ hỗ trợ các định dạng phổ biến.</p>
                        
                        <div class="custom-movie-requirements">
                            <h4>Yêu cầu:</h4>
                            <ul>
                                <li>Định dạng: MP4, AVI, MKV, MOV</li>
                                <li>Chất lượng: HD trở lên</li>
                                <li>Thời lượng: Tối đa 3 giờ</li>
                            </ul>
                        </div>
                        
                        <button class="btn btn-primary" onclick="selectCustomMovie()">Chọn Mang Phim Riêng</button>
                    </div>
                </div>
                
                <!-- Cinema Specials -->
                <div class="cinema-specials-section">
                    <h3>Cinema Specials</h3>
                    <p>Combo "Phim + Đồ ăn/uống" với giá trọn gói:</p>
                    <div class="combos-grid-modal">
                        <?php foreach ($combos as $combo): ?>
                            <div class="combo-card-modal" data-combo-id="<?php echo $combo['id']; ?>" onclick="selectCombo(<?php echo $combo['id']; ?>)">
                                <div class="combo-header-modal">
                                    <h4><?php echo htmlspecialchars($combo['name']); ?></h4>
                                    <div class="combo-price-modal"><?php echo number_format($combo['price']); ?> VNĐ</div>
                                </div>
                                <div class="combo-description-modal">
                                    <p><?php echo htmlspecialchars($combo['description']); ?></p>
                                </div>
                                <div class="combo-items-modal">
                                    <small><?php echo htmlspecialchars($combo['items_description'] ?? 'Chi tiết sẽ được cập nhật'); ?></small>
                                </div>
                                <div class="combo-actions">
                                    <button class="btn btn-outline btn-sm" onclick="event.stopPropagation(); viewComboDetail(<?php echo $combo['id']; ?>)">Chi tiết</button>
                                    <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); selectCombo(<?php echo $combo['id']; ?>)">Chọn combo</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Movie Summary -->
            <div class="movie-summary">
                <h3>Tổng Kết Đặt Phim</h3>
                
                <!-- Guest Selection -->
                <div class="guest-selection">
                    <label for="guestCount">Số người đi:</label>
                    <select id="guestCount" onchange="updateMinimumRequirement()">
                        <option value="1">1 người</option>
                        <option value="2" selected>2 người</option>
                        <option value="3">3 người</option>
                        <option value="4">4 người</option>
                        <option value="5">5 người</option>
                        <option value="6">6 người</option>
                        <option value="7">7 người</option>
                        <option value="8">8 người</option>
                    </select>
                </div>
                
                <div id="selectedMovieInfo"></div>
                <div id="selectedComboInfo"></div>
                
                <div class="summary-controls" id="summaryControls" style="display: none;">
                        <button type="button" class="btn btn-outline btn-sm" onclick="clearAllSelections()">
                        Xóa Tất Cả
                    </button>
                </div>
                
                <div class="total-section">
                    <div class="total-amount">
                        <strong>Tổng tiền: <span id="totalAmount">0</span> VNĐ</strong>
                    </div>
                    <div class="requirement-status" id="requirementStatus">
                        <span class="status-text">Chưa chọn phim</span>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-outline" onclick="closeMovieModal()">Hủy</button>
                    <button class="btn btn-primary" onclick="proceedToBooking()" id="proceedBtn" disabled>Tiếp Tục Đặt Vé</button>
                </div>
            </div>
        </div>
    </div>
</div>
                
<!-- Booking Modal -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeBookingModal()">&times;</span>
        <h2>Đặt Vé Xem Phim Cho Ngày <span id="bookingDate"></span></h2>
        
        <div class="booking-summary">
            <h3>Phim & Combo Đã Chọn</h3>
            <div id="bookingSelectedMovie"></div>
            <div id="bookingSelectedCombo"></div>
            <div class="booking-total">
                <strong>Tổng tiền: <span id="bookingTotalAmount">0</span> VNĐ</strong>
                <div class="booking-guest-info">
                    <small>Số người: <span id="bookingGuestCount">2</span> người</small>
                </div>
            </div>
        </div>
            
        <form class="booking-form-modal" action="process_booking.php" method="POST">
            <input type="hidden" name="service_type" value="cinema">
            <input type="hidden" name="date" id="hiddenSelectedDate">
            <input type="hidden" name="selected_movie_id" id="hiddenSelectedMovieId">
            <input type="hidden" name="selected_combo_id" id="hiddenSelectedComboId">
            <input type="hidden" name="movie_title" id="hiddenMovieTitle">
            <input type="hidden" name="combo_name" id="hiddenComboName">
            <input type="hidden" name="combo_price" id="hiddenComboPrice">
            <?php if (isLoggedIn()): ?>
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
            <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                    <label for="booking_time">Giờ chiếu *</label>
                    <select id="booking_time" name="time" required>
                                <option value="">Chọn giờ</option>
                                <option value="18:00">18:00</option>
                        <option value="20:30">20:30</option>
                        <option value="23:00">23:00</option>
                            </select>
                    </div>
                    <div class="form-group">
                    <label for="booking_guests">Số khách *</label>
                    <select id="booking_guests" name="guests" required>
                            <option value="">Chọn số khách</option>
                            <option value="1">1 người</option>
                            <option value="2">2 người</option>
                            <option value="3">3 người</option>
                            <option value="4">4 người</option>
                            <option value="5">5 người</option>
                            <option value="6">6 người</option>
                            <option value="7">7 người</option>
                            <option value="8">8 người</option>
                        </select>
                    </div>
            </div>
            
                    <div class="form-group">
                <label for="booking_name">Họ và tên *</label>
                <input type="text" id="booking_name" name="name" value="<?php echo isLoggedIn() ? htmlspecialchars($_SESSION['user_name']) : ''; ?>" required>
                    </div>
            
                    <div class="form-group">
                <label for="booking_email">Email *</label>
                <input type="email" id="booking_email" name="email" value="<?php echo isLoggedIn() ? htmlspecialchars($_SESSION['user_email']) : ''; ?>" required>
                    </div>
            
            <div class="form-group">
                <label for="booking_phone">Số điện thoại *</label>
                <input type="tel" id="booking_phone" name="phone" value="<?php echo isLoggedIn() ? htmlspecialchars($user['phone'] ?? '') : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="booking_special_requests">Yêu cầu đặc biệt</label>
                <textarea id="booking_special_requests" name="special_requests" rows="3" placeholder="Yêu cầu về đồ uống, setup phòng..."></textarea>
        </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="goBackToMovieSelection()">Quay Lại</button>
                <button type="submit" class="btn btn-primary">Đặt Vé Ngay</button>
                    </div>
        </form>
                        </div>
                    </div>

<style>
/* Cinema specific styles (updated to light theme) */
.cinema-hero {
    background: linear-gradient(rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.35)), url('assets/images/cinema-hero.jpg');
    background-size: cover;
    background-position: center;
    height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.hero-buttons {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
    justify-content: center;
}

.cinema-content {
    padding: var(--spacing-xl);
}

.cinema-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 8px;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
}

.feature-item .feature-icon { font-size: 1.5rem; }

/* Calendar Styles - CHỈ ĐEN VÀ TRẮNG */
.calendar-container {
    max-width: 1000px;
    margin: 0 auto;
    background: var(--color-white);
    border-radius: 12px;
    padding: var(--spacing-xl);
    border: 2px solid var(--color-black);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
}

.calendar-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
    padding-bottom: var(--spacing-md);
    border-bottom: 1px solid rgba(0, 0, 0, 0.12);
}

.current-month { font-size: 1.5rem; font-weight: 600; color: var(--color-black); }

.calendar-grid {
    background: var(--color-white);
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.08);
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: var(--color-black);
    color: var(--color-white);
    font-weight: 600;
}

.calendar-header > div { padding: var(--spacing-md); text-align: center; border-right: 1px solid rgba(255, 255, 255, 0.1); }
.calendar-header > div:last-child { border-right: none; }

.calendar-week { display: grid; grid-template-columns: repeat(7, 1fr); }

.calendar-day {
    min-height: 100px;
    padding: var(--spacing-sm);
    border-right: 1px solid rgba(0, 0, 0, 0.06);
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    background: var(--color-white);
    color: var(--color-black);
}

.calendar-day:hover { background: rgba(0, 0, 0, 0.06); }
.calendar-day.other-month { opacity: 0.6; cursor: not-allowed; }
.calendar-day.today { background: rgba(0, 0, 0, 0.14); border: 2px solid var(--color-black); }
.calendar-day.closed { background: rgba(0, 0, 0, 0.06); cursor: not-allowed; }
.calendar-day.full { background: rgba(0, 0, 0, 0.06); cursor: not-allowed; }
.calendar-day.too-far { background: rgba(0, 0, 0, 0.06); cursor: not-allowed; }
.calendar-day.selected { background: var(--color-black); color: var(--color-white); border: 2px solid var(--color-black); }

.calendar-day.selected .day-number,
.calendar-day.selected .status,
.calendar-day.selected .menu-type,
.calendar-day.selected .capacity-info,
.calendar-day.selected .booking-count,
.calendar-day.selected small { color: var(--color-black); }

.day-number { font-weight: 600; margin-bottom: var(--spacing-xs); color: var(--color-black); }
.day-status { margin-bottom: var(--spacing-xs); }
.status { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; margin-bottom: var(--spacing-xs); }
.status.available { background: rgba(0, 0, 0, 0.12); color: var(--color-black); }
.status.full { background: rgba(0, 0, 0, 0.12); color: var(--color-black); }
.status.too-far { background: rgba(0, 0, 0, 0.12); color: var(--color-black); }
.menu-type { display: block; font-size: 0.7rem; color: var(--color-light-gray); margin-bottom: var(--spacing-xs); }
.capacity-info { font-size: 0.7rem; color: var(--color-black); font-weight: 500; }
.booking-count { font-size: 0.7rem; color: var(--color-dark-gray); margin-top: var(--spacing-xs); }
.time-slots { margin-top: var(--spacing-xs); }
.time-slots small { color: var(--color-light-gray); font-size: 0.7rem; }

/* Movie items & modals - CHỈ ĐEN VÀ TRẮNG */
.movie-schedule { display: grid; gap: var(--spacing-lg); }
.movie-item { display: grid; grid-template-columns: 120px 1fr; gap: var(--spacing-lg); background: var(--color-white); border-radius: 8px; padding: var(--spacing-lg); border: 1px solid rgba(0, 0, 0, 0.06); color: var(--color-black); }
.movie-poster img { border-radius: 4px; }
.movie-info h3 { color: var(--color-black); margin-bottom: var(--spacing-sm); }
.movie-genre, .movie-duration, .movie-language { color: var(--color-light-gray); }
.minimum-spend { background: var(--color-white); padding: var(--spacing-sm); border-radius: 8px; margin-bottom: var(--spacing-md); border: 1px solid rgba(0, 0, 0, 0.06); }
.spend-label { color: var(--color-black); }
.spend-amount { color: var(--color-black); }
.time-slot { background: var(--color-white); color: var(--color-black); padding: 4px 12px; border-radius: 12px; font-size: 0.9rem; cursor: pointer; transition: all var(--transition-normal); border: 1px solid rgba(0,0,0,0.1); }
.time-slot:hover { background: var(--color-black); color: var(--color-white); }
.minimum-spend-info { background: var(--color-white); padding: var(--spacing-lg); border-radius: 8px; margin-bottom: var(--spacing-lg); border: 1px solid rgba(0, 0, 0, 0.06); }

/* Modals - CHỈ ĐEN VÀ TRẮNG */
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.6); }
.modal-content { background: var(--color-white); margin: 2% auto; padding: var(--spacing-xl); border: 2px solid var(--color-black); border-radius: 12px; width: 90%; max-width: 1000px; max-height: 90vh; overflow-y: auto; position: relative; color: var(--color-black); }
.modal-content h2 { color: var(--color-black); margin-bottom: var(--spacing-lg); text-align: center; }
.close { color: var(--color-black); float: right; font-size: 28px; font-weight: bold; cursor: pointer; position: absolute; top: var(--spacing-md); right: var(--spacing-lg); }
.close:hover, .close:focus { color: var(--color-black); text-decoration: none; }

.movie-selection { display: grid; grid-template-columns: 2fr 1fr; gap: var(--spacing-xl); margin-bottom: var(--spacing-xl); }
.movie-selection-options { background: var(--color-white); padding: var(--spacing-lg); border-radius: 8px; border: 1px solid rgba(0, 0, 0, 0.06); }
.tab-btn { background: transparent; border: 1px solid var(--color-gray); color: var(--color-black); padding: var(--spacing-sm) var(--spacing-md); border-radius: 4px; cursor: pointer; transition: all var(--transition-normal); font-size: 0.9rem; }
.tab-btn.active { background: var(--color-black); color: var(--color-white); border-color: var(--color-black); }
.filter-btn { background: var(--color-white); border: 1px solid var(--color-gray); color: var(--color-black); padding: var(--spacing-xs) var(--spacing-sm); border-radius: 12px; cursor: pointer; transition: all var(--transition-normal); font-size: 0.8rem; }
.filter-btn.active { background: var(--color-black); color: var(--color-white); border-color: var(--color-black); }

.custom-movie-info { text-align: center; padding: var(--spacing-xl); }
.custom-movie-info h3 { color: var(--color-black); }
.custom-movie-requirements { background: var(--color-white); padding: var(--spacing-lg); border-radius: 8px; margin-bottom: var(--spacing-lg); border: 1px solid rgba(0, 0, 0, 0.06); }

.movie-card-modal { background: var(--color-white); border-radius: 12px; padding: var(--spacing-md); border: 1px solid rgba(0, 0, 0, 0.06); transition: all var(--transition-normal); cursor: pointer; text-align: center; position: relative; overflow: hidden; }
.movie-card-modal:hover { transform: translateY(-4px); border-color: var(--color-black); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08); }
.movie-card-modal.selected { background: var(--color-black); color: var(--color-white); border-color: var(--color-black); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12); }

.combo-card-modal, .movie-card-modal, .movie-poster-modal { color: var(--color-dark-gray); }
.combo-items-modal small { background: rgba(0,0,0,0.03); color: var(--color-dark-gray); }

/* Form and summary - CHỈ ĐEN VÀ TRẮNG */
.movie-summary { background: var(--color-white); padding: var(--spacing-lg); border-radius: 8px; border: 1px solid rgba(0, 0, 0, 0.06); position: sticky; top: var(--spacing-lg); max-height: 80vh; overflow-y: auto; color: var(--color-black); }
.guest-selection label { color: var(--color-black); }
.guest-selection select { background: var(--color-white); border: 1px solid var(--color-gray); color: var(--color-black); }
.total-section { margin-bottom: var(--spacing-lg); padding: var(--spacing-md); background: var(--color-white); border-radius: 8px; border: 1px solid rgba(0, 0, 0, 0.06); }
.requirement-status .status-text { color: var(--color-black); }

/* Inputs */
.form-group label { color: var(--color-dark-gray); }
.form-group input, .form-group select, .form-group textarea { background: var(--color-white); color: var(--color-dark-gray); border: 1px solid var(--color-gray); }

/* Responsive tweaks (keep mostly same) */
@media (max-width: 768px) {
    .movie-selection { grid-template-columns: 1fr; gap: var(--spacing-lg); }
    .modal-content { width: 95%; margin: 5% auto; padding: var(--spacing-lg); }
    .movies-grid-modal { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: var(--spacing-sm); }
    .combos-grid-modal { grid-template-columns: 1fr; gap: var(--spacing-sm); }
    .movie-poster-modal { height: 120px; }
    .modal-actions { flex-direction: column; gap: var(--spacing-sm); }
    .modal-actions .btn { width: 100%; }
    .form-row { grid-template-columns: 1fr; }
    .cinema-content { padding: var(--spacing-md); }
    .calendar-container { padding: var(--spacing-md); }
    .calendar-day { min-height: 80px; padding: var(--spacing-xs); }
}
</style>

<script>
// Cinema data from PHP
const moviesData = <?php echo json_encode($movies, JSON_UNESCAPED_UNICODE); ?>;
const combosData = <?php echo json_encode($combos, JSON_UNESCAPED_UNICODE); ?>;

// Global variables
let selectedMovie = null;
let selectedCombo = null;
let currentDate = null;
let currentMovieType = 'available';
let guestCount = 2;

// Initialize calendar on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add click event to available calendar days
    document.querySelectorAll('.calendar-day:not(.other-month):not(.closed):not(.full):not(.too-far)').forEach(day => {
        day.addEventListener('click', function() {
            const date = this.dataset.date;
            openMovieModal(date);
        });
    });
    
    // Add filter functionality
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filter movies
            document.querySelectorAll('.movie-card-modal').forEach(card => {
                const genre = card.dataset.genre;
                if (filter === 'all' || genre.includes(filter)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});

// Open movie selection modal
function openMovieModal(date) {
    currentDate = date;
    selectedMovie = null;
    selectedCombo = null;
    currentMovieType = 'available';
    
    // Reset selections
    document.querySelectorAll('.movie-card-modal').forEach(card => {
        card.classList.remove('selected');
    });
    document.querySelectorAll('.combo-card-modal').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Reset tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelectorAll('.movie-type-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Set default active tab
    document.querySelector('.tab-btn').classList.add('active');
    document.getElementById('available-movies').classList.add('active');
    
    // Update modal title
    document.getElementById('selectedDate').textContent = formatDate(date);
    
    // Reset guest count
    document.getElementById('guestCount').value = '2';
    guestCount = 2;
    
    // Update summary
    updateMovieSummary();
    
    // Show modal
    document.getElementById('movieModal').style.display = 'block';
    
    // Highlight the selected date
    document.querySelectorAll('.calendar-day').forEach(day => {
        day.classList.remove('selected');
    });
    
    const selectedDay = document.querySelector(`[data-date="${date}"]`);
    if (selectedDay) {
        selectedDay.classList.add('selected');
    }
}

// Switch movie type tabs
function switchMovieType(type) {
    currentMovieType = type;
    
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[onclick="switchMovieType('${type}')"]`).classList.add('active');
    
    // Update content
    document.querySelectorAll('.movie-type-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(type === 'available' ? 'available-movies' : 'custom-movie').classList.add('active');
}

// Select custom movie (toggle)
function selectCustomMovie() {
    if (selectedMovie && selectedMovie.id === 'custom') {
        // Deselect custom movie
        selectedMovie = null;
    } else {
        // Select custom movie
        selectedMovie = {
            id: 'custom',
            title: 'Mang Phim Riêng',
            genre: 'Tùy chọn',
            duration: 'Tùy chọn'
        };
    }
    
    updateMovieSummary();
    updateRequirementStatus();
}

// Update guest count when changed
function updateMinimumRequirement() {
    guestCount = parseInt(document.getElementById('guestCount').value);
    updateMovieSummary();
    updateRequirementStatus();
}

// Update movie summary
function updateMovieSummary() {
    const movieInfo = document.getElementById('selectedMovieInfo');
    const comboInfo = document.getElementById('selectedComboInfo');
    const totalAmount = document.getElementById('totalAmount');
    
    // Update movie info
    if (selectedMovie) {
        movieInfo.innerHTML = `
            <div class="selected-item">
                <strong>Phim:</strong> ${selectedMovie.title}
                <br><small>Thể loại: ${selectedMovie.genre} | Thời lượng: ${selectedMovie.duration} phút</small>
            </div>
        `;
    } else {
        movieInfo.innerHTML = '<div class="selected-item"><small>Chưa chọn phim</small></div>';
    }
    
    // Update combo info
    if (selectedCombo) {
        comboInfo.innerHTML = `
            <div class="selected-item">
                <strong>Combo:</strong> ${selectedCombo.name}
                <br><small>Giá: ${parseFloat(selectedCombo.price).toLocaleString('vi-VN')} VNĐ</small>
            </div>
        `;
        
        const comboPrice = parseFloat(selectedCombo.price) || 0;
        totalAmount.textContent = comboPrice.toLocaleString('vi-VN');
    } else {
        comboInfo.innerHTML = '<div class="selected-item"><small>Không chọn combo</small></div>';
        totalAmount.textContent = '0';
    }
    
    // Show/hide summary controls
    const summaryControls = document.getElementById('summaryControls');
    if (selectedMovie || selectedCombo) {
        summaryControls.style.display = 'block';
    } else {
        summaryControls.style.display = 'none';
    }
}

// Update requirement status
function updateRequirementStatus() {
    const statusElement = document.getElementById('requirementStatus');
    const proceedBtn = document.getElementById('proceedBtn');
    
    if (!selectedMovie) {
        statusElement.className = 'requirement-status error';
        statusElement.querySelector('.status-text').textContent = 'Chưa chọn phim';
        proceedBtn.disabled = true;
        return;
    }
    
    statusElement.className = 'requirement-status success';
    statusElement.querySelector('.status-text').textContent = 'Sẵn sàng đặt vé!';
    proceedBtn.disabled = false;
}

// Clear all selections
function clearAllSelections() {
    selectedMovie = null;
    selectedCombo = null;
    
    document.querySelectorAll('.movie-card-modal').forEach(card => {
        card.classList.remove('selected');
    });
    document.querySelectorAll('.combo-card-modal').forEach(card => {
        card.classList.remove('selected');
    });
    
    updateMovieSummary();
    updateRequirementStatus();
}

// View combo detail
function viewComboDetail(comboId) {
    const combo = combosData.find(c => c.id == comboId);
    if (combo) {
        alert(`Chi tiết combo: ${combo.name}\n\nMô tả: ${combo.description}\n\nCác món: ${combo.items_description || 'Chi tiết sẽ được cập nhật'}`);
    }
}

// Close movie modal
function closeMovieModal() {
    document.getElementById('movieModal').style.display = 'none';
    
    // Remove calendar day selection
    document.querySelectorAll('.calendar-day').forEach(day => {
        day.classList.remove('selected');
    });
}

// Select movie (toggle)
function selectMovie(movieId) {
    const selectedCard = document.querySelector(`[data-movie-id="${movieId}"]`);
    const isCurrentlySelected = selectedCard.classList.contains('selected');
    
    if (isCurrentlySelected) {
        // Deselect movie
        selectedMovie = null;
        selectedCard.classList.remove('selected');
    } else {
        // Select movie (deselect others first)
        document.querySelectorAll('.movie-card-modal').forEach(card => {
            card.classList.remove('selected');
        });
        
        selectedMovie = moviesData.find(movie => movie.id == movieId);
        selectedCard.classList.add('selected');
    }
    
    // Update summary and status
    updateMovieSummary();
    updateRequirementStatus();
}

// Select combo (toggle)
function selectCombo(comboId) {
    const selectedCard = document.querySelector(`[data-combo-id="${comboId}"]`);
    const isCurrentlySelected = selectedCard.classList.contains('selected');
    
    if (isCurrentlySelected) {
        // Deselect combo
        selectedCombo = null;
        selectedCard.classList.remove('selected');
    } else {
        // Select combo (deselect others first)
        document.querySelectorAll('.combo-card-modal').forEach(card => {
            card.classList.remove('selected');
        });
        
        selectedCombo = combosData.find(combo => combo.id == comboId);
        selectedCard.classList.add('selected');
    }
    
    // Update summary and status
    updateMovieSummary();
    updateRequirementStatus();
}

// Proceed to booking modal
function proceedToBooking() {
    if (!selectedMovie) {
        alert('Vui lòng chọn phim trước khi tiếp tục!');
        return;
    }
    
    // Close movie modal
    document.getElementById('movieModal').style.display = 'none';
    
    // Update booking modal
    document.getElementById('bookingDate').textContent = formatDate(currentDate);
    document.getElementById('hiddenSelectedDate').value = currentDate;
    document.getElementById('hiddenSelectedMovieId').value = selectedMovie.id;
    document.getElementById('hiddenSelectedComboId').value = selectedCombo ? selectedCombo.id : '';
    document.getElementById('hiddenMovieTitle').value = selectedMovie.title;
    document.getElementById('hiddenComboName').value = selectedCombo ? selectedCombo.name : '';
    document.getElementById('hiddenComboPrice').value = selectedCombo ? selectedCombo.price : '0';
    
    // Update booking summary
    updateBookingSummary();
    
    // Show booking modal
    document.getElementById('bookingModal').style.display = 'block';
}

// Update booking summary
function updateBookingSummary() {
    const movieSummary = document.getElementById('bookingSelectedMovie');
    const comboSummary = document.getElementById('bookingSelectedCombo');
    const totalAmountElement = document.getElementById('bookingTotalAmount');
    
    // Movie summary
    movieSummary.innerHTML = `
        <div class="selected-item">
            <strong>Phim:</strong> ${selectedMovie.title}
            <br><small>Thể loại: ${selectedMovie.genre} | Thời lượng: ${selectedMovie.duration} phút</small>
        </div>
    `;
    
    // Combo summary
    if (selectedCombo) {
        comboSummary.innerHTML = `
            <div class="selected-item">
                <strong>Combo:</strong> ${selectedCombo.name}
                <br><small>Giá: ${parseFloat(selectedCombo.price).toLocaleString('vi-VN')} VNĐ</small>
            </div>
        `;
        
        const comboPrice = parseFloat(selectedCombo.price) || 0;
        totalAmountElement.textContent = comboPrice.toLocaleString('vi-VN');
    } else {
        comboSummary.innerHTML = `
            <div class="selected-item">
                <small>Không chọn combo</small>
            </div>
        `;
        totalAmountElement.textContent = '0';
    }
    
    // Update guest count
    const guests = parseInt(document.getElementById('booking_guests').value) || 1;
    document.getElementById('bookingGuestCount').textContent = guests;
}

// Close booking modal
function closeBookingModal() {
    document.getElementById('bookingModal').style.display = 'none';
    
    // Remove calendar day selection
    document.querySelectorAll('.calendar-day').forEach(day => {
        day.classList.remove('selected');
    });
}

// Go back to movie selection
function goBackToMovieSelection() {
    document.getElementById('bookingModal').style.display = 'none';
    document.getElementById('movieModal').style.display = 'block';
}

// Format date for display
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Update minimum spend when guests change
document.addEventListener('DOMContentLoaded', function() {
    const guestsSelect = document.getElementById('booking_guests');
    if (guestsSelect) {
        guestsSelect.addEventListener('change', function() {
            if (selectedMovie) {
                updateBookingSummary();
            }
        });
    }
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const movieModal = document.getElementById('movieModal');
    const bookingModal = document.getElementById('bookingModal');
    
    if (event.target === movieModal) {
        closeMovieModal();
    }
    
    if (event.target === bookingModal) {
        closeBookingModal();
    }
});

// Form submission with validation
document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.querySelector('.booking-form-modal');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
    if (!selectedMovie) {
        e.preventDefault();
                alert('Vui lòng chọn phim trước khi đặt vé!');
        return false;
    }
    
            const time = document.getElementById('booking_time').value;
            const guests = document.getElementById('booking_guests').value;
            
            if (!time) {
        e.preventDefault();
        alert('Vui lòng chọn giờ chiếu!');
        return false;
    }
            
            if (!guests) {
                e.preventDefault();
                alert('Vui lòng chọn số khách!');
                return false;
            }
    
    // Form data is already set in hidden fields, no need to append
    
    return true;
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
