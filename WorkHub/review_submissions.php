<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

// 🧹 Handle clearing all accepted submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_accepted'])) {
  $pdo->exec("DELETE FROM submissions WHERE status = 'accepted'");
}

// Handle filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'pending';

$sql = "
  SELECT s.id AS submission_id, s.file_path, s.status, s.submitted_at,
         t.title AS task_title, t.id AS task_id, t.due_date,
         u.name AS employee_name, u.id AS user_id
  FROM submissions s
  JOIN tasks t ON s.task_id = t.id
  JOIN users u ON s.user_id = u.id
";

$params = [];

if ($statusFilter !== 'all') {
  $sql .= " WHERE s.status = :status ";
  $params[':status'] = $statusFilter;
}

$sql .= " ORDER BY s.submitted_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$submissions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Review Submissions - WorkHub</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php include 'navbar.php'; ?>

  <div class="container">
    <div class="card">
      <h2>All Submissions</h2>

      <!-- Filter Dropdown -->
      <form method="get" style="margin-bottom: 1rem;">
        <label for="status">Filter by status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
          <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All</option>
          <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="accepted" <?= $statusFilter === 'accepted' ? 'selected' : '' ?>>Accepted</option>
          <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
      </form>

      <?php if (empty($submissions)): ?>
        <p>No submissions found.</p>
      <?php else: ?>
        <div class="table-container">
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
                <td><a href="<?= htmlspecialchars($s['file_path']) ?>" target="_blank">View</a></td>
                <td>
                  <form action="handle_review.php" method="post" style="display:inline;">
                    <input type="hidden" name="submission_id" value="<?= $s['submission_id'] ?>">
                    <button class="accept" name="action" value="accept">Accept</button>
                    <button class="reject" name="action" value="reject">Reject</button>
                  </form>
                  <form action="modify_submission.php" method="get" style="display:inline;">
                    <input type="hidden" name="submission_id" value="<?= $s['submission_id'] ?>">
                    <button class="modify" type="submit">Modify</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

      <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
  </div>

  <!-- 🔴 Clear Accepted Button at the Bottom -->
  <div class="container" style="text-align: center; margin-top: 2rem;">
    <form method="post" onsubmit="return confirm('Are you sure you want to delete ALL accepted submissions from the database?');">
      <button type="submit" name="clear_accepted"
        style="background-color: crimson; color: white; padding: 10px 20px; font-size: 16px; border: none; border-radius: 8px;">
        🧹 Delete All Accepted Submissions
      </button>
    </form>
  </div>

  <script src="js/theme-toggle.js" defer></script>
</body>
</html>
