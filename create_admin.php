<?php
// create_staff.php
ob_start();
session_start();

// Enable Error Reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connection/db.php'; 

ob_clean();
header('Content-Type: application/json');

// 1. SECURITY CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $middle = trim($_POST['middle_name'] ?? ''); // Optional
    $last = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $role = 'admin'; 

    if (empty($first) || empty($last) || empty($email) || empty($pass)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing Required Fields']);
        exit;
    }

    try {
        // 2. Check Duplicate Email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
            exit;
        }

        // 3. Create User (FIXED COLUMN NAME)
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        
        // CORRECTION: Changed 'password' to 'password_hash' to match your DB
        $sql = "INSERT INTO users (first_name, middle_name, last_name, email, password_hash, role, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        
        if($stmt->execute([$first, $middle, $last, $email, $hashed_pass, $role])) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'SQL Execution Failed']);
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
    }
}
?>