<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM files f
  JOIN file_access fa ON f.id = fa.file_id
  WHERE fa.employee_id = ?");
$stmt->execute([$user_id]);
$files = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Permitted Files - WorkHub</title>
  <link rel="stylesheet" href="style.css">
  <script src="theme-toggle.js" defer></script>
  <style>
    .file-card {
      background-color: #191946;
      padding: 1rem 2rem;
      border-radius: 10px;
      margin: 1rem auto;
      width: 90%;
      max-width: 600px;
      color: #fff;
      box-shadow: 0 0 10px rgba(0,255,200,0.2);
    }
    .file-card a {
      color: #00ffcc;
    }
  </style>
</head>
<body>
  <div class="navbar">
    <a href="employee_dashboard.php" class="navbar-logo" style="color :white">WorkHub</a>
    <div>
      <button id="theme-toggle">🌓</button>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </div>

  <div class="admin-dashboard">
    <h1>Files Shared With You</h1>
    <?php if (empty($files)): ?>
      <p>No files assigned to you yet.</p>
    <?php else: ?>
      <?php foreach ($files as $file): ?>
        <div class="file-card">
          <h3><?= htmlspecialchars($file['title']) ?></h3>
          <p><?= htmlspecialchars($file['description']) ?></p>
          <a href="<?= htmlspecialchars($file['file_path']) ?>" download>Download File</a>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>
