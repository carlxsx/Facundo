<?php
require 'db_connection/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $v_id = $_POST['vehicle_id'];
    $name = $_POST['client_name'];
    $phone = $_POST['client_phone'];

    try {
        $sql = "INSERT INTO leads (vehicle_id, client_name, client_phone, status) VALUES (?, ?, ?, 'New')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$v_id, $name, $phone]);

        // Redirect with a thank you message
        header("Location: index.php?inquiry=sent#showroom");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}