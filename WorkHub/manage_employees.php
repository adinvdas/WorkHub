<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
  $stmt->execute([$id]);
}

$employees = $pdo->prepare("SELECT * FROM users WHERE is_approved = 1 AND role = 'employee'");
$employees->execute();
$data = $employees->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>Manage Employees - WorkHub</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    /* General Layout */
    body {
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      background: var(--bg, #f4f6fa);
      color: var(--text, #222);
    }

    [data-theme="dark"] body {
      --bg: #121212;
      --text: #eee;
    }

    /* Navbar */
    .navbar {
      background: #191946;
      color: white;
      display: flex;
      align-items: center;
      padding: 1rem 2rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }
    .navbar-logo {
      font-size: 1.3rem;
      font-weight: bold;
      color: white;
      text-decoration: none;
      letter-spacing: 0.5px;
    }

    /* Dashboard Card */
    .admin-dashboard {
      max-width: 950px;
      margin: 2.5rem auto;
      padding: 2rem 2.5rem;
      background: white;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }
    [data-theme="dark"] .admin-dashboard {
      background: #1e1e2f;
    }
    .admin-dashboard h1 {
      text-align: center;
      margin-bottom: 2rem;
      color: #191946;
      font-size: 1.8rem;
    }
    [data-theme="dark"] .admin-dashboard h1 {
      color: #fff;
    }

    /* Table */
    .employee-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 1.5rem;
      border-radius: 8px;
      overflow: hidden;
    }
    .employee-table th,
    .employee-table td {
      padding: 14px 18px;
      text-align: left;
      font-size: 0.95rem;
    }
    .employee-table th {
      background: #191946;
      color: white;
      font-weight: 600;
    }
    .employee-table tr:nth-child(even) {
      background: #f9f9f9;
    }
    [data-theme="dark"] .employee-table tr:nth-child(even) {
      background: #2a2a3d;
    }
    .employee-table tr:hover {
      background: rgba(25, 25, 70, 0.06);
    }
    [data-theme="dark"] .employee-table tr:hover {
      background: rgba(0,255,200,0.08);
    }

    /* Buttons */
    .btn {
      padding: 8px 14px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 500;
      font-size: 0.9rem;
      transition: 0.3s;
      display: inline-block;
    }
    .btn.danger {
      background: #e74c3c;
      color: white;
    }
    .btn.danger:hover {
      background: #c0392b;
    }

    /* Back link */
    .back-link {
      display: inline-block;
      margin-top: 1rem;
      color: #191946;
      text-decoration: none;
      font-weight: 500;
    }
    .back-link:hover {
      text-decoration: underline;
    }
    [data-theme="dark"] .back-link {
      color: #00ffcc;
    }

    /* Empty message */
    .empty-msg {
      text-align: center;
      color: gray;
      font-style: italic;
      margin: 2rem 0;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <a href="admin_dashboard.php" class="navbar-logo">⚙️ WorkHub Admin</a>
  </nav>

  <!-- Content -->
  <main class="admin-dashboard">
    <h1>👥 Approved Employees</h1>
    <?php if (count($data) === 0): ?>
      <p class="empty-msg">No approved employees found.</p>
    <?php else: ?>
      <table class="employee-table">
        <thead>
          <tr>
            <th>👤 Name</th>
            <th>📧 Email</th>
            <th>⚡ Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $emp): ?>
          <tr>
            <td><?= htmlspecialchars($emp['name']) ?></td>
            <td><?= htmlspecialchars($emp['email']) ?></td>
            <td>
              <a class="btn danger" href="?delete=<?= $emp['id'] ?>" onclick="return confirm('Are you sure you want to delete this employee?');">❌ Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
  </main>

  <!-- Sync theme with dashboard -->
  <script>
    const currentTheme = localStorage.getItem("theme");
    if (currentTheme === "dark") {
      document.documentElement.setAttribute("data-theme", "dark");
    } else {
      document.documentElement.removeAttribute("data-theme");
    }
  </script>
</body>
</html>
