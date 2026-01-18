<?php
// db_connection/db.php

<<<<<<< HEAD
$host = 'localhost';
$db   = 'facundo_db';
$user = 'root'; // <--- CHANGE THIS (Remove @localhost)
$pass = '';     // Default XAMPP password is empty
=======
$host = 'sql111.infinityfree.com';
$db   = 'if0_40582828_facundo_db';
$user = 'if0_40582828'; 
$pass = '96aSE646qDTxd';
>>>>>>> origin/main

// 1. FORCE MANILA TIMEZONE (PHP Side)
date_default_timezone_set('Asia/Manila');

try {
    // Connect to Database
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. FORCE MANILA TIMEZONE (MySQL Database Side)
    // This makes sure NOW() and CURRENT_TIMESTAMP use Philippine Time
    $pdo->exec("SET time_zone = '+08:00';");

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>