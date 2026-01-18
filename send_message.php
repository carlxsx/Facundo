<?php
// send_message.php (In the Main/Root Folder)

// 1. Silent Buffer (Prevents errors from breaking JSON)
ob_start();

session_start();

// FIX: Point INTO the folder to find the database connection
require 'db_connection/db.php'; 

// 2. Clear Buffer & Set Header
ob_clean();
header('Content-Type: application/json');

// 3. Security Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = 0; // Admin
    
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    if (!empty($message)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$sender_id, $receiver_id, $message])) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database write failed']);
            }
            
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Message empty']);
    }
}
?>