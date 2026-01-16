<?php
// create_staff.php
ob_start();

// Enable Error Reporting (For debugging)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// SAFE SESSION START
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db_connection/db.php'; 

ob_clean();
header('Content-Type: application/json');

// 1. SECURITY CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: You are not an Admin']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $middle = trim($_POST['middle_name'] ?? ''); 
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
            echo json_encode(['status' => 'error', 'message' => 'This email is already registered']);
            exit;
        }

        // 3. Create User
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        
        // We explicitly set phone_number to NULL to avoid strict mode errors
        $sql = "INSERT INTO users (first_name, middle_name, last_name, email, password_hash, role, phone_number, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NULL, NOW())";
        
        $stmt = $pdo->prepare($sql);
        
        if($stmt->execute([$first, $middle, $last, $email, $hashed_pass, $role])) {
            echo json_encode(['status' => 'success']);
        } else {
            // CAPTURE THE EXACT SQL ERROR
            $errorInfo = $stmt->errorInfo();
            // $errorInfo[2] contains the human-readable error message from MySQL
            echo json_encode(['status' => 'error', 'message' => 'SQL Failed: ' . $errorInfo[2]]);
        }

    } catch (PDOException $e) {
        // CAPTURE THE EXACT DATABASE EXCEPTION
        echo json_encode(['status' => 'error', 'message' => 'DB Exception: ' . $e->getMessage()]);
    }
}
?>