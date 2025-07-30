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
<html data-theme="light">
<head>
  <title>Employee Dashboard - WorkHub</title>
  <link rel="stylesheet" href="style.css">
  <script src="theme-toggle.js" defer></script>
</head>
<body>
  <div class="navbar">
    <a href="employee_dashboard.php" class="navbar-logo" style="color: white">WorkHub</a>
    <div>
      <button id="theme-toggle">🌓</button>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </div>

  <div class="admin-dashboard">
    <h1>Employee Task Dashboard</h1>

    <form method="GET" style="margin-bottom: 2em;">
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
      <p>No tasks assigned yet.</p>
    <?php else: ?>
      <div class="card-grid">
        <?php foreach ($tasks as $task): ?>
          <div class="admin-card">
            <h2><?= htmlspecialchars($task['title']) ?></h2>
            <p><strong>Due:</strong> <?= htmlspecialchars($task['due_date']) ?></p>
            <p><strong>Status:</strong> <?= isset($task['status']) ? ucfirst($task['status']) : 'Not Submitted' ?></p>

            <?php
            if (!empty($task['file_path']) && file_exists($task['file_path'])):
              $filename = basename($task['file_path']);
            ?>
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