<?php
session_start();

// FIX: Point INTO the folder since we are outside
require 'db_connection/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$my_id = $_SESSION['user_id'];

try {
    $sql = "SELECT m.*, u.first_name, u.last_name 
        FROM messages m 
        LEFT JOIN users u ON m.sender_id = u.id 
        WHERE m.sender_id = ? OR m.receiver_id = ? 
        ORDER BY m.created_at ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$my_id, $my_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'current_user_id' => $my_id,
        'messages' => $history
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>