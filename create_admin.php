<?php
require 'db_connection/db.php'; // Path to the file you just showed me

$full_name = "Facundo Admin";
$email = "admin@facundo.ph";
$password = "admin123";
$role = "admin";

// Create the hash specifically on YOUR server
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // 1. Clear existing user to avoid "Duplicate Entry" errors
    $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);

    // 2. Insert fresh admin
    $sql = "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$full_name, $email, $hashed_password, $role]);

    echo "✅ Admin Created Successfully!<br>";
    echo "<b>Email:</b> " . $email . "<br>";
    echo "<b>Password:</b> " . $password;
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>