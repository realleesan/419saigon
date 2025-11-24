<?php
// Website configuration
define('SITE_NAME', '419 Saigon');
define('SITE_URL', 'http://localhost/419saigon');
define('SITE_DESCRIPTION', 'A Japanese Izakaya Meets Cocktail & Cinema');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Load database configuration and connection
require_once __DIR__ . '/database.php';

// Helper functions
function sanitize($data) {
    if ($data === null) {
        return '';
    }
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
?>
