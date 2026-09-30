<?php
require 'db.php';

$message = '';
$error = '';

// Handle "Add User" form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'user';

    if (!in_array($role, ['user', 'admin'], true)) {
        $role = 'user';
    }

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        // Check if the username already exists
        $check = $pdo->prepare("SELECT id FROM Users WHERE username = ?");
        $check->execute([$username]);

        if ($check->fetch()) {
            $error = 'That username is already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO Users (username, password, role) VALUES (?, ?, ?)");
            $insert->execute([$username, $hash, $role]);

            // Redirect so refreshing the page doesn't re-submit the form
            header('Location: admin.php?msg=created');
            exit;
        }
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'created') {
    $message = 'User created successfully.';
}

$stmt = $pdo->query("SELECT id, username, role FROM Users");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard</title>

<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: Arial, sans-serif;
        background: #f4f4f4;
        color: #333;
    }

    .header {
        background: #222;
        color: white;
        padding: 18px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header h2 {
        font-size: 20px;
    }

    .logout {
        background: #e74c3c;
        color: white;
        text-decoration: none;
        padding: 9px 15px;
        border-radius: 5px;
        font-size: 14px;
    }

    .logout:hover {
        background: #c0392b;
    }

    .container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .welcome {
        background: white;
        padding: 30px;
        border-radius: 8px;
        border: 1px solid #ddd;
        margin-bottom: 20px;
    }

    .welcome h1 {
        font-size: 26px;
        margin-bottom: 10px;
    }

    .welcome p {
        color: #777;
    }

    .menu {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }

    .menu a {
        background: white;
        padding: 25px;
        text-align: center;
        text-decoration: none;
        color: #333;
        border: 1px solid #ddd;
        border-radius: 8px;
        transition: 0.2s;
    }

    .menu a:hover {
        background: #3498db;
        color: white;
        border-color: #3498db;
    }

    .menu-icon {
        font-size: 28px;
        margin-bottom: 10px;
    }

    .menu h3 {
        font-size: 16px;
    }

    /* Alerts */
    .alert {
        padding: 12px 16px;
        border-radius: 6px;
        margin-top: 20px;
        font-size: 14px;
    }

    .alert.success {
        background: #e8f8ee;
        color: #1e7e34;
        border: 1px solid #b7e4c7;
    }

    .alert.error {
        background: #fdecea;
        color: #b71c1c;
        border: 1px solid #f5c2c0;
    }

    /* Create user form */
    .create-user {
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 25px;
        margin-top: 20px;
    }

    .create-user h3 {
        margin-bottom: 15px;
    }

    .create-user form {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
        flex: 1 1 180px;
    }

    .form-group label {
        font-size: 13px;
        color: #555;
    }

    .form-group input,
    .form-group select {
        padding: 10px 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
        background: white;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #3498db;
    }

    .add-btn {
        background: #3498db;
        color: white;
        border: none;
        padding: 11px 22px;
        border-radius: 5px;
        font-size: 14px;
        cursor: pointer;
        transition: 0.2s;
    }

    .add-btn:hover {
        background: #2980b9;
    }

    .users-table {
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 25px;
        margin-top: 20px;
        overflow-x: auto;
    }

    .users-table h3 {
        margin-bottom: 15px;
    }

    .users-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .users-table th,
    .users-table td {
        text-align: left;
        padding: 10px 12px;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }

    .users-table th {
        background: #f9f9f9;
        color: #555;
    }

    @media (max-width: 600px) {
        .header {
            padding: 15px 20px;
        }

        .container {
            margin-top: 25px;
        }

        .menu {
            grid-template-columns: 1fr;
        }

        .welcome h1 {
            font-size: 22px;
        }

        .add-btn {
            width: 100%;
        }
    }
</style>
</head>
<body>

<header class="header">
    <h2>Admin Dashboard</h2>
    <a href="logout.php" class="logout">Log Out</a>
</header>

<main class="container">

    <div class="welcome">
        <h1>Welcome, Admin!</h1>
        <p>You are logged in as an administrator.</p>
    </div>

    <div class="menu">
        <a href="#">
            <div class="menu-icon">👥</div>
            <h3>Users</h3>
        </a>

        <a href="#">
            <div class="menu-icon">📋</div>
            <h3>Reports</h3>
        </a>

        <a href="#">
            <div class="menu-icon">⚙️</div>
            <h3>Settings</h3>
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Create New User -->
    <div class="create-user">
        <h3>Create New User</h3>

        <form method="POST" action="admin.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit" name="add_user" class="add-btn">Add User</button>
        </form>
    </div>

    <div class="users-table">
        <h3>Registered Users</h3>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['id']); ?></td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>

</body>
</html>