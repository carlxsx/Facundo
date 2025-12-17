<?php
require_once 'config/init.php';

// Redirect if logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']); // safer than sanitize()
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        setMessage('danger', 'Please fill in all fields');
    } else {

        // FIX 1: Make sure the column names exist
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {   // FIX 2: match your real DB column
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_type'] = $user['user_type'];

            // Update login time
            $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?")
               ->execute([$user['user_id']]);

            // Redirect
            if ($user['user_type'] === 'admin') {
                redirect('admin/index.php');
            } else {
                redirect('dashboard.php');
            }

        } else {
            setMessage('danger', 'Invalid email or password');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4">Login</h2>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                                
                                <button type="submit" class="btn w-100 mb-3" style="background-color: #000; color: #fff; border: 2px solid #000; font-weight: 700; padding: 12px;">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                                
                                <div class="text-center mb-3">
                                    <a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a>
                                </div>
                                
                                <hr>
                                
                                <div class="d-grid gap-2 mb-3">
                                    <button type="button" class="btn btn-outline-danger" onclick="loginWithGoogle()">
                                        <i class="fab fa-google me-2"></i>Login with Google
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" onclick="loginWithFacebook()">
                                        <i class="fab fa-facebook me-2"></i>Login with Facebook
                                    </button>
                                </div>
                                
                                <p class="text-center mb-0">
                                    Don't have an account? <a href="register.php">Register here</a>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loginWithGoogle() {
            alert('Google login integration coming soon!');
            // Implement Google OAuth here
        }
        
        function loginWithFacebook() {
            alert('Facebook login integration coming soon!');
            // Implement Facebook OAuth here
        }
    </script>
</body>
</html>