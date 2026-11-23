<?php
session_start();
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NSBM Study Circle</title>
    <link rel="icon" type="image/png" href="images/logo-icon.png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8fafc;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            top: -10px; left: -10px; right: -10px; bottom: -10px; 
            background-image: url('images/image1.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;

            filter: blur(6px); 
            opacity: 0.8; 
            z-index: -1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 440px;
            padding: 40px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.8);
            position: relative;
            z-index: 1;
        }

        .logo-box {
            text-align: center;
            margin-bottom: 25px; 
        }
        .logo-box a {
            display: inline-block;
            transition: transform 0.3s ease;
        }
        .logo-box a:hover {
            transform: scale(1.05);
        }
        .logo-box img {
            max-width: 240px;
            width: 100%;      
            height: auto;
        }

        h2 {
            color: #22c55e;
            font-size: 28px; 
            font-weight: 800;
            margin-bottom: 25px;
            text-align: center;
        }

        .input-group {
            margin-bottom: 20px; 
        }

        .input-group label {
            display: block;
            color: #22c55e;
            font-size: 13px; 
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-group input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 30px 8px 0; 
            font-size: 16px; 
            color: #1e293b;
            outline: none;
            background: transparent;
            transition: 0.3s;
        }

        .input-group input:focus {
            border-bottom: 2px solid #22c55e;
        }

        .eye-icon {
            position: absolute;
            right: 0;
            top: 8px;
            cursor: pointer;
            color: #94a3b8;
            width: 20px;
            height: 20px;
        }

        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px; 
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            cursor: pointer;
        }

        .remember-me input {
            accent-color: #22c55e;
            width: 16px; 
            height: 16px;
            cursor: pointer;
        }

        .forgot-pass {
            color: #475569;
            text-decoration: underline;
        }

        .btn-group {
            display: flex;
            gap: 15px;
        }

        .btn {
            flex: 1;
            padding: 12px; 
            border-radius: 50px;
            font-size: 16px; 
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            text-align: center;
            text-decoration: none;
        }

        .btn-login {
            background-color: #22c55e;
            color: #ffffff;
            border: none;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        }

        .btn-login:hover {
            background-color: #16a34a;
            transform: translateY(-2px);
        }

        .btn-signup {
            background-color: transparent;
            color: #22c55e;
            border: 2px solid #22c55e;
        }

        .btn-signup:hover {
            background-color: #f0fdf4;
        }

        @media (max-width: 600px) {
            .login-card { padding: 30px 20px; margin: 20px; }
            h2 { font-size: 24px; }
        }
    </style>
</head>
<body>

    <div class="login-card">

        <div class="logo-box">
            <a href="index.php">
                <img src="images/nsbm-logo.png" alt="NSBM Logo">
            </a>
        </div>

        <h2>Welcome Back</h2>

        <form action="login_process.php" method="POST">
            
            <div class="input-group">
                <label>User Name</label>
                <div class="input-wrapper">
                    <input type="text" name="email" placeholder="Enter your username" required>
                </div>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                    
                    <svg id="eye-off" class="eye-icon" onclick="togglePassword()" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>

                    <svg id="eye-on" class="eye-icon" onclick="togglePassword()" style="display: none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </div>
            </div>

            <div class="options">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    Remember Me
                </label>
                <a href="#" class="forgot-pass">Forgot Password</a>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-login">Login</button>
                <a href="register.php" class="btn btn-signup">Register</a>
            </div>

        </form>
    </div>

    <script>
        function togglePassword() {
            var passInput = document.getElementById("password");
            var eyeOffIcon = document.getElementById("eye-off");
            var eyeOnIcon = document.getElementById("eye-on");

            if (passInput.type === "password") {
                passInput.type = "text";
                eyeOffIcon.style.display = "none";
                eyeOnIcon.style.display = "block";
            } else {
                passInput.type = "password";
                eyeOffIcon.style.display = "block";
                eyeOnIcon.style.display = "none";
            }
        }
    </script>

</body>
</html>