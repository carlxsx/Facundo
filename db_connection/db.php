<?php
$host = 'sql111.infinityfree.com';
$db   = 'if0_40582828_facundo_db';
$user = 'if0_40582828'; 
$pass = '96aSE646qDTxd';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>