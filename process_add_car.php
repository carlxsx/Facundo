<?php
require 'db_connection/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. COLLECT DATA
    $make          = trim($_POST['make'] ?? '');
    $model         = trim($_POST['model'] ?? '');
    $year_produced = $_POST['year'] ?? NULL; 
    $price_php     = $_POST['price'] ?? 0;
    $mileage_km    = $_POST['mileage'] ?? 0;
    
    // --- THE FIX: ADDED A HARD FALLBACK ---
    // If $_POST['transmission'] is empty, it will save "UNKNOWN" 
    // This helps us see if the issue is the FORM or the DATABASE.
    $transmission = !empty($_POST['transmission']) ? $_POST['transmission'] : 'UNKNOWN';
    
    $fuel_type     = $_POST['fuel_type'] ?? 'Gasoline';
    $notes         = $_POST['notes'] ?? '';
    $image_url     = $_POST['image_url'] ?? ''; 
    
    // 2. FILE UPLOAD LOGIC
    $target_dir = "uploads/cars/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    $image_path = "default-car.jpg"; 

    if (isset($_FILES["car_image"]) && $_FILES["car_image"]["error"] == 0) {
        $file_ext = strtolower(pathinfo($_FILES["car_image"]["name"], PATHINFO_EXTENSION));
        $new_name = uniqid() . "_" . strtolower(str_replace(' ', '_', $make)) . "." . $file_ext;
        
        if (move_uploaded_file($_FILES["car_image"]["tmp_name"], $target_dir . $new_name)) {
            $image_path = $new_name;
        }
    }

    // 3. DATABASE INSERT
    try {
        $sql = "INSERT INTO vehicles (
                    make, model, year_produced, price_php, mileage_km, 
                    transmission, fuel_type, status, image_url, image_path
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Available', ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            $make,          // 1
            $model,         // 2
            $year_produced, // 3
            $price_php,     // 4
            $mileage_km,    // 5
            $transmission,  // 6 
            $fuel_type,     // 7
            $image_url,     // 8
            $image_path     // 9
        ]);

        header("Location: index.php?success=unit_added");
        exit();

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}