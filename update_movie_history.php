<?php
// Update Movie History
// This file handles updating user movie history after a cinema booking is completed

require_once 'includes/config.php';

function updateUserMovieHistory($pdo, $user_id, $booking_id) {
    try {
        // Get cinema booking details
        $booking_stmt = $pdo->prepare("
            SELECT cb.*, b.date, b.time, b.guests
            FROM cinema_bookings cb
            JOIN bookings b ON cb.booking_id = b.id
            WHERE cb.booking_id = ? AND b.user_id = ?
        ");
        
        $booking_stmt->execute([$booking_id, $user_id]);
        $cinema_booking = $booking_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cinema_booking) {
            return false;
        }
        
        // Check if history already exists
        $existing_stmt = $pdo->prepare("
            SELECT id FROM user_movie_history 
            WHERE user_id = ? AND booking_id = ?
        ");
        $existing_stmt->execute([$user_id, $booking_id]);
        
        if ($existing_stmt->fetch()) {
            return true; // Already exists
        }
        
        // Insert new movie history
        $history_stmt = $pdo->prepare("
            INSERT INTO user_movie_history (
                user_id, booking_id, movie_id, combo_id, 
                watched_at, rating, notes, created_at
            ) VALUES (?, ?, ?, ?, ?, NULL, '', NOW())
        ");
        
        $watched_at = $cinema_booking['date'] . ' ' . $cinema_booking['time'];
        
        $history_stmt->execute([
            $user_id,
            $booking_id,
            $cinema_booking['movie_id'],
            $cinema_booking['combo_id'],
            $watched_at
        ]);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error updating movie history: " . $e->getMessage());
        return false;
    }
}

function getUserMovieHistory($pdo, $user_id, $limit = 10) {
    try {
        $history_stmt = $pdo->prepare("
            SELECT umh.*, m.title, m.poster, cc.name as combo_name
            FROM user_movie_history umh 
            JOIN movies m ON umh.movie_id = m.id 
            LEFT JOIN cinema_combos cc ON umh.combo_id = cc.id 
            WHERE umh.user_id = ? 
            ORDER BY umh.watched_at DESC 
            LIMIT ?
        ");
        
        $history_stmt->execute([$user_id, $limit]);
        return $history_stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error getting movie history: " . $e->getMessage());
        return [];
    }
}

function updateMovieRating($pdo, $user_id, $history_id, $rating, $notes = '') {
    try {
        $update_stmt = $pdo->prepare("
            UPDATE user_movie_history 
            SET rating = ?, notes = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        $update_stmt->execute([$rating, $notes, $history_id, $user_id]);
        return $update_stmt->rowCount() > 0;
        
    } catch (Exception $e) {
        error_log("Error updating movie rating: " . $e->getMessage());
        return false;
    }
}

function getPopularMovies($pdo, $limit = 5) {
    try {
        $popular_stmt = $pdo->prepare("
            SELECT m.*, COUNT(umh.id) as watch_count, AVG(umh.rating) as avg_rating
            FROM movies m
            LEFT JOIN user_movie_history umh ON m.id = umh.movie_id
            WHERE m.is_available = 1
            GROUP BY m.id
            ORDER BY watch_count DESC, avg_rating DESC
            LIMIT ?
        ");
        
        $popular_stmt->execute([$limit]);
        return $popular_stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error getting popular movies: " . $e->getMessage());
        return [];
    }
}

function getRecommendedCombos($pdo, $user_id, $limit = 3) {
    try {
        // Get user's most watched movie genres
        $genre_stmt = $pdo->prepare("
            SELECT m.genre, COUNT(*) as count
            FROM user_movie_history umh
            JOIN movies m ON umh.movie_id = m.id
            WHERE umh.user_id = ?
            GROUP BY m.genre
            ORDER BY count DESC
            LIMIT 1
        ");
        
        $genre_stmt->execute([$user_id]);
        $favorite_genre = $genre_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$favorite_genre) {
            // Return general combos if no history
            $combo_stmt = $pdo->prepare("
                SELECT * FROM cinema_combos 
                WHERE is_available = 1 
                ORDER BY sort_order 
                LIMIT ?
            ");
            $combo_stmt->execute([$limit]);
            return $combo_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Return combos that match user's favorite genre or general combos
        $combo_stmt = $pdo->prepare("
            SELECT cc.*, 
                   CASE 
                       WHEN cc.movie_id IS NULL THEN 1
                       WHEN m.genre = ? THEN 2
                       ELSE 0
                   END as priority
            FROM cinema_combos cc
            LEFT JOIN movies m ON cc.movie_id = m.id
            WHERE cc.is_available = 1
            ORDER BY priority DESC, cc.sort_order
            LIMIT ?
        ");
        
        $combo_stmt->execute([$favorite_genre['genre'], $limit]);
        return $combo_stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error getting recommended combos: " . $e->getMessage());
        return [];
    }
}

// API endpoints for AJAX calls
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'];
    $pdo = getDBConnection();
    
    switch ($action) {
        case 'update_rating':
            $history_id = (int)($_POST['history_id'] ?? 0);
            $rating = (int)($_POST['rating'] ?? 0);
            $notes = sanitize($_POST['notes'] ?? '');
            
            if ($rating < 1 || $rating > 5) {
                echo json_encode(['success' => false, 'message' => 'Đánh giá phải từ 1 đến 5 sao']);
                exit;
            }
            
            if (updateMovieRating($pdo, $user_id, $history_id, $rating, $notes)) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật đánh giá thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật đánh giá']);
            }
            break;
            
        case 'get_recommendations':
            $recommended_combos = getRecommendedCombos($pdo, $user_id);
            echo json_encode(['success' => true, 'combos' => $recommended_combos]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
    }
    exit;
}
?>
