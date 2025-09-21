<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['submission_id'])) {
    die('No submission selected.');
}

$submission_id = $_GET['submission_id'];

// Fetch task and submission details
$stmt = $pdo->prepare("
  SELECT s.id, s.task_id, s.user_id, t.title, s.file_path 
  FROM submissions s 
  JOIN tasks t ON s.task_id = t.id 
  WHERE s.id = ?
");
$stmt->execute([$submission_id]);
$submission = $stmt->fetch();

if (!$submission) {
    die('Submission not found.');
}

// Theme sync
$theme = $_SESSION['theme'] ?? 'dark'; // default dark
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($theme) ?>">
<head>
    <meta charset="UTF-8">
    <title>Modify Submission - WorkHub</title>
    <style>
        /* === MAIN THEME VARIABLES === */
        :root {
            --bg-color: #f8fafc;
            --card-color: #ffffff;
            --text-color: #0f172a;
            --accent-color: #1dbf73;
            --muted-text: #555;
        }

        :root[data-theme='dark'] {
            --bg-color: #0f172a;
            --card-color: #1e293b;
            --text-color: #e2e8f0;
            --accent-color: #1dbf73;
            --muted-text: #cccccc;
        }

        :root[data-theme='light'] {
            --bg-color: #f8fafc;
            --card-color: #ffffff;
            --text-color: #0f172a;
            --accent-color: #fca311;
            --muted-text: #555;
        }

        /* Global */
        body {
            font-family: Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        header {
            background-color: var(--card-color);
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            border-bottom: 2px solid #ccc;
        }

        header h1 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
        }

        .theme-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.3s ease;
        }

        .theme-toggle:hover {
            opacity: 0.7;
        }

        .card {
            max-width: 700px;
            margin: 40px auto;
            background: var(--card-color);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: var(--accent-color);
        }

        label {
            font-weight: bold;
            margin-bottom: 6px;
            display: block;
        }

        input[type="date"], textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: var(--bg-color);
            color: var(--text-color);
            margin-bottom: 20px;
        }

        textarea {
            resize: vertical;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            background: var(--accent-color);
            color: #fff;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            filter: brightness(0.9);
        }

        a {
            color: var(--accent-color);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function toggleTheme() {
            const root = document.documentElement;
            const btn = document.getElementById("themeBtn");
            const currentTheme = root.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            root.setAttribute('data-theme', newTheme);
            btn.textContent = newTheme === 'light' ? "☀️" : "🌙";

            // save theme in session (via fetch call)
            fetch("save_theme.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "theme=" + newTheme
            });
        }
    </script>
</head>
<body>
<header>
    <h1>WorkHub</h1>
    <div class="header-right">
        <button id="themeBtn" class="theme-toggle" onclick="toggleTheme()">
            <?= $theme === 'light' ? "☀️" : "🌙" ?>
        </button>
    </div>
</header>

<div class="card">
    <h2>Modify Request for: <?= htmlspecialchars($submission['title']) ?></h2>

    <p>File submitted: 
        <a href="<?= htmlspecialchars($submission['file_path']) ?>" target="_blank">View File</a>
    </p>

    <form action="handle_modify.php" method="POST">
        <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">

        <label for="comment">Comment (what needs to be changed):</label>
        <textarea name="comment" id="comment" rows="5" required></textarea>

        <label for="new_due_date">New Due Date (optional):</label>
        <input type="date" name="new_due_date" id="new_due_date">

        <button type="submit">✏️ Submit Modification Request</button>
    </form>
</div>
</body>
</html>
