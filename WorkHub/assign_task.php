<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $assigned_to = $_POST['assigned_to'];
    $due_date = $_POST['due_date'];

    $file_path = null;
    if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = basename($_FILES['project_file']['name']);
        $file_path = $upload_dir . time() . "_" . $filename;
        move_uploaded_file($_FILES['project_file']['tmp_name'], $file_path);
    }

    $stmt = $pdo->prepare("INSERT INTO tasks (title, description, assigned_to, due_date, file_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $desc, $assigned_to, $due_date, $file_path]);
    $message = "✅ Task assigned successfully!";
}

$employees = $pdo->query("SELECT id, name FROM users WHERE role = 'employee' AND is_approved = 1 ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assign Task - WorkHub</title>
<style>
    body {
        margin: 0;
        font-family: "Segoe UI", sans-serif;
        transition: background-color 0.3s, color 0.3s;
    }

    /* Dark theme */
    body.dark {
        background-color: #0f1026;
        color: white;
    }
    body.dark header {
        background-color: #181a3a;
    }
    body.dark .card {
        background-color: #181a3a;
        color: white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }
    body.dark select, 
    body.dark input, 
    body.dark textarea {
        background-color: #0f1026;
        color: white;
    }
    body.dark button {
        background-color: #1dbf73;
        color: white;
    }
    body.dark button:hover {
        background-color: #17a765;
    }

    /* Light theme */
    body.light {
        background-color: #f4f4f4;
        color: #000;
    }
    body.light header {
        background-color: #e0e0e0;
    }
    body.light .card {
        background-color: #fff;
        color: #000;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    body.light select, 
    body.light input, 
    body.light textarea {
        background-color: #fff;
        color: #000;
        border: 1px solid #ccc;
    }
    body.light button {
        background-color: #007b7b;
        color: white;
    }
    body.light button:hover {
        background-color: #005f5f;
    }

    header {
        padding: 15px 30px;
        font-size: 1.6rem;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .theme-toggle {
        cursor: pointer;
        padding: 6px 10px;
        border-radius: 5px;
        border: none;
        font-size: 0.9rem;
    }

    .container {
        display: flex;
        justify-content: center;
        padding: 30px;
    }

    .card {
        padding: 30px 40px;
        border-radius: 15px;
        width: 500px;
        animation: fadeIn 0.5s ease-in-out;
    }

    h2 {
        font-size: 1.8rem;
        margin-bottom: 20px;
        color: #ff4f87;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    label {
        display: block;
        margin-top: 15px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    select, input, textarea {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 8px;
        margin-bottom: 10px;
        font-size: 1rem;
    }

    textarea {
        resize: vertical;
        min-height: 80px;
    }

    input[type="file"] {
        padding: 5px;
    }

    button {
        padding: 12px;
        border: none;
        border-radius: 8px;
        width: 100%;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 15px;
        transition: background 0.3s ease;
    }

    .back-link {
        display: inline-block;
        margin-top: 15px;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>
<body>
<header>
    WorkHub
    <!-- <button class="theme-toggle" onclick="toggleTheme()">🌗 Toggle Theme</button> -->
</header>

<div class="container">
    <div class="card">
        <h2>📌 Assign New Task</h2>
        
        <?php if (!empty($message)) echo "<p>$message</p>"; ?>
        
        <form action="assign_task.php" method="post" enctype="multipart/form-data">
            <label for="assigned_to">Select Employee:</label>
            <select id="assigned_to" name="assigned_to" required>
                <option value="">-- Select Employee --</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="title">Task Title:</label>
            <input type="text" id="title" name="title" placeholder="Enter task title" required>

            <label for="description">Task Description:</label>
            <textarea id="description" name="description" placeholder="Enter task details..." required></textarea>

            <label for="due_date">Due Date:</label>
            <input type="date" id="due_date" name="due_date" required>

            <label for="project_file">Attach File (optional):</label>
            <input type="file" id="project_file" name="project_file" accept=".zip,.pdf,.doc,.docx,.png,.jpg,.jpeg,.txt">

            <button type="submit">✅ Assign Task</button>
        </form>

        <a href="admin_dashboard.php" class="back-link">⬅ Back to Dashboard</a>
    </div>
</div>

<script>
    function toggleTheme() {
        const body = document.body;
        body.classList.toggle('light');
        body.classList.toggle('dark');
        localStorage.setItem('theme', body.classList.contains('light') ? 'light' : 'dark');
    }

    (function() {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.body.classList.add(savedTheme);
    })();
</script>
</body>
</html>
