<?php
require_once 'config/init.php';

// Destroy all session data
session_unset();
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Start new session for message
session_start();
setMessage('success', 'You have been logged out successfully');

// Redirect to home page
redirect('index.php');
?>