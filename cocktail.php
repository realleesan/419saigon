<?php 
$page_title = "Cocktail";
include 'includes/header.php';

// Check if this is an AJAX request
$is_ajax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

// Kết nối database
require_once 'includes/config.php';
$pdo = getDBConnection();

// Tạo bảng cocktail_reviews nếu chưa tồn tại
try {
    $create_table_query = "CREATE TABLE IF NOT EXISTS `cocktail_reviews` (
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
        KEY `idx_cocktail_reviews_approved` (`is_approved`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($create_table_query);
    
    // Thêm dữ liệu mẫu nếu bảng trống
    $check_data = $pdo->query("SELECT COUNT(*) FROM cocktail_reviews");
    if ($check_data->fetchColumn() == 0) {
        $insert_sample = "INSERT INTO `cocktail_reviews` (`menu_item_id`, `user_id`, `customer_name`, `rating`, `comment`, `is_approved`) VALUES
            (1, NULL, 'Nguyễn Văn A', 5, 'Cocktail rất ngon, hương vị độc đáo!', 1),
            (1, NULL, 'Trần Thị B', 4, 'Phong cách pha chế chuyên nghiệp', 1),
            (2, NULL, 'Lê Văn C', 5, 'Hương vị hoàn hảo, sẽ quay lại!', 1),
            (2, NULL, 'Phạm Thị D', 4, 'Cocktail đẹp mắt và ngon miệng', 1),
            (3, NULL, 'Hoàng Văn E', 5, 'Rất thích hương vị này!', 1)";
        $pdo->exec($insert_sample);
    }
} catch (PDOException $e) {
    // Bỏ qua lỗi nếu bảng đã tồn tại
    error_log("Cocktail reviews table creation: " . $e->getMessage());
}

// Thêm cột images cho menu_items nếu chưa có
try {
    $pdo->exec("ALTER TABLE `menu_items` ADD COLUMN IF NOT EXISTS `images` TEXT DEFAULT NULL COMMENT 'JSON array of image URLs'");
    
    // Cập nhật một số cocktail với ảnh mẫu nếu chưa có
    $check_images = $pdo->query("SELECT COUNT(*) FROM menu_items WHERE images IS NOT NULL");
    if ($check_images->fetchColumn() == 0) {
        $update_images = "UPDATE `menu_items` 
                         SET `images` = '[\"assets/images/cocktail-default.jpg\"]'
                         WHERE `id` IN (1, 2, 3, 4, 5, 6, 7)";
        $pdo->exec($update_images);
    }
} catch (PDOException $e) {
    // Bỏ qua lỗi nếu cột đã tồn tại
    error_log("Menu items images column: " . $e->getMessage());
}

// Lấy danh sách cocktail từ database với thông tin đánh giá
$cocktail_query = "SELECT m.*, c.name as category_name,
                          COALESCE(AVG(cr.rating), 4.0) as avg_rating,
                          COUNT(cr.id) as review_count
                   FROM menu_items m 
                   JOIN categories c ON m.category_id = c.id 
                   LEFT JOIN cocktail_reviews cr ON m.id = cr.menu_item_id AND cr.is_approved = 1
                   WHERE c.type = 'drink' AND m.is_available = 1
                   GROUP BY m.id, c.name
                   ORDER BY c.name, m.sort_order, m.name";
$cocktail_stmt = $pdo->query($cocktail_query);
$cocktail_items = $cocktail_stmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination logic
$items_per_page = 9;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$total_items = count($cocktail_items);
$total_pages = ceil($total_items / $items_per_page);
$offset = ($current_page - 1) * $items_per_page;

// Get items for current page
$cocktail_items_paginated = array_slice($cocktail_items, $offset, $items_per_page);

// Lấy reviews cho từng cocktail
$cocktail_reviews = [];
foreach ($cocktail_items as $item) {
    $reviews_query = "SELECT cr.*, 
                             CONCAT(u.first_name, ' ', u.last_name) as user_name 
                      FROM cocktail_reviews cr 
                      LEFT JOIN users u ON cr.user_id = u.id 
                      WHERE cr.menu_item_id = ? AND cr.is_approved = 1 
                      ORDER BY cr.created_at DESC 
                      LIMIT 5";
    $reviews_stmt = $pdo->prepare($reviews_query);
    $reviews_stmt->execute([$item['id']]);
    $cocktail_reviews[$item['id']] = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy thông tin user nếu đã đăng nhập
$user = null;
if (isLoggedIn()) {
    $user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->execute([$_SESSION['user_id']]);
    $user = $user_stmt->fetch();
}

// Lấy thông tin booking cho cocktail
$booking_query = "SELECT date, COUNT(*) as booking_count, SUM(guests) as total_guests 
                  FROM bookings 
                  WHERE booking_type = 'cocktail' 
                  AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
                  GROUP BY date";
$booking_stmt = $pdo->query($booking_query);
$booking_result = $booking_stmt->fetchAll(PDO::FETCH_ASSOC);

$daily_bookings = [];
foreach ($booking_result as $row) {
    $daily_bookings[$row['date']] = $row;
}

// Phân loại cocktail theo category
$cocktails_by_category = [];
foreach ($cocktail_items as $item) {
    $category = strtolower($item['category_name']);
    if (!isset($cocktails_by_category[$category])) {
        $cocktails_by_category[$category] = [];
    }
    $cocktails_by_category[$category][] = $item;
}

// Lấy signature cocktails
$signature_cocktails = array_filter($cocktail_items, function($item) {
    return $item['is_featured'] == 1;
});

// Helper function to determine strength level
function getStrengthLevel($cocktail_name) {
    $light_cocktails = ['Virgin Mojito', 'Sunset Breeze', 'Mocktail'];
    $strong_cocktails = ['Old Fashioned', 'Martini', 'Negroni'];
    
    $name_lower = strtolower($cocktail_name);
    
    if (in_array($cocktail_name, $light_cocktails) || strpos($name_lower, 'mocktail') !== false) {
        return '<span class="strength light">Light</span>';
    } elseif (in_array($cocktail_name, $strong_cocktails) || strpos($name_lower, 'whisky') !== false || strpos($name_lower, 'gin') !== false) {
        return '<span class="strength strong">Strong</span>';
    } else {
        return '<span class="strength medium">Medium</span>';
    }
}

// Helper function to get strength level value for filtering
function getStrengthLevelValue($cocktail_name) {
    $light_cocktails = ['Virgin Mojito', 'Sunset Breeze', 'Mocktail'];
    $strong_cocktails = ['Old Fashioned', 'Martini', 'Negroni'];
    
    $name_lower = strtolower($cocktail_name);
    
    if (in_array($cocktail_name, $light_cocktails) || strpos($name_lower, 'mocktail') !== false) {
        return 'light';
    } elseif (in_array($cocktail_name, $strong_cocktails) || strpos($name_lower, 'whisky') !== false || strpos($name_lower, 'gin') !== false) {
        return 'strong';
    } else {
        return 'medium';
    }
}

// If AJAX request, only render the necessary parts
if ($is_ajax) {
    // Render only menu items and pagination
    ?>
    <!-- Menu Items Grid -->
    <div class="menu-items-grid">
        <?php foreach ($cocktail_items_paginated as $item): ?>
        <div class="menu-item-card" 
             data-category="<?php echo strtolower($item['category_name']); ?>" 
             data-id="<?php echo $item['id']; ?>"
             data-price="<?php echo $item['price']; ?>"
             data-rating="<?php echo $item['avg_rating']; ?>"
             data-strength="<?php echo getStrengthLevelValue($item['name']); ?>"
             data-name="<?php echo strtolower($item['name']); ?>">
            
            <div class="item-image">
                <?php 
                $images = [];
                if ($item['images']) {
                    $decoded_images = json_decode($item['images'], true);
                    if (is_array($decoded_images) && !empty($decoded_images)) {
                        $images = $decoded_images;
                    }
                }
                if (empty($images)) {
                    $images = ['assets/images/cocktail-default.jpg'];
                }
                $main_image = $images[0];
                ?>
                <img src="<?php echo htmlspecialchars($main_image); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="lazy">
                <?php if ($item['is_featured']): ?>
                <div class="featured-badge">Featured</div>
                <?php endif; ?>
            </div>
            
            <div class="item-content">
                <div class="item-header">
                    <h4 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                    <div class="item-rating">
                        <?php
                        $rating = round($item['avg_rating'], 1);
                        for ($i = 1; $i <= 5; $i++) {
                            $starClass = $i <= $rating ? 'star' : 'star empty';
                            echo '<span class="' . $starClass . '">★</span>';
                        }
                        ?>
                        <span class="rating-text"><?php echo $rating; ?></span>
                    </div>
                </div>
                
                <p class="item-description"><?php echo htmlspecialchars(substr($item['description'], 0, 80)) . (strlen($item['description']) > 80 ? '...' : ''); ?></p>
                
                <div class="item-ingredients">
                    <?php
                    if ($item['ingredients']) {
                        $ingredients = explode(',', $item['ingredients']);
                        foreach (array_slice($ingredients, 0, 2) as $ingredient) {
                            echo '<span class="ingredient-tag">' . htmlspecialchars(trim($ingredient)) . '</span>';
                        }
                        if (count($ingredients) > 2) {
                            echo '<span class="ingredient-more">+' . (count($ingredients) - 2) . '</span>';
                        }
                    }
                    ?>
                </div>
                
                <div class="item-meta">
                    <span class="strength-level"><?php echo getStrengthLevel($item['name']); ?></span>
                    <span class="price"><?php echo number_format($item['price']); ?> VNĐ</span>
                </div>
            </div>
            
            <div class="item-actions">
                <button class="btn btn-outline btn-sm" onclick="openCocktailDetail(<?php echo $item['id']; ?>)">Chi tiết</button>
                <?php if (isLoggedIn()): ?>
                <button class="btn btn-primary btn-sm" onclick="saveToMyDrinks(<?php echo $item['id']; ?>)">Lưu</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination-container">
        <div class="pagination-info">
            Hiển thị <?php echo $offset + 1; ?>-<?php echo min($offset + $items_per_page, $total_items); ?> trong tổng số <?php echo $total_items; ?> cocktail
        </div>
        <div class="pagination-controls">
            <?php if ($current_page > 1): ?>
                <button class="pagination-btn prev-btn" onclick="changePage(<?php echo $current_page - 1; ?>)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    Trước
                </button>
            <?php endif; ?>
            
            <div class="pagination-numbers">
                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1) {
                    echo '<button class="pagination-btn" onclick="changePage(1)">1</button>';
                    if ($start_page > 2) {
                        echo '<span class="pagination-ellipsis">...</span>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active_class = $i == $current_page ? 'active' : '';
                    echo '<button class="pagination-btn ' . $active_class . '" onclick="changePage(' . $i . ')">' . $i . '</button>';
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span class="pagination-ellipsis">...</span>';
                    }
                    echo '<button class="pagination-btn" onclick="changePage(' . $total_pages . ')">' . $total_pages . '</button>';
                }
                ?>
            </div>
            
            <?php if ($current_page < $total_pages): ?>
                <button class="pagination-btn next-btn" onclick="changePage(<?php echo $current_page + 1; ?>)">
                    Sau
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Results Summary -->
    <div class="results-summary">
        <span id="resultsCount"><?php echo $offset + 1; ?>-<?php echo min($offset + $items_per_page, $total_items); ?> trong tổng số <?php echo $total_items; ?> cocktail</span>
        <button id="clearFilters" class="clear-filters-btn">Xóa bộ lọc</button>
    </div>
    
    <?php
    exit; // Stop here for AJAX requests
}
?>

<!-- Hero Section -->
<section class="hero cocktail-hero">
    <div class="hero-content">
        <h1 class="hero-title">Cocktail Bar 419</h1>
        <p class="hero-subtitle">Nghệ thuật pha chế cocktail độc đáo với hương vị Việt Nam</p>
        <div class="hero-buttons">
            <a href="#menu" class="btn btn-primary">Khám Phá Menu</a>
            <?php if (isLoggedIn()): ?>
            <a href="account.php#my-drinks" class="btn btn-secondary">My Drinks</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- About Bar Section -->
<section class="section bar-intro">
    <div class="container">
        <div class="grid grid-2">
            <div class="bar-image">
                <img src="assets/images/cocktail-bar.jpg" alt="Cocktail Bar 419" class="lazy" data-src="assets/images/cocktail-bar.jpg">
            </div>
            <div class="bar-content">
                <h2>Quầy Bar 419 Saigon</h2>
                <p>Quầy bar của chúng tôi là nơi hội tụ của những nghệ nhân pha chế cocktail tài ba, những nguyên liệu cao cấp và những công thức độc quyền được tạo ra từ sự sáng tạo không giới hạn.</p>
                <p>Từ những cocktail cổ điển được pha chế hoàn hảo đến những tác phẩm nghệ thuật độc đáo, mỗi ly cocktail tại 419 Saigon đều mang trong mình một câu chuyện và hương vị riêng biệt.</p>
                <div class="bar-features">
                    <div class="feature-item">
                        <span class="feature-icon"></span>
                        <span>Rượu cao cấp nhập khẩu</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon"></span>
                        <span>Nguyên liệu tươi ngon</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon"></span>
                        <span>Bartender chuyên nghiệp</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Signature Cocktails Section -->
<?php if (!empty($signature_cocktails)): ?>
<section class="section signature-section">
    <div class="container">
        <h2 class="section-title">Cocktail Signature</h2>
        <div class="signature-cocktails-list">
            <?php 
            $signature_count = count($signature_cocktails);
            $show_all = false;
            $display_count = $show_all ? $signature_count : min(3, $signature_count);
            $signature_cocktails_display = array_slice($signature_cocktails, 0, $display_count);
            
            foreach ($signature_cocktails_display as $cocktail): ?>
            <div class="signature-cocktail-item" data-id="<?php echo $cocktail['id']; ?>">
                <div class="cocktail-main-info">
                    <div class="cocktail-image">
                        <?php 
                        $images = [];
                        if ($cocktail['images']) {
                            $decoded_images = json_decode($cocktail['images'], true);
                            if (is_array($decoded_images) && !empty($decoded_images)) {
                                $images = $decoded_images;
                            }
                        }
                        if (empty($images)) {
                            $images = ['assets/images/cocktail-default.jpg'];
                        }
                        $main_image = $images[0];
                        ?>
                        <img src="<?php echo htmlspecialchars($main_image); ?>" alt="<?php echo htmlspecialchars($cocktail['name']); ?>" class="lazy">
                        <div class="signature-badge">Signature</div>
                        <?php if (count($images) > 1): ?>
                        <div class="image-count">+<?php echo count($images) - 1; ?> ảnh</div>
                        <?php endif; ?>
                    </div>
                    <div class="cocktail-content">
                        <h3><?php echo htmlspecialchars($cocktail['name']); ?></h3>
                        <p class="cocktail-description"><?php echo htmlspecialchars($cocktail['description']); ?></p>
                        <div class="cocktail-ingredients">
                            <?php
                            if ($cocktail['ingredients']) {
                                $ingredients = explode(',', $cocktail['ingredients']);
                                foreach (array_slice($ingredients, 0, 3) as $ingredient) {
                                    echo '<span class="ingredient">' . htmlspecialchars(trim($ingredient)) . '</span>';
                                }
                                if (count($ingredients) > 3) {
                                    echo '<span class="ingredient">+' . (count($ingredients) - 3) . ' more</span>';
                                }
                            }
                            ?>
                        </div>
                        <div class="cocktail-rating">
                            <?php
                            $rating = round($cocktail['avg_rating'], 1);
                            for ($i = 1; $i <= 5; $i++) {
                                $starClass = $i <= $rating ? 'star' : 'star empty';
                                echo '<span class="' . $starClass . '">★</span>';
                            }
                            ?>
                            <span class="rating-text"><?php echo $rating; ?></span>
                            <span class="review-count">(<?php echo $cocktail['review_count']; ?> đánh giá)</span>
                        </div>
                        <div class="cocktail-price"><?php echo number_format($cocktail['price']); ?> VNĐ</div>
                    </div>
                </div>
                <div class="cocktail-actions">
                    <button class="btn btn-outline btn-sm" onclick="openCocktailDetail(<?php echo $cocktail['id']; ?>)">Chi tiết</button>
                    <?php if (isLoggedIn()): ?>
                    <button class="btn btn-primary btn-sm" onclick="saveToMyDrinks(<?php echo $cocktail['id']; ?>)">Lưu</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Hidden signature cocktails (for show more functionality) -->
        <?php if ($signature_count > 3): ?>
        <div class="signature-cocktails-list hidden" id="hiddenSignatureCocktails" style="display: none;">
            <?php 
            $hidden_cocktails = array_slice($signature_cocktails, 3);
            foreach ($hidden_cocktails as $cocktail): ?>
            <div class="signature-cocktail-item" data-id="<?php echo $cocktail['id']; ?>">
                <div class="cocktail-main-info">
                    <div class="cocktail-image">
                        <?php 
                        $images = [];
                        if ($cocktail['images']) {
                            $decoded_images = json_decode($cocktail['images'], true);
                            if (is_array($decoded_images) && !empty($decoded_images)) {
                                $images = $decoded_images;
                            }
                        }
                        if (empty($images)) {
                            $images = ['assets/images/cocktail-default.jpg'];
                        }
                        $main_image = $images[0];
                        ?>
                        <img src="<?php echo htmlspecialchars($main_image); ?>" alt="<?php echo htmlspecialchars($cocktail['name']); ?>" class="lazy">
                        <div class="signature-badge">Signature</div>
                        <?php if (count($images) > 1): ?>
                        <div class="image-count">+<?php echo count($images) - 1; ?> ảnh</div>
                        <?php endif; ?>
                    </div>
                    <div class="cocktail-content">
                        <h3><?php echo htmlspecialchars($cocktail['name']); ?></h3>
                        <p class="cocktail-description"><?php echo htmlspecialchars($cocktail['description']); ?></p>
                        <div class="cocktail-ingredients">
                            <?php
                            if ($cocktail['ingredients']) {
                                $ingredients = explode(',', $cocktail['ingredients']);
                                foreach (array_slice($ingredients, 0, 3) as $ingredient) {
                                    echo '<span class="ingredient">' . htmlspecialchars(trim($ingredient)) . '</span>';
                                }
                                if (count($ingredients) > 3) {
                                    echo '<span class="ingredient">+' . (count($ingredients) - 3) . ' more</span>';
                                }
                            }
                            ?>
                        </div>
                        <div class="cocktail-rating">
                            <?php
                            $rating = round($cocktail['avg_rating'], 1);
                            for ($i = 1; $i <= 5; $i++) {
                                $starClass = $i <= $rating ? 'star' : 'star empty';
                                echo '<span class="' . $starClass . '">★</span>';
                            }
                            ?>
                            <span class="rating-text"><?php echo $rating; ?></span>
                            <span class="review-count">(<?php echo $cocktail['review_count']; ?> đánh giá)</span>
                        </div>
                        <div class="cocktail-price"><?php echo number_format($cocktail['price']); ?> VNĐ</div>
                    </div>
                </div>
                <div class="cocktail-actions">
                    <button class="btn btn-outline btn-sm" onclick="openCocktailDetail(<?php echo $cocktail['id']; ?>)">Chi tiết</button>
                    <?php if (isLoggedIn()): ?>
                    <button class="btn btn-primary btn-sm" onclick="saveToMyDrinks(<?php echo $cocktail['id']; ?>)">Lưu</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Show More/Less Button -->
        <div class="signature-toggle-container">
            <button class="btn btn-outline" id="toggleSignatureCocktails" onclick="toggleSignatureCocktails()">
                <span id="toggleSignatureText">Xem thêm (<?php echo $signature_count - 3; ?> cocktail)</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="toggleSignatureIcon">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </button>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Hot Deals Section -->
<section class="section deals-section">
    <div class="container">
        <h2 class="section-title">Ưu Đãi Đặc Biệt</h2>
        <div class="deals-grid">
            <div class="deal-card">
                <div class="deal-badge">Happy Hour</div>
                <h3>Happy Hour 18:00 - 20:00</h3>
                <p>Giảm 30% tất cả cocktail từ 18:00 - 20:00 hàng ngày</p>
                <div class="deal-price">
                    <span class="original-price">200,000 VNĐ</span>
                    <span class="discount-price">140,000 VNĐ</span>
                </div>
            </div>
            <div class="deal-card">
                <div class="deal-badge">Weekend Special</div>
                <h3>Weekend Special</h3>
                <p>Mua 2 cocktail bất kỳ, tặng 1 cocktail cùng loại vào cuối tuần</p>
                <div class="deal-price">
                    <span class="original-price">400,000 VNĐ</span>
                    <span class="discount-price">200,000 VNĐ</span>
                </div>
            </div>
            <div class="deal-card">
                <div class="deal-badge">Student Night</div>
                <h3>Student Night - Thứ 4</h3>
                <p>Giảm 25% cho sinh viên vào thứ 4 hàng tuần (có thẻ sinh viên)</p>
                <div class="deal-price">
                    <span class="discount-price">Giảm 25%</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Menu Section -->
<section id="menu" class="section menu-section">
    <div class="container">
        <h2 class="section-title">Menu Cocktail</h2>
        
        <!-- Advanced Filter Section -->
        <div class="advanced-filters">
            <div class="filter-row">
                <!-- Category Filter -->
                <div class="filter-group">
                    <label>Danh mục:</label>
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="category" data-value="all">Tất cả</button>
                        <?php foreach (array_keys($cocktails_by_category) as $category): ?>
                        <button class="filter-btn" data-filter="category" data-value="<?php echo $category; ?>"><?php echo ucfirst($category); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Strength Filter -->
                <div class="filter-group">
                    <label>Mức độ mạnh:</label>
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="strength" data-value="all">Tất cả</button>
                        <button class="filter-btn" data-filter="strength" data-value="light">Light</button>
                        <button class="filter-btn" data-filter="strength" data-value="medium">Medium</button>
                        <button class="filter-btn" data-filter="strength" data-value="strong">Strong</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Results Summary -->
        <div class="results-summary">
            <span id="resultsCount"><?php echo count($cocktail_items); ?> cocktail</span>
            <button id="clearFilters" class="clear-filters-btn">Xóa bộ lọc</button>
        </div>
        
        <!-- Menu Items Grid -->
        <div class="menu-items-grid">
            <?php foreach ($cocktail_items_paginated as $item): ?>
            <div class="menu-item-card" 
                 data-category="<?php echo strtolower($item['category_name']); ?>" 
                 data-id="<?php echo $item['id']; ?>"
                 data-price="<?php echo $item['price']; ?>"
                 data-rating="<?php echo $item['avg_rating']; ?>"
                 data-strength="<?php echo getStrengthLevelValue($item['name']); ?>"
                 data-name="<?php echo strtolower($item['name']); ?>">
                
                <div class="item-image">
                    <?php 
                    $images = [];
                    if ($item['images']) {
                        $decoded_images = json_decode($item['images'], true);
                        if (is_array($decoded_images) && !empty($decoded_images)) {
                            $images = $decoded_images;
                        }
                    }
                    if (empty($images)) {
                        $images = ['assets/images/cocktail-default.jpg'];
                    }
                    $main_image = $images[0];
                    ?>
                    <img src="<?php echo htmlspecialchars($main_image); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="lazy">
                    <?php if ($item['is_featured']): ?>
                    <div class="featured-badge">Featured</div>
                    <?php endif; ?>
                </div>
                
                <div class="item-content">
                    <div class="item-header">
                        <h4 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                        <div class="item-rating">
                            <?php
                            $rating = round($item['avg_rating'], 1);
                            for ($i = 1; $i <= 5; $i++) {
                                $starClass = $i <= $rating ? 'star' : 'star empty';
                                echo '<span class="' . $starClass . '">★</span>';
                            }
                            ?>
                            <span class="rating-text"><?php echo $rating; ?></span>
                        </div>
                    </div>
                    
                    <p class="item-description"><?php echo htmlspecialchars(substr($item['description'], 0, 80)) . (strlen($item['description']) > 80 ? '...' : ''); ?></p>
                    
                    <div class="item-ingredients">
                        <?php
                        if ($item['ingredients']) {
                            $ingredients = explode(',', $item['ingredients']);
                            foreach (array_slice($ingredients, 0, 2) as $ingredient) {
                                echo '<span class="ingredient-tag">' . htmlspecialchars(trim($ingredient)) . '</span>';
                            }
                            if (count($ingredients) > 2) {
                                echo '<span class="ingredient-more">+' . (count($ingredients) - 2) . '</span>';
                            }
                        }
                        ?>
                    </div>
                    
                    <div class="item-meta">
                        <span class="strength-level"><?php echo getStrengthLevel($item['name']); ?></span>
                        <span class="price"><?php echo number_format($item['price']); ?> VNĐ</span>
                    </div>
                </div>
                
                <div class="item-actions">
                    <button class="btn btn-outline btn-sm" onclick="openCocktailDetail(<?php echo $item['id']; ?>)">Chi tiết</button>
                    <?php if (isLoggedIn()): ?>
                    <button class="btn btn-primary btn-sm" onclick="saveToMyDrinks(<?php echo $item['id']; ?>)">Lưu</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- No Results Message -->
        <div id="noResults" class="no-results" style="display: none;">
        <div class="no-results-icon"></div>
            <h3>Không tìm thấy cocktail nào</h3>
            <p>Hãy thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
        </div>
        
        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                Hiển thị <?php echo $offset + 1; ?>-<?php echo min($offset + $items_per_page, $total_items); ?> trong tổng số <?php echo $total_items; ?> cocktail
            </div>
            <div class="pagination-controls">
                <?php if ($current_page > 1): ?>
                    <button class="pagination-btn prev-btn" onclick="changePage(<?php echo $current_page - 1; ?>)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                        Trước
                    </button>
                <?php endif; ?>
                
                <div class="pagination-numbers">
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1) {
                        echo '<button class="pagination-btn" onclick="changePage(1)">1</button>';
                        if ($start_page > 2) {
                            echo '<span class="pagination-ellipsis">...</span>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $active_class = $i == $current_page ? 'active' : '';
                        echo '<button class="pagination-btn ' . $active_class . '" onclick="changePage(' . $i . ')">' . $i . '</button>';
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<span class="pagination-ellipsis">...</span>';
                        }
                        echo '<button class="pagination-btn" onclick="changePage(' . $total_pages . ')">' . $total_pages . '</button>';
                    }
                    ?>
                </div>
                
                <?php if ($current_page < $total_pages): ?>
                    <button class="pagination-btn next-btn" onclick="changePage(<?php echo $current_page + 1; ?>)">
                        Sau
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>



<!-- Cocktail Detail Modal -->
<div id="cocktailDetailModal" class="modal">
    <div class="modal-content cocktail-detail-content">
        <span class="close" onclick="closeCocktailDetail()">&times;</span>
        
        <div class="cocktail-detail-container">
            <!-- Main Image Section -->
            <div class="cocktail-detail-images">
                <div class="main-image-container">
                    <button class="image-nav-btn prev-btn" onclick="switchCocktailImage(currentImageIndex - 1)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </button>
                    <img id="detailMainImage" src="" alt="" class="main-image">
                    <button class="image-nav-btn next-btn" onclick="switchCocktailImage(currentImageIndex + 1)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </button>
                    <div class="image-overlay">
                        <div class="image-counter">
                            <span id="currentImageIndex">1</span> / <span id="totalImages">1</span>
                        </div>
                    </div>
                </div>
                
                <!-- Thumbnail Images -->
                <div class="thumbnail-images" id="thumbnailImages">
                    <!-- Thumbnails will be populated by JavaScript -->
                </div>
            </div>
            
            <!-- Cocktail Information Section -->
            <div class="cocktail-detail-info">
                <div class="cocktail-header">
                    <h2 id="detailCocktailName"></h2>
                    <div class="cocktail-price" id="detailCocktailPrice"></div>
                </div>
                
                <div class="cocktail-description" id="detailCocktailDescription"></div>
                
                <!-- Rating Section -->
                <div class="cocktail-rating">
                    <div class="rating-stars" id="detailRatingStars">
                        <!-- Stars will be populated by JavaScript -->
                    </div>
                    <div class="rating-text" id="detailRatingText"></div>
                </div>
                
                <!-- Ingredients Section -->
                <div class="cocktail-ingredients-detail" id="detailCocktailIngredients">
                    <!-- Ingredients will be populated by JavaScript -->
                </div>
                
                <!-- Strength Level -->
                <div class="cocktail-strength" id="detailCocktailStrength">
                    <!-- Strength will be populated by JavaScript -->
                </div>
                
                <!-- Reviews Section -->
                <div class="cocktail-reviews">
                    <h4>Đánh giá từ khách hàng</h4>
                    <div class="reviews-list" id="detailReviewsList">
                        <!-- Reviews will be populated by JavaScript -->
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="cocktail-detail-actions">
                    <?php if (isLoggedIn()): ?>
                    <button class="btn btn-primary" onclick="saveCurrentCocktail()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        Lưu vào My Drinks
                    </button>
                    <?php endif; ?>
                    <button class="btn btn-outline" onclick="closeCocktailDetail()">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bespoke Cocktail Section -->
<?php if (isLoggedIn()): ?>
<section class="section bespoke-section">
    <div class="container">
        <h2 class="section-title">Bespoke Cocktail</h2>
        <p class="section-subtitle">Tạo cocktail độc đáo theo ý thích của bạn</p>
        
        <div class="bespoke-container">
            <div class="bespoke-content">
                <div class="bespoke-info">
                    <h3>Tùy chỉnh theo ý thích</h3>
                    <p>Tại 419 Saigon, chúng tôi tin rằng mỗi người đều có khẩu vị riêng. Hãy cho chúng tôi biết bạn muốn gì và chúng tôi sẽ tạo ra một cocktail hoàn hảo dành riêng cho bạn.</p>
                    
                    <div class="bespoke-features">
                        <div class="feature-item">
                            <span class="feature-icon"></span>
                            <span>Nguyên liệu tươi ngon</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon"></span>
                            <span>Bartender chuyên nghiệp</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon"></span>
                            <span>Hương vị độc đáo</span>
                        </div>
                    </div>
                </div>
                
                <div class="bespoke-form-container">
                    <form class="bespoke-form" id="bespokeForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="bespoke_name">Tên cocktail</label>
                                <input type="text" id="bespoke_name" name="bespoke_name" placeholder="Nhập tên cocktail" required>
                            </div>
                            <div class="form-group">
                                <label for="bespoke_base">Rượu nền chính</label>
                                <select id="bespoke_base" name="bespoke_base" required>
                                    <option value="">Chọn rượu nền</option>
                                    <option value="gin">Gin</option>
                                    <option value="vodka">Vodka</option>
                                    <option value="whisky">Whisky</option>
                                    <option value="rum">Rum</option>
                                    <option value="tequila">Tequila</option>
                                    <option value="none">Không có rượu (Mocktail)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="bespoke_ingredients">Nguyên liệu và công thức</label>
                            <textarea id="bespoke_ingredients" name="bespoke_ingredients" placeholder="Mô tả chi tiết nguyên liệu, tỷ lệ và cách pha chế..." rows="4" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="bespoke_flavor">Hương vị mong muốn</label>
                            <textarea id="bespoke_flavor" name="bespoke_flavor" placeholder="Ngọt, chua, đắng, cay, mát lạnh... Mô tả hương vị bạn muốn" rows="2"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="bespoke_notes">Ghi chú đặc biệt</label>
                            <textarea id="bespoke_notes" name="bespoke_notes" placeholder="Yêu cầu đặc biệt, ghi chú về hương vị, dị ứng..." rows="2"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                                Lưu Bespoke Cocktail
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- QR Code Modal for Bartender -->
<div id="qrCodeModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeQRModal()">&times;</span>
        <div class="qr-content">
            <h3>Show to Bartender</h3>
            <div class="qr-code" id="qrCodeDisplay">
                <!-- QR code will be generated here -->
            </div>
            <p>Quét mã QR này để bartender có thể xem cocktail yêu thích của bạn</p>
            <div class="qr-actions">
                <button class="btn btn-primary" onclick="downloadQRCode()">Tải xuống</button>
                <button class="btn btn-outline" onclick="closeQRModal()">Đóng</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Cocktail specific styles */
.cocktail-hero {
    background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/images/cocktail-hero.jpg');
    background-size: cover;
    background-position: center;
    height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.bar-intro {
    background: var(--color-black);
}

.bar-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 8px;
}

.bar-content {
    padding: var(--spacing-xl);
}

.bar-features {
    margin-top: var(--spacing-lg);
}

.feature-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
}

.feature-item .feature-icon {
    font-size: 1.5rem;
}

.hero-buttons {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
    justify-content: center;
}

.signature-section {
    background: var(--color-dark-gray);
}

.signature-card {
    position: relative;
    border: 2px solid var(--color-gold);
}

.signature-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--color-gold);
    color: var(--color-black);
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
}

.signature-cocktails-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.signature-cocktail-item {
    background: var(--color-black);
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid rgba(212, 175, 55, 0.2);
    transition: transform var(--transition-normal);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
}

.signature-cocktail-item:hover {
    transform: translateY(-2px);
    border-color: var(--color-gold);
}

.cocktail-main-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    flex: 1;
}

.cocktail-image {
    height: 120px;
    width: 120px;
    position: relative;
    flex-shrink: 0;
}

.cocktail-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.signature-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: var(--color-gold);
    color: var(--color-black);
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
}

.image-count {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.8);
    color: var(--color-cream);
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 0.7rem;
}

.cocktail-content {
    flex: 1;
}

.cocktail-content h3 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-sm);
    font-size: 1.25rem;
}

.cocktail-description {
    margin-bottom: var(--spacing-md);
    color: var(--color-light-gray);
    line-height: 1.5;
}

.cocktail-ingredients {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-xs);
    margin-bottom: var(--spacing-md);
}

.ingredient {
    background: var(--color-dark-gray);
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    color: var(--color-gold);
}

.cocktail-rating {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
}

.cocktail-rating .star {
    color: var(--color-gold);
    font-size: 1rem;
}

.cocktail-rating .star.empty {
    color: var(--color-gray);
}

.rating-text {
    color: var(--color-gold);
    font-weight: 600;
    margin-left: var(--spacing-xs);
}

.review-count {
    color: var(--color-gray);
    font-size: 0.8rem;
}

.cocktail-price {
    color: var(--color-gold);
    font-size: 1.25rem;
    font-weight: 600;
}

.cocktail-actions {
    display: flex;
    gap: var(--spacing-sm);
    flex-shrink: 0;
}

/* Signature Toggle Button */
.signature-toggle-container {
    text-align: center;
    margin-top: var(--spacing-xl);
    padding-top: var(--spacing-lg);
    border-top: 1px solid rgba(212, 175, 55, 0.2);
}

#toggleSignatureCocktails {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-md) var(--spacing-xl);
    background: transparent;
    color: var(--color-gold);
    border: 2px solid var(--color-gold);
    border-radius: 25px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-normal);
    min-width: 200px;
    justify-content: center;
}

#toggleSignatureCocktails:hover {
    background: var(--color-gold);
    color: var(--color-black);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
}

#toggleSignatureIcon {
    transition: transform var(--transition-normal);
}

#toggleSignatureCocktails.expanded #toggleSignatureIcon {
    transform: rotate(180deg);
}

.cocktail-card {
    background: var(--color-black);
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid rgba(212, 175, 55, 0.2);
    transition: transform var(--transition-normal);
}

.cocktail-card:hover {
    transform: translateY(-5px);
}

.cocktail-image {
    height: 200px;
    position: relative;
}

.cocktail-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cocktail-content {
    padding: var(--spacing-lg);
}

.cocktail-content h3 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-sm);
}

.cocktail-description {
    margin-bottom: var(--spacing-md);
    color: var(--color-light-gray);
    line-height: 1.5;
}

.cocktail-ingredients {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-xs);
    margin-bottom: var(--spacing-md);
}

.ingredient {
    background: var(--color-dark-gray);
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    color: var(--color-gold);
}

.cocktail-price {
    color: var(--color-gold);
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: var(--spacing-md);
}

.cocktail-actions {
    display: flex;
    gap: var(--spacing-sm);
}

.deals-section {
    background: var(--color-black);
}

.deals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-lg);
}

.deal-card {
    background: var(--color-dark-gray);
    padding: var(--spacing-xl);
    border-radius: 8px;
    border: 1px solid rgba(212, 175, 55, 0.2);
    position: relative;
    text-align: center;
    transition: transform var(--transition-normal);
}

.deal-card:hover {
    transform: translateY(-3px);
    border-color: var(--color-gold);
}

.deal-badge {
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--color-gold);
    color: var(--color-black);
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.deal-card h3 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-sm);
}

.deal-price {
    margin-top: var(--spacing-md);
}

.original-price {
    text-decoration: line-through;
    color: var(--color-light-gray);
    margin-right: var(--spacing-sm);
}

.discount-price {
    color: var(--color-gold);
    font-size: 1.25rem;
    font-weight: 600;
}

.menu-section {
    background: var(--color-dark-gray);
}

/* Advanced Filters */
.advanced-filters {
    background: var(--color-black);
    padding: var(--spacing-xl);
    border-radius: 12px;
    margin-bottom: var(--spacing-xl);
    border: 1px solid rgba(212, 175, 55, 0.2);
}

.filter-row {
    display: flex;
    gap: var(--spacing-xl);
    margin-bottom: var(--spacing-lg);
    align-items: flex-start;
    justify-content: center;
}

.filter-row:last-child {
    margin-bottom: 0;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    color: var(--color-gold);
    font-weight: 600;
    margin-bottom: var(--spacing-sm);
    font-size: 0.9rem;
}

.filter-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-xs);
}

.filter-btn {
    background: var(--color-dark-gray);
    color: var(--color-cream);
    border: 1px solid var(--color-gray);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: 20px;
    cursor: pointer;
    transition: all var(--transition-normal);
    font-size: 0.8rem;
    white-space: nowrap;
}

.filter-btn:hover {
    border-color: var(--color-gold);
    transform: translateY(-1px);
}

.filter-btn.active {
    background: var(--color-gold);
    color: var(--color-black);
    border-color: var(--color-gold);
}

.search-sort-container {
    display: flex;
    gap: var(--spacing-lg);
    align-items: center;
    width: 100%;
}

.search-box {
    display: flex;
    flex: 1;
    max-width: 400px;
}

.search-input {
    flex: 1;
    padding: var(--spacing-sm) var(--spacing-md);
    background: var(--color-dark-gray);
    border: 1px solid var(--color-gray);
    border-radius: 8px 0 0 8px;
    color: var(--color-cream);
    font-size: 0.9rem;
}

.search-input:focus {
    outline: none;
    border-color: var(--color-gold);
}

.search-btn {
    padding: var(--spacing-sm) var(--spacing-md);
    background: var(--color-gold);
    color: var(--color-black);
    border: none;
    border-radius: 0 8px 8px 0;
    cursor: pointer;
    font-size: 1rem;
}

.sort-options {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.sort-options label {
    color: var(--color-gold);
    font-weight: 600;
    font-size: 0.9rem;
    white-space: nowrap;
}

.sort-select {
    padding: var(--spacing-sm) var(--spacing-md);
    background: var(--color-dark-gray);
    border: 1px solid var(--color-gray);
    border-radius: 8px;
    color: var(--color-cream);
    font-size: 0.9rem;
    min-width: 150px;
}

.sort-select:focus {
    outline: none;
    border-color: var(--color-gold);
}

/* Results Summary */
.results-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-md) 0;
    border-bottom: 1px solid rgba(212, 175, 55, 0.2);
}

.results-summary span {
    color: var(--color-cream);
    font-weight: 600;
}

.clear-filters-btn {
    background: var(--color-gray);
    color: var(--color-cream);
    border: none;
    padding: var(--spacing-xs) var(--spacing-md);
    border-radius: 20px;
    cursor: pointer;
    font-size: 0.8rem;
    transition: all var(--transition-normal);
}

.clear-filters-btn:hover {
    background: var(--color-light-gray);
}

/* Menu Items Grid */
.menu-items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: var(--spacing-lg);
}

.menu-item-card {
    background: var(--color-black);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(212, 175, 55, 0.2);
    transition: all var(--transition-normal);
    display: flex;
    flex-direction: column;
}

.menu-item-card:hover {
    transform: translateY(-5px);
    border-color: var(--color-gold);
    box-shadow: 0 10px 25px rgba(212, 175, 55, 0.2);
}

.item-image {
    height: 200px;
    position: relative;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-normal);
}

.menu-item-card:hover .item-image img {
    transform: scale(1.05);
}

.featured-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: var(--color-gold);
    color: var(--color-black);
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
}

.item-content {
    padding: var(--spacing-lg);
    flex: 1;
    display: flex;
    flex-direction: column;
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-sm);
}

.item-name {
    color: var(--color-gold);
    margin: 0;
    font-size: 1.1rem;
    line-height: 1.3;
}

.item-rating {
    display: flex;
    align-items: center;
    gap: 2px;
}

.item-rating .star {
    color: var(--color-gold);
    font-size: 0.8rem;
}

.item-rating .star.empty {
    color: var(--color-gray);
}

.rating-text {
    color: var(--color-gold);
    font-weight: 600;
    font-size: 0.8rem;
    margin-left: var(--spacing-xs);
}

.item-description {
    color: var(--color-light-gray);
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: var(--spacing-md);
    flex: 1;
}

.item-ingredients {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-xs);
    margin-bottom: var(--spacing-md);
}

.ingredient-tag {
    background: var(--color-dark-gray);
    color: var(--color-gold);
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.7rem;
    border: 1px solid rgba(212, 175, 55, 0.3);
}

.ingredient-more {
    background: var(--color-gray);
    color: var(--color-cream);
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.7rem;
    font-style: italic;
}

.item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-md);
}

.strength-level {
    font-size: 0.8rem;
}

.strength.light {
    color: #51cf66;
    background: rgba(81, 207, 102, 0.1);
    padding: 2px 8px;
    border-radius: 12px;
}

.strength.medium {
    color: #ffd43b;
    background: rgba(255, 212, 59, 0.1);
    padding: 2px 8px;
    border-radius: 12px;
}

.strength.strong {
    color: #ff6b6b;
    background: rgba(255, 107, 107, 0.1);
    padding: 2px 8px;
    border-radius: 12px;
}

.price {
    color: var(--color-gold);
    font-weight: 600;
    font-size: 1.1rem;
}

.item-actions {
    padding: 0 var(--spacing-lg) var(--spacing-lg);
    display: flex;
    gap: var(--spacing-sm);
}

/* No Results */
.no-results {
    text-align: center;
    padding: var(--spacing-xl);
    background: var(--color-black);
    border-radius: 12px;
    border: 1px solid rgba(212, 175, 55, 0.2);
}

.no-results-icon {
    font-size: 3rem;
    margin-bottom: var(--spacing-md);
}

.no-results h3 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-sm);
}

.no-results p {
    color: var(--color-gray);
}

/* Pagination Styles */
.pagination-container {
    margin-top: var(--spacing-xl);
    padding: var(--spacing-lg);
    background: var(--color-black);
    border-radius: 12px;
    border: 1px solid rgba(212, 175, 55, 0.2);
}

.pagination-info {
    text-align: center;
    color: var(--color-gray);
    margin-bottom: var(--spacing-md);
    font-size: 0.9rem;
}

.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
}

.pagination-numbers {
    display: flex;
    gap: var(--spacing-xs);
    align-items: center;
}

.pagination-btn {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-xs);
    padding: var(--spacing-sm) var(--spacing-md);
    background: var(--color-dark-gray);
    color: var(--color-cream);
    border: 1px solid var(--color-gray);
    border-radius: 8px;
    text-decoration: none;
    transition: all var(--transition-normal);
    font-size: 0.9rem;
    min-width: 40px;
    justify-content: center;
}

.pagination-btn:hover {
    border-color: var(--color-gold);
    background: rgba(212, 175, 55, 0.1);
    transform: translateY(-1px);
}

.pagination-btn.active {
    background: var(--color-gold);
    color: var(--color-black);
    border-color: var(--color-gold);
    font-weight: 600;
}

.pagination-btn.prev-btn,
.pagination-btn.next-btn {
    min-width: 80px;
}

.pagination-ellipsis {
    color: var(--color-gray);
    padding: 0 var(--spacing-xs);
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .pagination-controls {
        flex-direction: column;
        gap: var(--spacing-md);
    }
    
    .pagination-numbers {
        order: 2;
    }
    
    .pagination-btn.prev-btn,
    .pagination-btn.next-btn {
        order: 1;
        min-width: 120px;
    }
}

.menu-item {
    background: var(--color-black);
    padding: var(--spacing-lg);
    border-radius: 8px;
    border: 1px solid rgba(212, 175, 55, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all var(--transition-normal);
}

.menu-item:hover {
    border-color: var(--color-gold);
    transform: translateY(-2px);
}

.item-info {
    flex: 1;
}

.item-info h4 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-xs);
}

.item-info p {
    color: var(--color-light-gray);
    font-size: 0.9rem;
    margin-bottom: var(--spacing-sm);
    line-height: 1.4;
}

.item-meta {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.strength-level {
    font-size: 0.8rem;
}

.strength.light {
    color: #51cf66;
    background: rgba(81, 207, 102, 0.1);
    padding: 2px 8px;
    border-radius: 12px;
}

.strength.medium {
    color: #ffd43b;
    background: rgba(255, 212, 59, 0.1);
    padding: 2px 8px;
    border-radius: 12px;
}

.strength.strong {
    color: #ff6b6b;
    background: rgba(255, 107, 107, 0.1);
    padding: 2px 8px;
    border-radius: 12px;
}

.price {
    color: var(--color-gold);
    font-weight: 600;
    white-space: nowrap;
}

.item-actions {
    display: flex;
    gap: var(--spacing-sm);
}





/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
}

.modal-content {
    background: var(--color-black);
    margin: 2% auto;
    padding: var(--spacing-xl);
    border: 2px solid var(--color-gold);
    border-radius: 12px;
    width: 90%;
    max-width: 900px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
}

/* Cocktail Detail Modal Styles */
.cocktail-detail-content {
    max-width: 100%;
}

.cocktail-detail-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xl);
}

.cocktail-detail-images {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.main-image-container {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
}

.main-image {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: 8px;
}

.image-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.7);
    color: var(--color-cream);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-normal);
}

.image-nav-btn:hover {
    background: var(--color-gold);
    color: var(--color-black);
}

.prev-btn {
    left: 10px;
}

.next-btn {
    right: 10px;
}

.image-overlay {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.8);
    color: var(--color-cream);
    padding: 4px 8px;
    border-radius: 8px;
    font-size: 0.8rem;
}

.thumbnail-images {
    display: flex;
    gap: var(--spacing-sm);
    justify-content: center;
}

.thumbnail-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all var(--transition-normal);
}

.thumbnail-image:hover,
.thumbnail-image.active {
    border-color: var(--color-gold);
    transform: scale(1.05);
}

.cocktail-detail-info {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.cocktail-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-lg);
    padding-bottom: var(--spacing-md);
    border-bottom: 1px solid rgba(212, 175, 55, 0.2);
}

.cocktail-header h2 {
    color: var(--color-gold);
    margin: 0;
    font-size: 1.8rem;
}

.cocktail-price {
    color: var(--color-gold);
    font-size: 1.5rem;
    font-weight: 600;
}

.cocktail-description {
    color: var(--color-cream);
    line-height: 1.6;
    margin-bottom: var(--spacing-md);
}

.cocktail-rating {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
}

.rating-stars .star {
    color: var(--color-gold);
    font-size: 1.2rem;
}

.rating-stars .star.empty {
    color: var(--color-gray);
}

.rating-text {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.rating-value {
    color: var(--color-gold);
    font-weight: 600;
    font-size: 1.1rem;
}

.review-count {
    color: var(--color-gray);
    font-size: 0.9rem;
}

.cocktail-ingredients-detail h4,
.cocktail-strength h4 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-sm);
}

.ingredients-list {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-xs);
    margin-bottom: var(--spacing-md);
}

.cocktail-reviews h4 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-md);
}

.reviews-list {
    max-height: 200px;
    overflow-y: auto;
}

.review-item {
    background: var(--color-dark-gray);
    padding: var(--spacing-md);
    border-radius: 8px;
    margin-bottom: var(--spacing-sm);
    border: 1px solid rgba(212, 175, 55, 0.1);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-sm);
    flex-wrap: wrap;
    gap: var(--spacing-xs);
}

.reviewer-name {
    color: var(--color-gold);
    font-weight: 600;
    font-size: 0.9rem;
}

.review-rating .star {
    color: var(--color-gold);
    font-size: 0.8rem;
}

.review-rating .star.empty {
    color: var(--color-gray);
}

.review-date {
    color: var(--color-gray);
    font-size: 0.8rem;
}

.review-comment {
    color: var(--color-cream);
    font-size: 0.9rem;
    line-height: 1.4;
}

.no-reviews {
    color: var(--color-gray);
    text-align: center;
    font-style: italic;
}

.cocktail-detail-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: flex-end;
    padding-top: var(--spacing-lg);
    border-top: 1px solid rgba(212, 175, 55, 0.2);
    margin-top: auto;
}

.close {
    color: var(--color-gold);
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    position: absolute;
    right: var(--spacing-lg);
    top: var(--spacing-md);
}

.close:hover {
    color: var(--color-cream);
}

.cocktail-detail-content {
    margin-top: var(--spacing-lg);
}

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-lg);
    padding-bottom: var(--spacing-md);
    border-bottom: 1px solid rgba(212, 175, 55, 0.2);
}

.detail-header h2 {
    color: var(--color-gold);
    margin: 0;
}

.detail-price {
    color: var(--color-gold);
    font-size: 1.5rem;
    font-weight: 600;
}

.detail-body {
    margin-bottom: var(--spacing-lg);
}

.detail-description {
    color: var(--color-cream);
    margin-bottom: var(--spacing-md);
    line-height: 1.6;
}

.detail-ingredients {
    margin-bottom: var(--spacing-md);
}

.detail-ingredients h4 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-sm);
}

.detail-strength h4 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-sm);
}

.detail-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: flex-end;
    padding-top: var(--spacing-lg);
    border-top: 1px solid rgba(212, 175, 55, 0.2);
}

/* QR Code Modal */
.qr-content {
    text-align: center;
}

.qr-content h3 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-lg);
}

.qr-code {
    background: white;
    padding: var(--spacing-lg);
    border-radius: 8px;
    margin: var(--spacing-lg) auto;
    width: 200px;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.qr-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
    margin-top: var(--spacing-lg);
}

@media (max-width: 768px) {
    .bar-content {
        padding: var(--spacing-md);
    }
    
    .category-tabs {
        flex-direction: column;
        align-items: center;
    }
    
    .menu-item {
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-sm);
    }
    
    .item-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .detail-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
    
    .detail-actions {
        flex-direction: column;
    }
    
    /* Mobile styles for signature cocktails */
    .signature-cocktail-item {
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-md);
    }
    
    .cocktail-main-info {
        flex-direction: column;
        text-align: center;
    }
    
    .cocktail-image {
        height: 150px;
        width: 150px;
    }
    
    .cocktail-actions {
        justify-content: center;
    }
    
    /* Mobile styles for cocktail detail modal */
    .cocktail-detail-container {
        grid-template-columns: 1fr;
        gap: var(--spacing-lg);
    }
    
    .main-image {
        height: 250px;
    }
    
    .cocktail-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
    
    .cocktail-detail-actions {
        flex-direction: column;
    }
    
    .thumbnail-images {
        justify-content: center;
    }
    
    /* Mobile styles for advanced filters */
    .filter-row {
        flex-direction: column;
        gap: var(--spacing-md);
    }
    
    .filter-group {
        min-width: auto;
    }
    
    .search-sort-container {
        flex-direction: column;
        align-items: stretch;
        gap: var(--spacing-md);
    }
    
    .search-box {
        max-width: none;
    }
    
    .sort-options {
        justify-content: center;
    }
    
    .menu-items-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
    }
    
    .results-summary {
        flex-direction: column;
        gap: var(--spacing-sm);
        align-items: center;
    }
}

/* Bespoke Section Styles */
.bespoke-section {
    background: var(--color-black);
    padding: var(--spacing-xl) 0;
}

.bespoke-container {
    max-width: 1200px;
    margin: 0 auto;
}

.bespoke-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xl);
    align-items: start;
}

.bespoke-info h3 {
    color: var(--color-gold);
    font-size: 1.5rem;
    margin-bottom: var(--spacing-md);
}

.bespoke-info p {
    color: var(--color-cream);
    line-height: 1.6;
    margin-bottom: var(--spacing-lg);
}

.bespoke-features {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.bespoke-features .feature-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    color: var(--color-light-gray);
}

.bespoke-features .feature-icon {
    font-size: 1.2rem;
}

.bespoke-form-container {
    background: var(--color-dark-gray);
    padding: var(--spacing-xl);
    border-radius: 12px;
    border: 1px solid rgba(212, 175, 55, 0.2);
}

.bespoke-form {
    display: grid;
    gap: var(--spacing-md);
}

.bespoke-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
}

.bespoke-form .form-group {
    display: flex;
    flex-direction: column;
}

.bespoke-form .form-group label {
    color: var(--color-cream);
    margin-bottom: var(--spacing-xs);
    font-weight: 500;
    font-size: 0.9rem;
}

.bespoke-form .form-group input,
.bespoke-form .form-group select,
.bespoke-form .form-group textarea {
    padding: var(--spacing-sm);
    background: var(--color-black);
    border: 1px solid var(--color-gray);
    border-radius: 8px;
    color: var(--color-cream);
    font-size: 0.9rem;
    transition: all var(--transition-normal);
}

.bespoke-form .form-group input:focus,
.bespoke-form .form-group select:focus,
.bespoke-form .form-group textarea:focus {
    outline: none;
    border-color: var(--color-gold);
    box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.3);
}

.bespoke-form .form-actions {
    margin-top: var(--spacing-md);
}

.bespoke-form .btn {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-xs);
    padding: var(--spacing-md) var(--spacing-lg);
    font-size: 1rem;
}

@media (max-width: 768px) {
    .bespoke-content {
        grid-template-columns: 1fr;
        gap: var(--spacing-lg);
    }
    
    .bespoke-form .form-row {
        grid-template-columns: 1fr;
    }
    
    .bespoke-form-container {
        padding: var(--spacing-lg);
    }
}
</style>

<script>
// Global variables
let currentCocktailId = null;
let currentCocktailData = null;
let currentImageIndex = 0;

// Make currentCocktailData globally accessible
window.currentCocktailData = null;

// Cocktail data from PHP with reviews
const cocktailData = <?php 
$cocktail_data_for_js = [];
foreach ($cocktail_items as $item) {
    $cocktail_data_for_js[$item['id']] = [
        'id' => $item['id'],
        'name' => $item['name'],
        'description' => $item['description'],
        'price' => (float)$item['price'],
        'ingredients' => $item['ingredients'],
        'category' => $item['category_name'],
        'avg_rating' => (float)$item['avg_rating'],
        'review_count' => (int)$item['review_count'],
        'images' => (function($images) {
            if ($images) {
                $decoded = json_decode($images, true);
                if (is_array($decoded) && !empty($decoded)) {
                    return $decoded;
                }
            }
            return ['assets/images/cocktail-default.jpg'];
        })($item['images']),
        'reviews' => isset($cocktail_reviews[$item['id']]) ? $cocktail_reviews[$item['id']] : []
    ];
}
echo json_encode($cocktail_data_for_js, JSON_UNESCAPED_UNICODE);
?>;



// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Advanced filtering system
    initializeAdvancedFilters();
    
    // Bespoke form submission
    const bespokeForm = document.getElementById('bespokeForm');
    if (bespokeForm) {
        bespokeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveBespokeCocktail();
        });
    }
});

// Toggle signature cocktails visibility
function toggleSignatureCocktails() {
    const hiddenCocktails = document.getElementById('hiddenSignatureCocktails');
    const toggleBtn = document.getElementById('toggleSignatureCocktails');
    const toggleText = document.getElementById('toggleSignatureText');
    const toggleIcon = document.getElementById('toggleSignatureIcon');
    
    if (!hiddenCocktails || !toggleBtn) return;
    
    const isHidden = hiddenCocktails.style.display === 'none';
    
    if (isHidden) {
        // Show hidden cocktails
        hiddenCocktails.style.display = 'flex';
        toggleText.textContent = 'Ẩn bớt';
        toggleBtn.classList.add('expanded');
        
        // Smooth scroll to the newly shown content
        setTimeout(() => {
            hiddenCocktails.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start',
                inline: 'nearest'
            });
        }, 100);
    } else {
        // Hide cocktails
        hiddenCocktails.style.display = 'none';
        toggleText.textContent = 'Xem thêm (<?php echo count($signature_cocktails) - 3; ?> cocktail)';
        toggleBtn.classList.remove('expanded');
        
        // Scroll back to the toggle button
        setTimeout(() => {
            toggleBtn.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center',
                inline: 'nearest'
            });
        }, 100);
    }
}

// Advanced filtering system
function initializeAdvancedFilters() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const menuItems = document.querySelectorAll('.menu-item-card');
    
    let activeFilters = {
        category: 'all',
        strength: 'all'
    };
    
    // Filter button clicks
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filterType = this.dataset.filter;
            const filterValue = this.dataset.value;
            
            // Update active state
            document.querySelectorAll(`[data-filter="${filterType}"]`).forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Update active filters
            activeFilters[filterType] = filterValue;
            
            // Apply filters
            applyFilters();
        });
    });
    
    
    // Clear filters
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            clearAllFilters();
        });
    }
    
    // Apply all active filters
    function applyFilters() {
        let visibleCount = 0;
        
        menuItems.forEach(item => {
            let shouldShow = true;
            
            // Category filter
            if (activeFilters.category !== 'all' && item.dataset.category !== activeFilters.category) {
                shouldShow = false;
            }
            
            // Strength filter
            if (activeFilters.strength !== 'all' && item.dataset.strength !== activeFilters.strength) {
                shouldShow = false;
            }
            
            // Show/hide item
            if (shouldShow) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Update results count
        updateResultsCount(visibleCount);
        
        // Show/hide no results message
        const noResults = document.getElementById('noResults');
        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }
        
        // Hide pagination when filtering (since we're showing all filtered results)
        const paginationContainer = document.querySelector('.pagination-container');
        if (paginationContainer) {
            const hasActiveFilters = activeFilters.category !== 'all' || 
                                   activeFilters.strength !== 'all';
            paginationContainer.style.display = hasActiveFilters ? 'none' : 'block';
        }
    }
    
    
    // Clear all filters
    function clearAllFilters() {
        // Reset filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            if (btn.dataset.value === 'all') {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Reset active filters
        activeFilters = {
            category: 'all',
            strength: 'all'
        };
        
        // Show all items
        menuItems.forEach(item => {
            item.style.display = 'flex';
        });
        
        // Update results count
        updateResultsCount(menuItems.length);
        
        // Hide no results message
        const noResults = document.getElementById('noResults');
        if (noResults) {
            noResults.style.display = 'none';
        }
        
        // Show pagination again
        const paginationContainer = document.querySelector('.pagination-container');
        if (paginationContainer) {
            paginationContainer.style.display = 'block';
        }
    }
    
    // Update results count
    function updateResultsCount(count) {
        const resultsCount = document.getElementById('resultsCount');
        if (resultsCount) {
            // Check if we're filtering or showing paginated results
            const hasActiveFilters = activeFilters.category !== 'all' || 
                                   activeFilters.strength !== 'all';
            
            if (hasActiveFilters) {
                // When filtering, show total filtered results
                resultsCount.textContent = `${count} cocktail${count !== 1 ? 's' : ''} (đã lọc)`;
            } else {
                // When not filtering, show pagination info
                const currentPage = <?php echo $current_page; ?>;
                const totalPages = <?php echo $total_pages; ?>;
                const totalItems = <?php echo $total_items; ?>;
                const itemsPerPage = <?php echo $items_per_page; ?>;
                const startItem = (currentPage - 1) * itemsPerPage + 1;
                const endItem = Math.min(currentPage * itemsPerPage, totalItems);
                
                resultsCount.textContent = `${startItem}-${endItem} trong tổng số ${totalItems} cocktail`;
            }
        }
    }
    
}

// Open cocktail detail modal
function openCocktailDetail(cocktailId) {
    currentCocktailId = cocktailId;
    currentCocktailData = cocktailData[cocktailId];
    window.currentCocktailData = currentCocktailData;
    
    if (!currentCocktailData) {
        showAlert('Không tìm thấy thông tin cocktail!', 'error');
        return;
    }
    
    currentImageIndex = 0;
    
    // Populate modal content
    document.getElementById('detailCocktailName').textContent = currentCocktailData.name;
    document.getElementById('detailCocktailPrice').textContent = currentCocktailData.price.toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('detailCocktailDescription').textContent = currentCocktailData.description;
    
    // Populate images
    populateCocktailImages(currentCocktailData.images);
    
    // Populate rating
    populateCocktailRating(currentCocktailData.avg_rating, currentCocktailData.review_count);
    
    // Populate ingredients
    const ingredientsContainer = document.getElementById('detailCocktailIngredients');
    if (currentCocktailData.ingredients) {
        const ingredients = currentCocktailData.ingredients.split(',').map(i => i.trim());
        ingredientsContainer.innerHTML = `
            <h4>Nguyên liệu:</h4>
            <div class="ingredients-list">
                ${ingredients.map(ingredient => `<span class="ingredient">${ingredient}</span>`).join('')}
            </div>
        `;
    } else {
        ingredientsContainer.innerHTML = '<p>Không có thông tin nguyên liệu</p>';
    }
    
    // Populate strength level
    const strengthContainer = document.getElementById('detailCocktailStrength');
    strengthContainer.innerHTML = `
        <h4>Mức độ mạnh:</h4>
        ${getStrengthLevelHTML(currentCocktailData.name)}
    `;
    
    // Populate reviews
    populateCocktailReviews(currentCocktailData.reviews);
    
    // Update navigation buttons
    updateCocktailImageNavButtons();
    
    // Show modal
    document.getElementById('cocktailDetailModal').style.display = 'block';
}

// Close cocktail detail modal
function closeCocktailDetail() {
    document.getElementById('cocktailDetailModal').style.display = 'none';
    currentCocktailId = null;
    currentCocktailData = null;
    currentImageIndex = 0;
}

// Populate cocktail images
function populateCocktailImages(images) {
    const mainImage = document.getElementById('detailMainImage');
    const thumbnailContainer = document.getElementById('thumbnailImages');
    const currentIndexSpan = document.getElementById('currentImageIndex');
    const totalImagesSpan = document.getElementById('totalImages');
    
    // Set main image
    mainImage.src = images[0];
    mainImage.alt = document.getElementById('detailCocktailName').textContent;
    
    // Update counters
    currentIndexSpan.textContent = '1';
    totalImagesSpan.textContent = images.length;
    
    // Clear and populate thumbnails
    thumbnailContainer.innerHTML = '';
    
    images.forEach((image, index) => {
        const thumbnail = document.createElement('img');
        thumbnail.src = image;
        thumbnail.alt = `Ảnh ${index + 1}`;
        thumbnail.className = 'thumbnail-image' + (index === 0 ? ' active' : '');
        thumbnail.onclick = () => switchCocktailImage(index);
        thumbnailContainer.appendChild(thumbnail);
    });
}

// Switch cocktail image
function switchCocktailImage(index) {
    if (!currentCocktailData || !currentCocktailData.images) return;
    
    // Handle navigation with bounds checking
    if (index < 0) index = currentCocktailData.images.length - 1;
    if (index >= currentCocktailData.images.length) index = 0;
    
    currentImageIndex = index;
    
    // Update main image
    document.getElementById('detailMainImage').src = currentCocktailData.images[index];
    
    // Update counters
    document.getElementById('currentImageIndex').textContent = index + 1;
    
    // Update thumbnail active state
    document.querySelectorAll('.thumbnail-image').forEach((thumb, i) => {
        thumb.classList.toggle('active', i === index);
    });
    
    // Update navigation button states
    updateCocktailImageNavButtons();
}

// Update cocktail image navigation buttons
function updateCocktailImageNavButtons() {
    if (!currentCocktailData || !currentCocktailData.images) return;
    
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    
    if (prevBtn) prevBtn.style.display = currentCocktailData.images.length > 1 ? 'block' : 'none';
    if (nextBtn) nextBtn.style.display = currentCocktailData.images.length > 1 ? 'block' : 'none';
}

// Populate cocktail rating
function populateCocktailRating(avgRating, reviewCount) {
    const ratingStars = document.getElementById('detailRatingStars');
    const ratingText = document.getElementById('detailRatingText');
    
    // Populate stars
    ratingStars.innerHTML = '';
    for (let i = 1; i <= 5; i++) {
        const starClass = i <= avgRating ? 'star' : 'star empty';
        ratingStars.innerHTML += `<span class="${starClass}">★</span>`;
    }
    
    // Populate rating text
    ratingText.innerHTML = `
        <span class="rating-value">${avgRating.toFixed(1)}</span>
        <span class="review-count">(${reviewCount} đánh giá)</span>
    `;
}

// Populate cocktail reviews
function populateCocktailReviews(reviews) {
    const reviewsList = document.getElementById('detailReviewsList');
    
    if (!reviews || reviews.length === 0) {
        reviewsList.innerHTML = '<p class="no-reviews">Chưa có đánh giá nào cho cocktail này.</p>';
        return;
    }
    
    reviewsList.innerHTML = reviews.map(review => {
        // Xác định tên người review
        let reviewerName = 'Khách hàng';
        if (review.customer_name) {
            reviewerName = review.customer_name;
        } else if (review.user_name && review.user_name.trim() !== '') {
            reviewerName = review.user_name;
        }
        
        return `
        <div class="review-item">
            <div class="review-header">
                <div class="reviewer-name">${reviewerName}</div>
                <div class="review-rating">
                    ${Array(5).fill().map((_, i) => 
                        `<span class="star ${i < review.rating ? '' : 'empty'}">★</span>`
                    ).join('')}
                </div>
                <div class="review-date">${new Date(review.created_at).toLocaleDateString('vi-VN')}</div>
            </div>
            <div class="review-comment">${review.comment || 'Không có bình luận'}</div>
        </div>
        `;
    }).join('');
}

// Save cocktail to My Drinks
function saveToMyDrinks(cocktailId) {
    if (!cocktailId) {
        showAlert('Vui lòng chọn cocktail để lưu!', 'warning');
        return;
    }
    
    const cocktail = cocktailData[cocktailId];
    if (!cocktail) {
        showAlert('Không tìm thấy thông tin cocktail!', 'error');
        return;
    }
    
    const setName = prompt(`Nhập tên cho "${cocktail.name}" trong My Drinks:`);
    if (!setName || setName.trim() === '') {
        showAlert('Tên không được để trống!', 'warning');
        return;
    }
    
    // Prepare data for saving
    const setData = {
        type: 'cocktail',
        items: { [cocktailId]: 1 },
        cocktail_name: cocktail.name,
        price: cocktail.price
    };
    
    // Send to server
    saveCocktailSet(setName, setData);
}

// Save current cocktail from detail modal
function saveCurrentCocktail() {
    if (currentCocktailId && currentCocktailData) {
        saveToMyDrinks(currentCocktailId);
        closeCocktailDetail();
    }
}

// Save bespoke cocktail
function saveBespokeCocktail() {
    const form = document.getElementById('bespokeForm');
    const formData = new FormData(form);
    
    const setName = formData.get('bespoke_name');
    const base = formData.get('bespoke_base');
    const ingredients = formData.get('bespoke_ingredients');
    const flavor = formData.get('bespoke_flavor');
    const notes = formData.get('bespoke_notes');
    
    if (!setName || !base || !ingredients) {
        showAlert('Vui lòng điền đầy đủ thông tin bắt buộc!', 'warning');
        return;
    }
    
    // Prepare bespoke cocktail data
    const setData = {
        type: 'bespoke_cocktail',
        bespoke_name: setName,
        base_alcohol: base,
        ingredients: ingredients,
        flavor_profile: flavor,
        notes: notes,
        created_at: new Date().toISOString()
    };
    
    // Save to server
    saveCocktailSet(setName, setData);
    
    // Reset form
    form.reset();
    
    // Show success message
    showAlert('Bespoke cocktail đã được lưu thành công!', 'success');
}

// Save cocktail set to server
function saveCocktailSet(setName, setData) {
    const requestData = {
        set_name: setName,
        set_data: setData
    };
    
    fetch('save_set.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            // Reload page to show new saved drink
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Có lỗi xảy ra khi lưu cocktail!', 'error');
    });
}



// Helper function to get strength level HTML
function getStrengthLevelHTML(cocktailName) {
    const nameLower = cocktailName.toLowerCase();
    
    if (nameLower.includes('mocktail') || nameLower.includes('virgin')) {
        return '<span class="strength light">Light</span>';
    } else if (nameLower.includes('whisky') || nameLower.includes('gin') || nameLower.includes('martini')) {
        return '<span class="strength strong">Strong</span>';
    } else {
        return '<span class="strength medium">Medium</span>';
    }
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const cocktailModal = document.getElementById('cocktailDetailModal');
    const qrModal = document.getElementById('qrCodeModal');
    
    if (event.target === cocktailModal) {
        closeCocktailDetail();
    }
    if (event.target === qrModal) {
        closeQRModal();
    }
});

// AJAX pagination function
function changePage(pageNumber) {
    // Show loading state
    const menuGrid = document.querySelector('.menu-items-grid');
    const paginationContainer = document.querySelector('.pagination-container');
    
    if (menuGrid) {
        menuGrid.style.opacity = '0.5';
        menuGrid.style.pointerEvents = 'none';
    }
    
    // Update URL without reloading
    const url = new URL(window.location);
    url.searchParams.set('page', pageNumber);
    window.history.pushState({}, '', url);
    
    // Fetch new page content
    fetch(`cocktail.php?page=${pageNumber}&ajax=1`)
        .then(response => response.text())
        .then(html => {
            // Parse HTML and extract menu items
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update menu items
            const newMenuItems = doc.querySelector('.menu-items-grid');
            if (newMenuItems && menuGrid) {
                menuGrid.innerHTML = newMenuItems.innerHTML;
            }
            
            // Update pagination
            const newPagination = doc.querySelector('.pagination-container');
            if (newPagination && paginationContainer) {
                paginationContainer.innerHTML = newPagination.innerHTML;
            }
            
            // Update results count
            const newResultsCount = doc.querySelector('.results-summary span');
            const currentResultsCount = document.getElementById('resultsCount');
            if (newResultsCount && currentResultsCount) {
                currentResultsCount.textContent = newResultsCount.textContent;
            }
            
            // Reinitialize filters for new items
            initializeAdvancedFilters();
            
            // Scroll to top of menu section smoothly
            const menuSection = document.getElementById('menu');
            if (menuSection) {
                menuSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        })
        .catch(error => {
            console.error('Error loading page:', error);
            showAlert('Có lỗi xảy ra khi tải trang!', 'error');
        })
        .finally(() => {
            // Restore normal state
            if (menuGrid) {
                menuGrid.style.opacity = '1';
                menuGrid.style.pointerEvents = 'auto';
            }
        });
}
</script>

<?php include 'includes/footer.php'; ?>
