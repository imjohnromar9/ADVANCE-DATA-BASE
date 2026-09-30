<?php
session_start();
require 'db.php';
 
$error = "";
$success = "";
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
 
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM Users WHERE username = :username");
        $stmt->execute(['username' => $username]);
 
        if ($stmt->fetch()) {
            $error = "Username already taken!";
        } else {
            // Hash the password before storing it
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
 
            $stmt = $pdo->prepare(
                "INSERT INTO Users (username, password, role) VALUES (:username, :password, :role)"
            );
            $stmt->execute([
                'username' => $username,
                'password' => $hashed_password,
                'role' => 'user'
            ]);
 
            $success = "Account created successfully! You can now log in.";
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
 
<head>
 
    <meta charset="UTF-8">
 
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
 
    <title>Register</title>
 
    <style>
 
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }
 
        body {
            min-height: 100vh;
 
            display: flex;
            justify-content: center;
            align-items: center;
 
            background:
                linear-gradient(
                    135deg,
                    #e0f2fe,
                    #eef2ff,
                    #f8fafc
                );
 
            padding: 20px;
        }
 
        /* Decorative circles */
 
        .circle {
            position: fixed;
            border-radius: 50%;
            z-index: 0;
        }
 
        .circle-one {
            width: 250px;
            height: 250px;
 
            background: #bfdbfe;
 
            top: -80px;
            left: -80px;
        }
 
        .circle-two {
            width: 300px;
            height: 300px;
 
            background: #ddd6fe;
 
            bottom: -120px;
            right: -100px;
        }
 
        /* Register Card */
 
        .register-card {
            position: relative;
            z-index: 1;
 
            width: 100%;
            max-width: 430px;
 
            background: rgba(255, 255, 255, 0.95);
 
            padding: 45px;
 
            border-radius: 24px;
 
            box-shadow:
                0 25px 60px rgba(30, 41, 59, 0.15);
 
            border: 1px solid rgba(255, 255, 255, 0.8);
 
            animation: slideUp 0.6s ease;
        }
 
        /* Logo */
 
        .logo {
            width: 75px;
            height: 75px;
 
            margin: 0 auto 20px;
 
            display: flex;
            align-items: center;
            justify-content: center;
 
            border-radius: 20px;
 
            background:
                linear-gradient(
                    135deg,
                    #3b82f6,
                    #6366f1
                );
 
            color: white;
 
            font-size: 32px;
 
            box-shadow:
                0 12px 25px rgba(59, 130, 246, 0.3);
        }
 
        /* Header */
 
        .register-card h1 {
            text-align: center;
 
            color: #0f172a;
 
            font-size: 28px;
 
            margin-bottom: 8px;
        }
 
        .description {
            text-align: center;
 
            color: #64748b;
 
            font-size: 14px;
 
            margin-bottom: 30px;
 
            line-height: 1.5;
        }
 
        /* Error */
 
        .error {
            display: flex;
            align-items: center;
 
            gap: 10px;
 
            padding: 13px 15px;
 
            margin-bottom: 20px;
 
            border-radius: 10px;
 
            background: #fff1f2;
 
            border: 1px solid #fecdd3;
 
            color: #be123c;
 
            font-size: 13px;
        }
 
        /* Success */
 
        .success {
            display: flex;
            align-items: center;
 
            gap: 10px;
 
            padding: 13px 15px;
 
            margin-bottom: 20px;
 
            border-radius: 10px;
 
            background: #f0fdf4;
 
            border: 1px solid #bbf7d0;
 
            color: #15803d;
 
            font-size: 13px;
        }
 
        /* Input */
 
        .input-group {
            margin-bottom: 20px;
        }
 
        .input-group label {
            display: block;
 
            color: #334155;
 
            font-size: 13px;
 
            font-weight: 600;
 
            margin-bottom: 8px;
        }
 
        .input-box {
            position: relative;
        }
 
        .input-box .icon {
            position: absolute;
 
            left: 15px;
            top: 50%;
 
            transform: translateY(-50%);
 
            font-size: 16px;
 
            color: #94a3b8;
        }
 
        .input-box input {
            width: 100%;
 
            height: 50px;
 
            padding: 0 45px;
 
            border: 1px solid #dbe3ef;
 
            border-radius: 11px;
 
            background: #f8fafc;
 
            color: #0f172a;
 
            font-size: 14px;
 
            outline: none;
 
            transition: 0.25s;
        }
 
        .input-box input:focus {
            background: white;
 
            border-color: #3b82f6;
 
            box-shadow:
                0 0 0 4px rgba(59, 130, 246, 0.10);
        }
 
        .input-box input::placeholder {
            color: #a0aec0;
        }
 
        /* Show password */
 
        .show-password {
            position: absolute;
 
            right: 14px;
            top: 50%;
 
            transform: translateY(-50%);
 
            border: none;
 
            background: transparent;
 
            cursor: pointer;
 
            font-size: 16px;
 
            color: #64748b;
        }
 
        .show-password:hover {
            color: #2563eb;
        }
 
        /* Button */
 
        .register-button {
            width: 100%;
 
            height: 50px;
 
            border: none;
 
            border-radius: 11px;
 
            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #4f46e5
                );
 
            color: white;
 
            font-size: 15px;
 
            font-weight: bold;
 
            cursor: pointer;
 
            transition: 0.25s;
 
            margin-top: 5px;
        }
 
        .register-button:hover {
            transform: translateY(-2px);
 
            box-shadow:
                0 10px 25px rgba(37, 99, 235, 0.3);
        }
 
        .register-button:active {
            transform: translateY(0);
        }
 
        /* Bottom text */
 
        .bottom-text {
            text-align: center;
 
            margin-top: 25px;
 
            color: #94a3b8;
 
            font-size: 12px;
        }
 
        .bottom-text a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }
 
        .bottom-text a:hover {
            text-decoration: underline;
        }
 
        /* Animation */
 
        @keyframes slideUp {
 
            from {
                opacity: 0;
                transform: translateY(30px);
            }
 
            to {
                opacity: 1;
                transform: translateY(0);
            }
 
        }
 
        /* Mobile */
 
        @media (max-width: 500px) {
 
            .register-card {
                padding: 35px 25px;
 
                border-radius: 20px;
            }
 
            .register-card h1 {
                font-size: 24px;
            }
 
            .logo {
                width: 65px;
                height: 65px;
 
                font-size: 28px;
            }
 
        }
 
    </style>
 
</head>
 
<body>
 
    <!-- Background Decorations -->
    <div class="circle circle-one"></div>
    <div class="circle circle-two"></div>
 
 
    <!-- Register Card -->
 
    <div class="register-card">
 
        <!-- Logo -->
 
        <div class="logo">
            📝
        </div>
 
 
        <h1>
            Create Account
        </h1>
 
        <p class="description">
            Fill in your details
            to create a new account.
        </p>
 
 
        <!-- Error Message -->
 
        <?php if (!empty($error)): ?>
 
            <div class="error">
                ⚠️
 
                <span>
                    <?php echo htmlspecialchars($error); ?>
                </span>
            </div>
 
        <?php endif; ?>
 
 
        <!-- Success Message -->
 
        <?php if (!empty($success)): ?>
 
            <div class="success">
                ✅
 
                <span>
                    <?php echo htmlspecialchars($success); ?>
                </span>
            </div>
 
        <?php endif; ?>
 
 
        <!-- Register Form -->
 
        <form method="POST">
 
            <!-- Username -->
 
            <div class="input-group">
 
                <label for="username">
                    Username
                </label>
 
                <div class="input-box">
 
                    <span class="icon">
                        👤
                    </span>
 
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Choose a username"
                        autocomplete="username"
                        required
                    >
 
                </div>
 
            </div>
 
 
            <!-- Password -->
 
            <div class="input-group">
 
                <label for="password">
                    Password
                </label>
 
                <div class="input-box">
 
                    <span class="icon">
                        🔒
                    </span>
 
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a password"
                        autocomplete="new-password"
                        required
                    >
 
                    <button
                        type="button"
                        class="show-password"
                        onclick="togglePassword('password', this)"
                        aria-label="Show password"
                    >
                        👁
                    </button>
 
                </div>
 
            </div>
 
 
            <!-- Confirm Password -->
 
            <div class="input-group">
 
                <label for="confirm_password">
                    Confirm Password
                </label>
 
                <div class="input-box">
 
                    <span class="icon">
                        🔒
                    </span>
 
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Re-enter your password"
                        autocomplete="new-password"
                        required
                    >
 
                    <button
                        type="button"
                        class="show-password"
                        onclick="togglePassword('confirm_password', this)"
                        aria-label="Show password"
                    >
                        👁
                    </button>
 
                </div>
 
            </div>
 
 
            <!-- Register Button -->
 
            <button
                type="submit"
                class="register-button"
            >
                Sign Up
            </button>
 
        </form>
 
 
        <!-- Footer -->
 
        <p class="bottom-text">
            Already have an account? <a href="login.php">Log in</a>
        </p>
 
    </div>
 
 
    <!-- Password Script -->
 
    <script>
 
        function togglePassword(fieldId, button) {
 
            const field =
                document.getElementById(fieldId);
 
            if (field.type === "password") {
 
                field.type = "text";
 
                button.textContent = "🙈";
 
            } else {
 
                field.type = "password";
 
                button.textContent = "👁";
 
            }
 
        }
 
    </script>
 
</body>
</html>