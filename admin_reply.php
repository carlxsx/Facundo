<?php
// admin_reply.php
ob_start();
session_start();
require 'db_connection/db.php'; 

ob_clean();
header('Content-Type: application/json');

// 1. Security: Only Admins can use this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. DYNAMIC CHANGE: Use the Admin's real ID (e.g., Carlos) instead of 0
    // This allows the database to know exactly WHICH admin replied.
    $sender_id = $_SESSION['user_id']; 
    
    // 3. Get Target User & Message
    $receiver_id = $_POST['receiver_id'] ?? null;
    $message = trim($_POST['message'] ?? '');

    if ($receiver_id && !empty($message)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$sender_id, $receiver_id, $message]);
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    }
}
?>