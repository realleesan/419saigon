<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php">
                        <span class="logo-text">419</span>
                        <span class="logo-subtitle">Saigon</span>
                    </a>
                </div>
                
                <div class="nav-menu" id="nav-menu">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Trang Chủ</a>
                        </li>
                        <!-- Izakaya removed per client request -->
                        <li class="nav-item">
                            <a href="cocktail.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cocktail.php' ? 'active' : ''; ?>">Cocktail</a>
                        </li>
                        <li class="nav-item">
                            <a href="cinema.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cinema.php' ? 'active' : ''; ?>">Cinema</a>
                        </li>
                        <li class="nav-item">
                            <a href="about.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">Về Chúng Tôi</a>
                        </li>
                        <li class="nav-item">
                            <a href="contact.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Liên Hệ</a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-actions">
                    <?php if (isLoggedIn()): ?>
                        <div class="user-menu">
                            <a href="account.php" class="btn btn-secondary">
                                <?php 
                                // Hiển thị tên đăng nhập hoặc tên đầy đủ nếu có
                                if (isset($_SESSION['user_name']) && !empty(trim($_SESSION['user_name']))) {
                                    echo htmlspecialchars($_SESSION['user_name']);
                                } else {
                                    // Fallback về username nếu có
                                    $pdo = getDBConnection();
                                    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    $user = $stmt->fetch();
                                    echo htmlspecialchars($user['username'] ?? 'Tài Khoản');
                                }
                                ?>
                            </a>
                            <div class="user-dropdown">
                                <a href="account.php" class="dropdown-item">Thông Tin Cá Nhân</a>
                                <a href="logout.php" class="dropdown-item">Đăng Xuất</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-secondary">Tài Khoản</a>
                    <?php endif; ?>
                </div>
                
                <div class="nav-toggle" id="nav-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="main-content">
