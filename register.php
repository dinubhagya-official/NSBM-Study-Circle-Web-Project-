<?php
session_start();
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - NSBM Study Circle</title>
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
            padding: 20px 0;
        }

        body::before {
            content: "";
            position: absolute;
            top: -10px; left: -10px; right: -10px; bottom: -10px; 
            background-image: url('images/image2.png'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            
            filter: blur(6px); 
            opacity: 0.6; 
            z-index: -1;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 440px;
            padding: 30px 35px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.8);
            position: relative;
            z-index: 1;
        }

        .logo-box {
            text-align: center;
            margin-bottom: 15px;
        }
        .logo-box a {
            display: inline-block;
            transition: transform 0.3s ease;
        }
        .logo-box a:hover {
            transform: scale(1.05);
        }
        .logo-box img {
            max-width: 210px; 
            width: 100%;      
            height: auto;
        }

        h2 {
            color: #1d4ed8; 
            font-size: 26px; 
            font-weight: 800;
            margin-bottom: 20px;
            text-align: center;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            color: #1d4ed8; 
            font-size: 12px; 
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-group input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 30px 8px 0; 
            font-size: 15px; 
            color: #1e293b;
            outline: none;
            background: transparent;
            transition: 0.3s;
        }

        .input-group input:focus {
            border-bottom: 2px solid #1d4ed8;
        }

        .eye-icon {
            position: absolute;
            right: 0;
            top: 8px;
            cursor: pointer;
            color: #94a3b8;
            width: 18px;
            height: 18px;
        }

        .terms-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 13px; 
            color: #475569;
            cursor: pointer;
        }

        .terms-check input {
            accent-color: #1d4ed8;
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        .btn-group {
            display: flex;
            gap: 12px;
        }

        .btn {
            flex: 1;
            padding: 12px; 
            border-radius: 50px;
            font-size: 15px; 
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            text-align: center;
            text-decoration: none;
        }

        .btn-register {
            background-color: #1d4ed8;
            color: #ffffff;
            border: none;
            box-shadow: 0 4px 15px rgba(29, 78, 216, 0.3);
        }

        .btn-register:hover {
            background-color: #1e3a8a;
            transform: translateY(-2px);
        }

        .btn-login {
            background-color: transparent;
            color: #1d4ed8;
            border: 2px solid #1d4ed8;
        }

        .btn-login:hover {
            background-color: #eff6ff;
        }

        @media (max-width: 600px) {
            .register-card { padding: 25px 20px; margin: 20px; }
            h2 { font-size: 24px; }
        }
    </style>
</head>
<body>

    <div class="register-card">

        <div class="logo-box">
            <a href="index.php">
                <img src="images/nsbm-logo.png" alt="NSBM Logo">
            </a>
        </div>

        <h2>Create Account</h2>

        <form action="register_process.php" method="POST">
            
            <div class="input-group">
                <label>Student Email</label>
                <div class="input-wrapper">
                    <input type="email" name="email" placeholder="kavindu@students.nsbm.ac.lk" required>
                </div>
            </div>

            <div class="input-group">
                <label>Full Name</label>
                <div class="input-wrapper">
                    <input type="text" name="full_name" placeholder="Kavindu Perera" required>
                </div>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" placeholder="Type strong password" required>
                    
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

            <label class="terms-check">
                <input type="checkbox" name="terms" required>
                I agree to the Terms & Conditions
            </label>

            <div class="btn-group">
                <button type="submit" class="btn btn-register">Register</button>
                <a href="login.php" class="btn btn-login">Login</a>
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