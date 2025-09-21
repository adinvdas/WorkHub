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
    /* ========== RESET ========== */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: system-ui, sans-serif;
    }
    body {
      background: #f4f6fa;
      color: #222;
      line-height: 1.6;
      min-height: 100vh;
    }
    [data-theme="dark"] body {
      background: #0f0f23;
      color: #eaeaea;
    }

    /* ========== NAVBAR ========== */
    .navbar {
      background: #191946;
      color: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.8rem 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.25);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .navbar-logo {
      font-size: 1.5rem;
      font-weight: 700;
      text-decoration: none;
      color: #fff;
    }
    .navbar-actions {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .navbar button, 
    .navbar .logout-btn {
      background: none;
      border: none;
      cursor: pointer;
      font-size: 1rem;
      color: #fff;
      text-decoration: none;
      transition: color 0.3s;
    }
    .navbar button:hover,
    .navbar .logout-btn:hover {
      color: #00ffcc;
    }

    /* ========== MAIN LAYOUT ========== */
    .admin-dashboard {
      max-width: 900px;
      margin: 2rem auto;
      padding: 1rem;
      text-align: center;
    }
    .admin-dashboard h1 {
      font-size: 2rem;
      margin-bottom: 1.5rem;
      color: #191946;
    }
    [data-theme="dark"] .admin-dashboard h1 {
      color: #fff;
    }

    /* ========== FILE CARDS ========== */
    .file-card {
      background: #191946;
      color: #fff;
      padding: 1.5rem 2rem;
      border-radius: 12px;
      margin: 1rem auto;
      width: 100%;
      max-width: 600px;
      box-shadow: 0 4px 15px rgba(0,255,200,0.2);
      text-align: left;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    [data-theme="dark"] .file-card {
      background: #22224d;
    }
    .file-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 6px 18px rgba(0,255,200,0.35);
    }
    .file-card h3 {
      margin-bottom: 0.5rem;
      font-size: 1.3rem;
    }
    .file-card p {
      margin-bottom: 1rem;
      font-size: 0.95rem;
      color: #e0e0e0;
    }
    .file-card a {
      display: inline-block;
      background: #00ffcc;
      color: #191946;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: background 0.3s ease;
    }
    .file-card a:hover {
      background: #00e6b8;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 600px) {
      .navbar {
        flex-direction: column;
        gap: 0.5rem;
      }
      .navbar-actions {
        gap: 0.5rem;
      }
      .file-card {
        padding: 1rem;
      }
    }
  </style>
</head>
<body>
  <div class="navbar">
    <a href="employee_dashboard.php" class="navbar-logo">WorkHub</a>
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
