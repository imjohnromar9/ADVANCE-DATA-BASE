<?php
session_start();

// Security Guard: Just check for the wristband
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$pdo = new PDO("mysql:host=localhost;dbname=aguadodb;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Get the user's id (looks it up by username if login.php didn't save it)
if (!isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $_SESSION['user_id'] = $stmt->fetchColumn();
}
$userId = $_SESSION['user_id'];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        if ($title !== '') {
            $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $title, $desc]);
            $_SESSION['flash'] = "Task added successfully!";
        }
    } elseif ($action === 'toggle') {
        $stmt = $pdo->prepare("UPDATE tasks SET status = IF(status='pending','completed','pending') WHERE id = ? AND user_id = ?");
        $stmt->execute([(int)$_POST['id'], $userId]);
    } elseif ($action === 'remove') {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([(int)$_POST['id'], $userId]);
        $_SESSION['flash'] = "Task removed.";
    }

    header("Location: user.php");
    exit();
}

// Flash message (shown once)
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// Fetch this user's tasks
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($tasks);
$completed = count(array_filter($tasks, fn($t) => $t['status'] === 'completed'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Tasks</title>
    <style>
        :root {
            --bg: #f3f4f8;
            --card: #ffffff;
            --text: #1f2430;
            --muted: #6b7280;
            --border: #e5e7eb;
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --success: #16a34a;
            --success-bg: #dcfce7;
            --warn: #b45309;
            --warn-bg: #fef3c7;
            --danger: #dc2626;
            --danger-bg: #fee2e2;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, -apple-system, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        /* Top bar */
        .navbar {
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            color: #fff;
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .navbar .brand { font-weight: 700; font-size: 18px; }
        .navbar nav a {
            color: #fff;
            text-decoration: none;
            margin-left: 8px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 14px;
        }
        .navbar nav a:hover { background: rgba(255,255,255,.18); }
        .navbar nav a.logout { background: rgba(255,255,255,.2); }

        .container { max-width: 900px; margin: 0 auto; padding: 28px 20px 50px; }
        h1 { margin: 0 0 4px; font-size: 28px; }
        .subtitle { color: var(--muted); margin: 0 0 22px; }

        /* Stats */
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 22px; }
        .stat {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            text-align: center;
        }
        .stat .num { font-size: 26px; font-weight: 700; color: var(--primary); }
        .stat .label { font-size: 13px; color: var(--muted); }

        /* Cards */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 22px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
        }
        .card h3 { margin: 0 0 16px; font-size: 18px; }

        .flash {
            background: var(--success-bg);
            color: var(--success);
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Form */
        label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 6px; }
        input[type="text"], textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font: inherit;
            margin-bottom: 16px;
            background: #fafafa;
        }
        input[type="text"]:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(79,70,229,.15);
        }
        textarea { resize: vertical; min-height: 80px; }

        .btn {
            border: none;
            cursor: pointer;
            font: inherit;
            font-weight: 600;
            border-radius: 10px;
            padding: 11px 22px;
            transition: background .15s, transform .05s;
        }
        .btn:active { transform: scale(.97); }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }

        /* Table */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th {
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--muted);
            padding: 10px 12px;
            border-bottom: 2px solid var(--border);
        }
        td { padding: 14px 12px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafbff; }

        .task-title { font-weight: 600; }
        .task-desc { color: var(--muted); font-size: 14px; margin-top: 3px; }
        .done .task-title, .done .task-desc { text-decoration: line-through; opacity: .6; }

        .badge {
            border: none;
            cursor: pointer;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            padding: 7px 14px;
            border-radius: 999px;
            white-space: nowrap;
        }
        .badge.pending { background: var(--warn-bg); color: var(--warn); }
        .badge.completed { background: var(--success-bg); color: var(--success); }
        .badge:hover { filter: brightness(.95); }

        .remove {
            background: var(--danger-bg);
            color: var(--danger);
            border: none;
            cursor: pointer;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            padding: 7px 14px;
            border-radius: 8px;
        }
        .remove:hover { background: #fecaca; }

        .empty { text-align: center; color: var(--muted); padding: 30px 10px; }

        @media (max-width: 560px) {
            .stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="navbar">
    <span class="brand">&#128203; TaskManager</span>
    <nav>
        <a href="user.php">My Tasks</a>
        <a href="logout.php" class="logout">Log Out</a>
    </nav>
</div>

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p class="subtitle">Manage your tasks and track your progress.</p>

    <?php if ($flash): ?>
        <div class="flash">&#10003; <?php echo htmlspecialchars($flash); ?></div>
    <?php endif; ?>

    <div class="stats">
        <div class="stat"><div class="num"><?php echo $total; ?></div><div class="label">Total Tasks</div></div>
        <div class="stat"><div class="num"><?php echo $total - $completed; ?></div><div class="label">Pending</div></div>
        <div class="stat"><div class="num"><?php echo $completed; ?></div><div class="label">Completed</div></div>
    </div>

    <div class="card">
        <h3>Add a New Task</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <label for="title">Task Title</label>
            <input type="text" id="title" name="title" placeholder="What do you need to do?" required>
            <label for="description">Description (Optional)</label>
            <textarea id="description" name="description" placeholder="Add more details..."></textarea>
            <button type="submit" class="btn btn-primary">Add Task</button>
        </form>
    </div>

    <div class="card">
        <h3>My Active Workspace</h3>
        <div class="table-wrap">
            <table>
                <tr>
                    <th>Status</th>
                    <th>Task Details</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($tasks as $t): ?>
                <?php $isDone = ($t['status'] === 'completed'); ?>
                <tr class="<?php echo $isDone ? 'done' : ''; ?>">
                    <td>
                        <form method="POST">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <button type="submit" class="badge <?php echo $isDone ? 'completed' : 'pending'; ?>">
                                <?php echo $isDone ? '&#10004; Completed' : '&#9203; Pending'; ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="task-title"><?php echo htmlspecialchars($t['title']); ?></div>
                        <div class="task-desc"><?php echo htmlspecialchars($t['description'] ?? ''); ?></div>
                    </td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Remove this task?');">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <button type="submit" class="remove">&#10006; Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$tasks): ?>
                <tr><td colspan="3" class="empty">No tasks yet. Add your first task above!</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>