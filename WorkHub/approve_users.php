<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

if (isset($_GET['approve'])) {
  $id = $_GET['approve'];
  $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?")->execute([$id]);
}

if (isset($_GET['reject'])) {
  $id = $_GET['reject'];
  $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
}

$users = $pdo->query("SELECT * FROM users WHERE is_approved = 0")->fetchAll();
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Approve Users - WorkHub</title>
  <link rel="stylesheet" href="style.css">
  <script src="theme-toggle.js" defer></script>
</head>
<body>
  <div class="card">
    <h2>Pending Users</h2>
    <?php if (count($users) === 0): ?>
      <p>No users pending approval.</p>
    <?php else: ?>
      <table>
        <tr>
          <th>Name</th><th>Email</th><th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
              <a href="?approve=<?= $user['id'] ?>">✅ Approve</a> | 
              <a href="?reject=<?= $user['id'] ?>" onclick="return confirm('Reject user?')">❌ Reject</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
    <p><a href="admin_dashboard.php">⬅ Back to Dashboard</a></p>
  </div>
</body>
</html>
