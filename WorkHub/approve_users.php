<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Approve user
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?")->execute([$id]);
}

// Reject user
if (isset($_GET['reject'])) {
    $id = $_GET['reject'];
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
}

// Get pending users
$users = $pdo->query("SELECT * FROM users WHERE is_approved = 0")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Approve Users - WorkHub</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: background-color 0.3s, color 0.3s;
        }

        /* Light Theme */
        body.light {
            background-color: #f4f4f4;
            color: #000;
        }
        body.light .navbar {
            background-color: #e0e0e0;
        }
        body.light .card {
            background-color: #fff;
            color: #000;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
        body.light a {
            color: #007b7b;
        }
        body.light .btn {
            background-color: #007b7b;
            color: white;
        }

        /* Dark Theme */
        body.dark {
            background-color: #0b0b2d;
            color: #fff;
        }
        body.dark .navbar {
            background-color: #191946;
        }
        body.dark .card {
            background-color: #191946;
            color: #fff;
            box-shadow: 0 0 10px rgba(0,255,200,0.2);
        }
        body.dark a {
            color: #00ffcc;
        }
        body.dark .btn {
            background-color: #42766863;
            color: white;
        }
        body.dark .btn:hover {
            background-color: #00b386;
        }

        .navbar {
            padding: 1rem 2rem;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            margin: 0;
        }

        .theme-toggle {
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 5px;
            border: none;
        }

        .card {
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
            width: 90%;
            max-width: 800px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            font-weight: bold;
        }

        a {
            text-decoration: none;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 2px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>WorkHub</h1>
        <div>
            <!-- <button class="theme-toggle" onclick="toggleTheme()">🌗 Toggle Theme</button> -->
            <!-- <img src="profile.jpg" alt="Profile" style="width:30px; height:30px; border-radius:50%; margin-left:10px;"> -->
        </div>
    </div>

    <div class="card">
        <h2>Pending Users</h2>
        <?php if (count($users) === 0): ?>
            <p>No users pending approval.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <a class="btn" href="?approve=<?= $user['id'] ?>">✅ Approve</a>
                            <a class="btn" href="?reject=<?= $user['id'] ?>" onclick="return confirm('Reject user?')">❌ Reject</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        <p><a href="admin_dashboard.php">← Back to Dashboard</a></p>
    </div>

    <script>
        function toggleTheme() {
            const body = document.body;
            body.classList.toggle('light');
            body.classList.toggle('dark');

            // Save preference
            localStorage.setItem('theme', body.classList.contains('light') ? 'light' : 'dark');
        }

        // Load saved theme
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.body.classList.add(savedTheme);
        })();
    </script>
</body>
</html>
