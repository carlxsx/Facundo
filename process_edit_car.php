<?php
session_start();
require 'db_connection/db.php';

// 1. Access Control: Only Admins can edit the fleet
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied: Secure Transmission Protocol Required.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Collect and Sanitize
    $id    = filter_input(INPUT_POST, 'car_id', FILTER_SANITIZE_NUMBER_INT);
    $make  = trim($_POST['make']);
    $model = trim($_POST['model']);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $mile  = filter_input(INPUT_POST, 'mileage', FILTER_SANITIZE_NUMBER_INT);
    $year  = $_POST['year'];

    // 3. Simple Validation
    if (!$id || empty($make) || $price <= 0) {
        header("Location: index.php?error=invalid_data");
        exit();
    }

    try {
        $sql = "UPDATE vehicles SET make=?, model=?, price_php=?, mileage_km=?, year_produced=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$make, $model, $price, $mile, $year, $id]);

        header("Location: index.php?success=updated");
        exit();
    } catch (PDOException $e) {
        // Log error instead of 'die' in production
        error_log("Update Error: " . $e->getMessage());
        header("Location: index.php?error=system_failure");
        exit();
    }
}