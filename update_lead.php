<?php
require 'db_connection/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['lead_id'];
    $status = $_POST['new_status'];

    try {
        $stmt = $pdo->prepare("UPDATE leads SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        header("Location: index.php?success=lead_updated#crm");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}