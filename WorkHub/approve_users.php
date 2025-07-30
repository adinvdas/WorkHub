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
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #0b0b2d;
      color: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .navbar {
      background-color: #191946;
      padding: 1rem 2rem;
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .navbar h1 {
      margin: 0;
      color: #00ffcc;
    }

    .card {
      background-color: #191946;
      padding: 2rem;
      border-radius: 15px;
      margin-top: 2rem;
      width: 90%;
      max-width: 800px;
      box-shadow: 0 0 10px rgba(0,255,200,0.2);
    }

    h2 {
      color: #ffffff;
      text-align: center;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    th, td {
      padding: 10px;
      text-align: center;
      color: #fff;
    }

    th {
      color: #ffffff;
      font-weight: bold;
    }

    a {
      text-decoration: none;
      color: #00ffcc;
    }

    .btn {
      padding: 6px 12px;
      background-color: #42766863;
      border: none;
      border-radius: 5px;
      color: white;
      cursor: pointer;
      margin: 2px;
      font-size: 0.9rem;
    }

    .btn:hover {
      background-color: #00b386;
    }

    p a {
      color: #00ffcc;
    }
  </style>
</head>
<body>
  <div class="navbar">
    <h1>WorkHub</h1>
    <div>
      <img src="profile.jpg" alt="Profile" style="width:30px; height:30px; border-radius:50%;">
    </div>
  </div>

  <div class="card">
    <h2>Pending Users</h2>
    <?php if (count($users) === 0): ?>
      <p>No users pending approval.</p>
    <?php else: ?>
      <table>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
              <a class="btn" href="?approve=<?= $user['id'] ?>">✅ Approve</a>
              <a class="btn" href="?reject=<?= $user['id'] ?>" onclick="return confirm('Reject user?')">❌ Reject</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
    <p><a href="admin_dashboard.php">← Back to Dashboard</a></p>
  </div>
</body>
</html>
