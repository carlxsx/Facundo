<?php
session_start();
// If already logged in, send to index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/style.css">
    <title>FACUNDO | Access Portal</title>
    <style>
        .input-facundo {
            background: #0d1117;
            border: 1px solid #1f2937;
            padding: 1rem;
            border-radius: 0.75rem;
            color: white;
            font-size: 0.875rem;
            transition: all 0.3s;
        }
        .input-facundo:focus {
            border-color: #dfff00; /* cyberlime */
            outline: none;
            box-shadow: 0 0 0 2px rgba(223, 255, 0, 0.1);
        }
        .text-cyberlime { color: #dfff00; }
        .bg-cyberlime { background-color: #dfff00; }
    </style>
</head>
<body class="bg-[#0b0e14] flex items-center justify-center min-h-screen p-6">
    <div class="max-w-sm w-full p-8 bg-[#161b22] border border-gray-800 rounded-3xl shadow-2xl">
        <div class="text-3xl font-black italic mb-8 text-center text-white">
            FACU<span class="text-cyberlime">NDO</span>
        </div>
        
        <h2 id="modal-title" class="text-white text-xs font-bold uppercase tracking-[0.2em] mb-8 text-center opacity-70">Internal Access Only</h2>

        <?php if(isset($_GET['error'])): ?>
            <div class="bg-red-500/10 border border-red-500/20 p-3 rounded-xl mb-6 text-center">
                <p class="text-red-500 text-[10px] font-bold uppercase tracking-widest">
                    <?php 
                        if($_GET['error'] == 'exists') echo "Email already registered";
                        else echo "Access Denied: Invalid Credentials";
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <form action="db_connection/login_handler.php" method="POST" class="space-y-4">
            <input type="hidden" name="auth_mode" id="auth_mode" value="login">

            <div id="registration-fields" class="hidden space-y-4">
                <div class="space-y-1">
                    <label class="text-[9px] font-bold text-gray-500 uppercase ml-2">Full Name</label>
                    <input type="text" name="full_name" id="reg_name" placeholder="Juan Dela Cruz" class="input-facundo w-full">
                </div>
                <div class="space-y-1">
                    <label class="text-[9px] font-bold text-gray-500 uppercase ml-2">Phone Number</label>
                    <input type="text" name="phone" id="reg_phone" placeholder="0917 ••• ••••" class="input-facundo w-full">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[9px] font-bold text-gray-500 uppercase ml-2">Email</label>
                <input type="email" name="email" placeholder="identity@facundo.ph" class="input-facundo w-full" required>
            </div>

            <div class="space-y-1">
                <label class="text-[9px] font-bold text-gray-500 uppercase ml-2">Password</label>
                <input type="password" name="password" placeholder="••••••••" class="input-facundo w-full" required>
            </div>

            <button type="submit" id="submit-btn" class="w-full bg-cyberlime text-black font-black py-4 rounded-xl uppercase text-[10px] tracking-widest hover:bg-white transition-all duration-300 transform active:scale-95 shadow-lg shadow-cyberlime/10">
                Enter Portal
            </button>
        </form>

        <div class="mt-8 space-y-4">
            <button onclick="toggleRegister()" id="toggle-link" class="block w-full text-center text-gray-400 text-[9px] font-bold uppercase tracking-widest hover:text-cyberlime transition-colors">
                New Buyer? Join the Fleet
            </button>
            
            <a href="index.php" class="block text-center text-gray-600 text-[9px] font-bold uppercase tracking-widest hover:text-white transition-colors">
                ← Back to Showroom
            </a>
        </div>
    </div>

    <script>
        function toggleRegister() {
            const mode = document.getElementById('auth_mode');
            const regFields = document.getElementById('registration-fields');
            const title = document.getElementById('modal-title');
            const submitBtn = document.getElementById('submit-btn');
            const toggleLink = document.getElementById('toggle-link');
            
            const nameInput = document.getElementById('reg_name');
            const phoneInput = document.getElementById('reg_phone');

            if (mode.value === 'login') {
                mode.value = 'register';
                regFields.classList.remove('hidden');
                title.innerText = "Create Identity";
                submitBtn.innerText = "Register Identity";
                toggleLink.innerText = "Already have access? Sign In";
                nameInput.required = true;
                phoneInput.required = true;
            } else {
                mode.value = 'login';
                regFields.classList.add('hidden');
                title.innerText = "Internal Access Only";
                submitBtn.innerText = "Enter Portal";
                toggleLink.innerText = "New Buyer? Join the Fleet";
                nameInput.required = false;
                phoneInput.required = false;
            }
        }
    </script>
</body>
</html>