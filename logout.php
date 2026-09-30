<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .logout-card {
            width: 90%;
            max-width: 420px;
            background: #ffffff;
            padding: 45px 35px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .logout-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #f3e8ff;
            color: #7c3aed;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
        }

        h1 {
            color: #222;
            margin-bottom: 12px;
            font-size: 28px;
        }

        p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .login-btn {
            display: inline-block;
            width: 100%;
            padding: 14px;
            background: #7c3aed;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
        }

        .login-btn:hover {
            background: #6d28d9;
            transform: translateY(-2px);
        }

        .footer {
            margin-top: 20px;
            color: #999;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <div class="logout-card">

        <div class="logout-icon">
            ✓
        </div>

        <h1>You're Logged Out</h1>

        <p>
            Your session has been successfully ended.
            Thank you for visiting!
        </p>

        <a href="login.php" class="login-btn">
            Back to Login
        </a>

        <div class="footer">
            Your account is secure.
        </div>

    </div>

</body>
</html>