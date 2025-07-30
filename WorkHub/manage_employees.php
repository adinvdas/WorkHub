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
<html data-theme="light">
<head>
  <meta charset="UTF-8" />
  <title>Manage Employees - WorkHub</title>
  <link rel="stylesheet" href="style.css" />
  <script src="theme-toggle.js" defer></script>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-left">
      <a href="admin_dashboard.php" class="navbar-logo">WorkHub Admin</a>
    </div>
    <div class="navbar-right" style="display: flex; align-items: center; gap: 10px;">
      <button id="theme-toggle" title="Toggle Theme">🌙</button>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </nav>

  <!-- Content -->
  <main class="admin-dashboard">
    <h1>👥 Approved Employees</h1>
    <?php if (count($data) === 0): ?>
      <p>No approved employees found.</p>
    <?php else: ?>
      <table class="employee-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <!-- <th>Username</th> -->
            <th>Action</th>
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
    <p><a href="admin_dashboard.php">← Back to Dashboard</a></p>
  </main>
  <script>
        const toggleBtn = document.getElementById("theme-toggle");
        const currentTheme = localStorage.getItem("theme");

        if (currentTheme === "dark") {
            document.documentElement.setAttribute("data-theme", "dark");
            toggleBtn.textContent = "🌞";
        }

        toggleBtn.addEventListener("click", () => {
            const theme = document.documentElement.getAttribute("data-theme");
            if (theme === "dark") {
                document.documentElement.removeAttribute("data-theme");
                localStorage.setItem("theme", "light");
                toggleBtn.textContent = "🌙";
            } else {
                document.documentElement.setAttribute("data-theme", "dark");
                localStorage.setItem("theme", "dark");
                toggleBtn.textContent = "🌞";
            }
        });
    </script>
</body>
</html>
