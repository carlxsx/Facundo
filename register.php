<?php
require_once 'config/init.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : 'buyer';
    
    $errors = [];
    
    // Validation
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $errors[] = 'Please fill in all required fields';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    // Check if username exists
    $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $errors[] = 'Username already taken';
    }
    
    // Check if email exists
    $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email already registered';
    }
    
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash, full_name, phone, user_type) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$username, $email, $password_hash, $full_name, $phone, $user_type])) {
            setMessage('success', 'Registration successful! Please login.');
            redirect('login.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            setMessage('danger', $error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4">Create Account</h2>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Username *</label>
                                    <input type="text" name="username" class="form-control" required value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="full_name" class="form-control" required value="<?php echo isset($_POST['full_name']) ? $_POST['full_name'] : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" placeholder="+63" value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Password *</label>
                                    <input type="password" name="password" class="form-control" required minlength="6">
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Confirm Password *</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">I want to: *</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="user_type" value="buyer" id="buyer" checked>
                                        <label class="form-check-label" for="buyer">
                                            Buy a car
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="user_type" value="seller" id="seller">
                                        <label class="form-check-label" for="seller">
                                            Sell a car
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="user_type" value="buyer" id="both">
                                        <label class="form-check-label" for="both">
                                            Both
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="terms.php" target="_blank">Terms & Conditions</a>
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn w-100 mb-3" style="background-color: #000; color: #fff; border: 2px solid #000; font-weight: 700; padding: 12px;">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                                
                                <hr>
                                
                                <div class="d-grid gap-2 mb-3">
                                    <button type="button" class="btn" onclick="registerWithGoogle()" style="background-color: #fff; color: #000; border: 2px solid #000; font-weight: 600;">
                                        <i class="fab fa-google me-2"></i>Sign up with Google
                                    </button>
                                    <button type="button" class="btn" onclick="registerWithFacebook()" style="background-color: #fff; color: #000; border: 2px solid #000; font-weight: 600;">
                                        <i class="fab fa-facebook me-2"></i>Sign up with Facebook
                                    </button>
                                </div>
                                
                                <p class="text-center mb-0">
                                    Already have an account? <a href="login.php">Login here</a>
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
        function registerWithGoogle() {
            alert('Google registration coming soon!');
        }
        
        function registerWithFacebook() {
            alert('Facebook registration coming soon!');
        }
    </script>
</body>
</html>