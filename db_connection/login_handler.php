<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Find user in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // 2. Verify password (assuming you used password_hash in your database)
    if ($user && password_verify($password, $user['password_hash'])) {
        
        // 3. Set Session Variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role']; // 'admin' or 'buyer'
        $_SESSION['name']    = $user['full_name'];
        
        // 4. Send back to the dashboard
        header("Location: ../index.php");
        exit;
    } else {
        // 5. If it fails, go back and trigger the error message in the modal
        header("Location: ../index.php?error=1");
        exit;
    }
}