<?php
// delete_listing.php
require 'db_connection/db.php';
session_start();

// 1. Return JSON header so JavaScript allows it
header('Content-Type: application/json');

// 2. Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 3. Match the variable name sent by JavaScript ('id')
    $id = $_POST['id'] ?? null;

    if($id) {
        try {
            // 4. Delete from the 'vehicles' table
            $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
            $stmt->execute([$id]);
            
            // 5. Send Success Signal back to Javascript
            echo json_encode(['status' => 'success']);
            exit();
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No ID Provided']);
    }
}
?>