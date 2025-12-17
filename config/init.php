<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database
require_once __DIR__ . '/database.php';

// Site settings
define('SITE_NAME', 'AutoHub Car Marketplace');
define('SITE_URL', 'https://locars.rf.gd'); // Change this to your domain
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Create upload directories if they don't exist
$upload_dirs = [
    UPLOAD_PATH . 'cars/',
    UPLOAD_PATH . 'profiles/',
    UPLOAD_PATH . 'chat/',
    UPLOAD_PATH . 'documents/'
];

foreach ($upload_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Helper functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function setMessage($type, $message) {
    $_SESSION['message'] = [
        'type' => $type,
        'text' => $message
    ];
}

function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' mins ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $timestamp);
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>