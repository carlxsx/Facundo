<?php
session_start();
require 'db.php'; // Ensure this points to your actual db connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Capture Inputs
    $email     = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password  = $_POST['password'];
    $auth_mode = $_POST['auth_mode'] ?? 'login';

    // --- REGISTRATION LOGIC ---
    if ($auth_mode === 'register') {
        // Collect specific name fields from the form
        $first_name  = trim($_POST['first_name']);
        $last_name   = trim($_POST['last_name']);
        $middle_name = trim($_POST['middle_name'] ?? ''); // Optional
        $phone       = trim($_POST['phone']);

        // 1. Check if email exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            header("Location: ../index.php?error=exists");
            exit;
        }

        // 2. Hash Password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert User (MATCHING YOUR EXACT DATABASE COLUMNS)
        // Columns: first_name, middle_name, last_name, email, password_hash, phone_number, role
        $sql = "INSERT INTO users (first_name, middle_name, last_name, email, password_hash, phone_number, role) 
                VALUES (?, ?, ?, ?, ?, ?, 'buyer')";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([$first_name, $middle_name, $last_name, $email, $hashed_password, $phone]);
            
            // 4. Auto-Login Session Setup
            $_SESSION['user_id']    = $pdo->lastInsertId();
            $_SESSION['role']       = 'buyer';
            $_SESSION['first_name'] = $first_name;
            $_SESSION['name']       = $first_name; // Fixes "Guest" display in index.php

            header("Location: ../index.php?success=registered");
            exit;
        } catch (PDOException $e) {
            header("Location: ../index.php?error=system");
            exit;
        }

    } 
    // --- LOGIN LOGIC ---
    else {
        // 1. Find user by Email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 2. Verify Password 
        // CRITICAL FIX: Changed $user['password'] to $user['password_hash'] to match your DB
        if ($user && password_verify($password, $user['password_hash'])) {
            
            // 3. Set Session Variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            
            // CRITICAL FIX: Ensure 'name' is set for index.php to read
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name']  = $user['last_name'];
            $_SESSION['name']       = $user['first_name']; 
            
            header("Location: ../index.php");
            exit;
        } else {
            // Invalid credentials
            header("Location: ../index.php?error=invalid");
            exit;
        }
    }
}
?>