<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

// Fetch all submissions with task and user info
$stmt = $pdo->query("
  SELECT s.id AS submission_id, s.file_path, s.status, s.submitted_at,
         t.title AS task_title, t.id AS task_id, t.due_date,
         u.name AS employee_name, u.id AS user_id
  FROM submissions s
  JOIN tasks t ON s.task_id = t.id
  JOIN users u ON s.user_id = u.id
  ORDER BY s.submitted_at DESC
");

$submissions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Review Submissions - WorkHub</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="card">
    <h2>All Submissions</h2>

    <?php if (empty($submissions)): ?>
      <p>No submissions found.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Employee</th>
            <th>Task</th>
            <th>Submitted At</th>
            <th>Status</th>
            <th>File</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($submissions as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['employee_name']) ?></td>
            <td><?= htmlspecialchars($s['task_title']) ?></td>
            <td><?= $s['submitted_at'] ?></td>
            <td><?= $s['status'] ?></td>
            <td><a href="<?= $s['file_path'] ?>" target="_blank">View</a></td>
            <td>
              <form action="handle_review.php" method="post" style="display:inline;">
                <input type="hidden" name="submission_id" value="<?= $s['submission_id'] ?>">
                <button name="action" value="accept">Accept</button>
                <button name="action" value="reject">Reject</button>
              </form>
              <form action="modify_submission.php" method="get" style="display:inline;">
                <input type="hidden" name="submission_id" value="<?= $s['submission_id'] ?>">
                <button type="submit">Modify</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
