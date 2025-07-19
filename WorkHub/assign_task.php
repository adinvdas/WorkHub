<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'];
  $desc = $_POST['description'];
  $assigned_to = $_POST['assigned_to'];
  $due_date = $_POST['due_date'];

  $stmt = $pdo->prepare("INSERT INTO tasks (title, description, assigned_to, due_date) VALUES (?, ?, ?, ?)");
  $stmt->execute([$title, $desc, $assigned_to, $due_date]);
  $message = "Task assigned successfully!";
}

// Fetch all approved employees
$employees = $pdo->query("SELECT id, name FROM users WHERE role = 'employee' AND is_approved = 1")->fetchAll();
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Assign Task - WorkHub</title>
  <link rel="stylesheet" href="style.css">
  <script src="theme-toggle.js" defer></script>
</head>
<body>
  <div class="card">
    <h2>Assign Task</h2>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>
    <form method="POST">
      <label>Employee:</label>
      <select name="assigned_to" required>
        <option value="">-- Select Employee --</option>
        <?php foreach ($employees as $emp): ?>
          <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <input name="title" placeholder="Task Title" required>
      <textarea name="description" placeholder="Task Description" required></textarea>
      <label>Due Date:</label>
      <input type="date" name="due_date" required>
      <button type="submit">Assign Task</button>
    </form>

    <p><a href="admin_dashboard.php">⬅ Back to Dashboard</a></p>
  </div>
</body>
</html>
