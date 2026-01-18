<?php
require 'db_connection/db.php';
session_start();

// Security: Only allow admins to update status
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['car_id'];
    $status = $_POST['new_status'];

    try {
        $stmt = $pdo->prepare("UPDATE vehicles SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        header("Location: index.php?success=status_updated#stc");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}