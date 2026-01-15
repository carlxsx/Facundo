<?php
session_start();
require 'db_connection/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['full_name'];
        header("Location: index.php"); // Redirect to home after login
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/style.css">
    <title>FACUNDO | Staff Login</title>
</head>
<body class="bg-[#0b0e14] flex items-center justify-center min-h-screen">
    <div class="max-w-sm w-full p-8 bg-[#161b22] border border-gray-800 rounded-3xl shadow-2xl">
        <div class="text-3xl font-black italic mb-8 text-center text-white">
            FACU<span class="text-cyberlime">NDO</span>
        </div>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="bg-red-500/10 border border-red-500/20 p-3 rounded-xl mb-4 text-center">
                <p class="text-red-500 text-[10px] font-bold uppercase tracking-widest">Access Denied: Invalid Credentials</p>
            </div>
        <?php endif; ?>

        <form action="db_connection/login_handler.php" method="POST" class="space-y-4">
            <div class="space-y-1">
                <label class="text-[9px] font-bold text-gray-500 uppercase ml-2">Internal Email</label>
                <input type="email" name="email" placeholder="admin@facundo.ph" class="input-facundo w-full" required>
            </div>

            <div class="space-y-1">
                <label class="text-[9px] font-bold text-gray-500 uppercase ml-2">Password</label>
                <input type="password" name="password" placeholder="••••••••" class="input-facundo w-full" required>
            </div>

            <button type="submit" class="w-full bg-cyberlime text-black font-black py-4 rounded-xl uppercase text-[10px] tracking-widest hover:bg-white transition-all duration-300 transform active:scale-95">
                Enter Portal
            </button>
        </form>

        <a href="index.php" class="block text-center mt-8 text-gray-500 text-[9px] font-bold uppercase tracking-widest hover:text-cyberlime transition-colors">
            ← Back to Showroom
        </a>
    </div>
</body>
</html>