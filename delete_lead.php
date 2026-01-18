<?php
require 'db_connection/db.php';
session_start();

// Security: Only Admin can delete
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['lead_id'])) {
    $id = $_POST['lead_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM leads WHERE id = ?");
        $stmt->execute([$id]);
        
        // Redirect back to CRM section
        header("Location: index.php#crm");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}