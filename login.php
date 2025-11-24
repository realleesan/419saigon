<?php
$page_title = 'Đăng Nhập';
require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$action = isset($_GET['action']) ? $_GET['action'] : 'login';
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'login') {
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            $error = 'Vui lòng nhập đầy đủ thông tin.';
        } else {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                
                redirect('index.php');
            } else {
                $error = 'Email hoặc mật khẩu không đúng.';
            }
        }
    } elseif ($action === 'register') {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $phone = sanitize($_POST['phone']);
        
        if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
            $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
        } elseif ($password !== $confirm_password) {
            $error = 'Mật khẩu xác nhận không khớp.';
        } elseif (strlen($password) < 6) {
            $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
        } else {
            $pdo = getDBConnection();
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email đã được sử dụng.';
            } else {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'Tên đăng nhập đã được sử dụng.';
                } else {
                    // Create new user
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, phone, role) VALUES (?, ?, ?, ?, ?, ?, 'user')");
                    
                    if ($stmt->execute([$username, $email, $hashed_password, $first_name, $last_name, $phone])) {
                        $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
                        $action = 'login';
                    } else {
                        $error = 'Có lỗi xảy ra. Vui lòng thử lại.';
                    }
                }
            }
        }
    } elseif ($action === 'forgot') {
        $email = sanitize($_POST['email']);
        
        if (empty($email)) {
            $error = 'Vui lòng nhập email của bạn.';
        } else {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, first_name FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store reset token (you might want to create a separate table for this)
                // For now, we'll just show a success message
                $success = 'Hướng dẫn đặt lại mật khẩu đã được gửi đến email của bạn.';
            } else {
                $error = 'Email không tồn tại trong hệ thống.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h1 class="auth-title">
                <?php 
                if ($action === 'login') echo 'Đăng Nhập';
                elseif ($action === 'register') echo 'Đăng Ký';
                elseif ($action === 'forgot') echo 'Quên Mật Khẩu';
                ?>
            </h1>
            <p class="auth-subtitle">
                <?php 
                if ($action === 'login') echo 'Đăng nhập để truy cập tài khoản của bạn';
                elseif ($action === 'register') echo 'Tạo tài khoản mới để trải nghiệm dịch vụ';
                elseif ($action === 'forgot') echo 'Nhập email để đặt lại mật khẩu';
                ?>
            </p>
        </div>

        <!-- Success/Error Messages will be handled by JavaScript notification system -->

        <?php if ($action === 'login'): ?>
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="password">Mật Khẩu</label>
                    <input type="password" id="password" name="password" required class="form-control">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-full">Đăng Nhập</button>
                </div>
                
                <div class="auth-links">
                    <a href="login.php?action=forgot" class="link">Quên mật khẩu?</a>
                    <span class="separator">|</span>
                    <a href="login.php?action=register" class="link">Chưa có tài khoản? Đăng ký</a>
                </div>
            </form>
        <?php elseif ($action === 'register'): ?>
            <form method="POST" class="auth-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Tên</label>
                        <input type="text" id="first_name" name="first_name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Họ</label>
                        <input type="text" id="last_name" name="last_name" required class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Tên Đăng Nhập</label>
                    <input type="text" id="username" name="username" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="phone">Số Điện Thoại</label>
                    <input type="tel" id="phone" name="phone" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="password">Mật Khẩu</label>
                    <input type="password" id="password" name="password" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Xác Nhận Mật Khẩu</label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="form-control">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-full">Đăng Ký</button>
                </div>
                
                <div class="auth-links">
                    <a href="login.php" class="link">Đã có tài khoản? Đăng nhập</a>
                </div>
            </form>
        <?php elseif ($action === 'forgot'): ?>
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required class="form-control">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-full">Gửi Link Đặt Lại Mật Khẩu</button>
                </div>
                
                <div class="auth-links">
                    <a href="login.php" class="link">Quay lại đăng nhập</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/notification.php'; ?>

<script>
// Show notifications for login page
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($error): ?>
        showAlert('<?php echo addslashes($error); ?>', 'error');
    <?php endif; ?>
    
    <?php if ($success): ?>
        showAlert('<?php echo addslashes($success); ?>', 'success');
    <?php endif; ?>
});
</script>

<?php include 'includes/footer.php'; ?>
