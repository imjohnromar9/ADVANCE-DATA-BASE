<?php
session_start(); // Must be the very first line!
require 'db.php';

$error = "";
$migrated = [];
$skipped = [];
$ran_migration = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user safely using PDO
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    // Verify login
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: admin.php");
            exit();
        } else {
            header("Location: user.php");
            exit();
        }
    } else {
        $error = "Invalid username or password!";
    }
}

// One-time helper: hash any plaintext passwords still in the Users table (admin included)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['migrate_passwords'])) {
    $ran_migration = true;

    $stmt = $pdo->query("SELECT id, username, password FROM Users");
    $all_users = $stmt->fetchAll();

    foreach ($all_users as $u) {
        if (str_starts_with($u['password'], '$2y$')) {
            $skipped[] = $u['username'];
            continue;
        }

        $newHash = password_hash($u['password'], PASSWORD_DEFAULT);

        $update = $pdo->prepare("UPDATE Users SET password = :password WHERE id = :id");
        $update->execute([
            'password' => $newHash,
            'id' => $u['id']
        ]);

        $migrated[] = $u['username'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login</title>

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

        /* Login Card */

        .login-card {
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

        .login-card h1 {
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

        .login-button {
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

        .login-button:hover {
            transform: translateY(-2px);

            box-shadow:
                0 10px 25px rgba(37, 99, 235, 0.3);
        }

        .login-button:active {
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

        /* Migration Tool */

        .migrate-tool {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #eef2f7;
        }

        .migrate-tool summary {
            cursor: pointer;
            font-size: 13px;
            color: #64748b;
            font-weight: 600;
            outline: none;
        }

        .migrate-tool .migrate-body {
            margin-top: 15px;
        }

        .migrate-tool button {
            width: 100%;
            height: 42px;
            border: none;
            border-radius: 10px;
            background: #64748b;
            color: white;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
        }

        .migrate-result {
            margin-top: 12px;
        }

        .migrate-result h4 {
            font-size: 11px;
            color: #334155;
            margin: 10px 0 6px;
        }

        .migrate-result ul {
            list-style: none;
        }

        .migrate-result li {
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .migrate-result .done {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .migrate-result .skip {
            background: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
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

            .login-card {
                padding: 35px 25px;

                border-radius: 20px;
            }

            .login-card h1 {
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


    <!-- Login Card -->

    <div class="login-card">

        <!-- Logo -->

        <div class="logo">
            🔐
        </div>


        <h1>
            Welcome Back
        </h1>

        <p class="description">
            Please enter your account details
            to access the system.
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


        <!-- Login Form -->

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
                        placeholder="Enter your username"
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
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                </div>

            </div>


            <!-- Login Button -->

            <button
                type="submit"
                class="login-button"
            >
                Sign In
            </button>

        </form>


        <!-- Footer -->

        <p class="bottom-text">
            Don't have an account? <a href="register.php">Create new account</a>
        </p>

        <p class="bottom-text">
            © 2026 Your System. All rights reserved.
        </p>


        <!-- Migration Tool (dev tool: remove when done) -->

        <details class="migrate-tool">

            <summary>🔧 Hash old plaintext passwords (dev tool)</summary>

            <div class="migrate-body">

                <form method="POST">
                    <button type="submit" name="migrate_passwords" value="1">
                        Hash All Old Passwords (incl. admin)
                    </button>
                </form>

                <?php if ($ran_migration): ?>
                    <div class="migrate-result">

                        <h4>✅ Hashed just now (<?php echo count($migrated); ?>)</h4>
                        <ul>
                            <?php foreach ($migrated as $name): ?>
                                <li class="done"><?php echo htmlspecialchars($name); ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <h4>⏭ Already hashed (<?php echo count($skipped); ?>)</h4>
                        <ul>
                            <?php foreach ($skipped as $name): ?>
                                <li class="skip"><?php echo htmlspecialchars($name); ?></li>
                            <?php endforeach; ?>
                        </ul>

                    </div>
                <?php endif; ?>

            </div>

        </details>

    </div>

</body>