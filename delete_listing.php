<?php
require 'db_connection/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['car_id'])) {
    $id = $_POST['car_id'];

    try {
        // This will also delete associated leads if you set up the 'ON DELETE CASCADE' in SQL
        $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: index.php?success=unit_deleted#stc");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}