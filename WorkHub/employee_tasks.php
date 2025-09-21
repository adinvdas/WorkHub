<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$filter = $_GET['status'] ?? 'all';

$sql = "
  SELECT t.id, t.title, t.description, t.due_date, t.file_path,
         s.status, s.file_path AS submission_file, s.submitted_at, 
         s.comment_text, s.new_due_date
  FROM tasks t
  LEFT JOIN submissions s ON t.id = s.task_id AND s.user_id = ?
  WHERE t.assigned_to = ?
";

$params = [$user_id, $user_id];
if ($filter !== 'all') {
  $sql .= " AND (s.status = ? OR (s.status IS NULL AND ? = 'Not Submitted'))";
  $params[] = $filter;
  $params[] = $filter;
}

$sql .= " ORDER BY t.due_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Employee Dashboard - WorkHub</title>
  <style>
/* 🌞 Light Theme */
html[data-theme="light"] {
  --bg-color: #f9fafb;
  --text-color: #374151;
  --card-bg: #ffffff;
  --card-border: #e5e7eb;
  --primary-color: #111827;
  --primary-hover: #2563eb;
  --navbar-bg: #ffffff;
  --navbar-text: #111827;
  --button-bg: #2563eb;
  --button-text: #ffffff;
  --button-hover: #1e40af;
}

/* 🌙 Dark Theme */
html[data-theme="dark"] {
  --bg-color: #111827;
  --text-color: #e5e7eb;
  --card-bg: #1f2937;
  --card-border: #374151;
  --primary-color: #f9fafb;
  --primary-hover: #3b82f6;
  --navbar-bg: #1f2937;
  --navbar-text: #f9fafb;
  --button-bg: #3b82f6;
  --button-text: #ffffff;
  --button-hover: #2563eb;
}

/* Base */
body {
  background-color: var(--bg-color);
  color: var(--text-color);
  font-family: 'Segoe UI', Tahoma, sans-serif;
  margin: 0;
  padding: 0;
  line-height: 1.6;
  transition: background 0.3s ease, color 0.3s ease;
}

/* Navbar */
.navbar {
  background-color: var(--navbar-bg);
  color: var(--navbar-text);
  display: flex;
  justify-content: flex-start;
  align-items: center;
  padding: 1em 2em;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.navbar-logo {
  font-weight: 700;
  font-size: 1.5rem;
  color: #f59e0b !important;
  letter-spacing: 0.5px;
  text-decoration: none;
}

/* Dashboard */
.admin-dashboard {
  max-width: 1200px;
  margin: 2em auto;
  padding: 0 1em;
}

.admin-dashboard h1 {
  font-size: 2.2rem;
  margin-bottom: 1em;
  text-align: center;
  color: var(--primary-color);
}

/* Filter Dropdown */
form {
  text-align: center;
  margin-bottom: 1.5em;
}

form label {
  font-weight: 600;
}

form select {
  margin-left: 0.5em;
  padding: 0.5em 0.8em;
  border-radius: 8px;
  border: 1px solid var(--card-border);
  background: var(--card-bg);
  color: var(--text-color);
  font-weight: 500;
  transition: border 0.2s, background 0.2s;
}

form select:hover {
  border-color: var(--primary-hover);
}

/* Card Grid */
.card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 2em;
  margin-top: 1.5em;
}

/* Task Cards */
.admin-card {
  background-color: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 16px;
  padding: 1.5em;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s, box-shadow 0.2s;
}

.admin-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
}

.admin-card h2 {
  font-size: 1.3rem;
  margin-bottom: 0.6em;
  color: var(--primary-color);
}

.admin-card p {
  font-size: 0.95rem;
  margin: 0.4em 0;
  color: var(--text-color);
}

.admin-card a {
  display: inline-block;
  margin-top: 0.8em;
  padding: 0.5em 1.2em;
  background: var(--button-bg);
  color: #fff;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 500;
  text-decoration: none;
  transition: background 0.2s, transform 0.2s;
}

.admin-card a:hover {
  background: var(--button-hover);
  transform: scale(1.05);
}
  </style>

  <!-- 🌗 Apply saved theme -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const savedTheme = localStorage.getItem("theme") || "light";
      document.documentElement.setAttribute("data-theme", savedTheme);
    });
  </script>
</head>
<body>
  <!-- Navbar -->
  <div class="navbar">
    <a href="employee_dashboard.php" class="navbar-logo">WorkHub</a>
  </div>

  <!-- Dashboard -->
  <div class="admin-dashboard">
    <h1>Employee Task Dashboard</h1>

    <form method="GET">
      <label for="status">Filter by status: </label>
      <select name="status" id="status" onchange="this.form.submit()">
        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
        <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="accepted" <?= $filter === 'accepted' ? 'selected' : '' ?>>Accepted</option>
        <option value="rejected" <?= $filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        <option value="modify" <?= $filter === 'modify' ? 'selected' : '' ?>>Modify</option>
        <option value="Not Submitted" <?= $filter === 'Not Submitted' ? 'selected' : '' ?>>Not Submitted</option>
      </select>
    </form>

    <?php if (empty($tasks)): ?>
      <p style="text-align:center; margin-top:2em;">No tasks assigned yet.</p>
    <?php else: ?>
      <div class="card-grid">
        <?php foreach ($tasks as $task): ?>
          <div class="admin-card">
            <h2><?= htmlspecialchars($task['title']) ?></h2>
            <p><strong>Due:</strong> <?= htmlspecialchars($task['due_date']) ?></p>
            <p><strong>Status:</strong> <?= isset($task['status']) ? ucfirst($task['status']) : 'Not Submitted' ?></p>

            <?php if (!empty($task['description'])): ?>
              <p><strong>Admin Feedback:</strong> <?= htmlspecialchars($task['description']) ?></p>
            <?php endif; ?>

            <?php if (!empty($task['file_path']) && file_exists($task['file_path'])):
              $filename = basename($task['file_path']); ?>
              <p><strong>Admin File:</strong> 
                <a href="<?= htmlspecialchars($task['file_path']) ?>" download="<?= $filename ?>" target="_blank">
                  Download
                </a>
              </p>
            <?php endif; ?>

            <?php if (!isset($task['status'])): ?>
              <a href="submit_task.php?task_id=<?= $task['id'] ?>">Submit</a>
            <?php else: ?>
              <a href="submit_task.php?task_id=<?= $task['id'] ?>">Update</a>
              <?php if ($task['status'] === 'modify'): ?>
                <p><strong>Feedback:</strong> <?= htmlspecialchars($task['comment_text'] ?? 'No comment') ?></p>
                <?php if (!empty($task['new_due_date'])): ?>
                  <p><strong>New Due:</strong> <?= htmlspecialchars($task['new_due_date']) ?></p>
                <?php endif; ?>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
