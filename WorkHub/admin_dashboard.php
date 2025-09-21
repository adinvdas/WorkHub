<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>WorkHub Admin Dashboard</title>

  <!-- Set theme before page renders -->
  <script>
    (function () {
      const savedTheme = localStorage.getItem("theme") || "light";
      document.documentElement.setAttribute("data-theme", savedTheme);
    })();
  </script>

  <style>
    :root {
      --bg-color: #f5f5f5;
      --card-bg: #ffffff;
      --text-color: #222;
      --accent-color: #fca311ff;
      --muted-text: #555;
    }
    [data-theme="dark"] {
      --bg-color: #1a1a2e;
      --card-bg: #25274d;
      --text-color: #ffffff;
      --accent-color: #16c79a;
      --muted-text: #cccccc;
    }
    body {
      margin: 0;
      font-family: "Segoe UI", sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
      height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .navbar {
      background-color: var(--card-bg);
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 999;
    }
    .navbar-logo {
      font-size: 22px;
      font-weight: bold;
      color: var(--accent-color);
      text-decoration: none;
    }
    .logout-btn {
      color: white;
      background: crimson;
      padding: 8px 14px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: 0.2s;
      margin-right: 60px;
    }
    .logout-btn:hover {
      background: rgb(200, 0, 0);
    }
    .admin-dashboard {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding-top: 80px;
      text-align: center;
    }
    .admin-dashboard h1 {
      font-size: 28px;
      margin-bottom: 30px;
      color: var(--text-color);
    }
    .card-grid {
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: stretch;
      gap: 20px;
      padding: 0 20px;
      flex-wrap: nowrap;
    }
    .admin-card {
      background: var(--card-bg);
      padding: 25px;
      border-radius: 12px;
      text-align: left;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      text-decoration: none;
      color: var(--text-color);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      width: 220px;
    }
    .admin-card h2 {
      margin-top: 0;
      font-size: 20px;
    }
    .admin-card p {
      font-size: 15px;
      margin-top: 8px;
      color: var(--muted-text);
    }
    .card-grid .admin-card:hover {
      filter: none !important;
      transform: scale(1.02);
      z-index: 2;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    }
    #theme-toggle {
      background: transparent;
      border: none;
      width: 40px;
      padding: 6px 6px;
      border-radius: 10px;
      cursor: pointer;
      font-size: 20px;
      transition: background 0.3s ease;
      color: var(--accent-color);
    }
    #theme-toggle:hover {
      background: rgba(0, 0, 0, 0.05);
    }
  </style>
</head>

<body>
  <nav class="navbar">
    <div class="navbar-left">
      <a href="admin_dashboard.php" class="navbar-logo">WorkHub Admin</a>
    </div>
    <div class="navbar-right" style="display: flex; align-items: center; gap: 10px;">
      <button id="theme-toggle" title="Toggle Theme">🌙</button>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </nav>

  <main class="admin-dashboard">
    <h1>Welcome, Admin!</h1>
    <div class="card-grid">
      <a href="approve_users.php" class="admin-card">
        <h2>✅ Approve Users</h2>
        <p>View and approve new employee registrations.</p>
      </a>
      <a href="assign_task.php" class="admin-card">
        <h2>📝 Assign Tasks</h2>
        <p>Create tasks for employees to complete.</p>
      </a>
      <a href="review_submissions.php" class="admin-card">
        <h2>🔍 Review Submissions</h2>
        <p>Accept, reject, or request changes to work.</p>
      </a>
      <a href="manage_employees.php" class="admin-card">
        <h2>👥 Manage Employees</h2>
        <p>View and remove approved employees from the system.</p>
      </a>
    </div>

    <div class="card-grid" style="margin-top: 30px;">
      <a href="upload_company_files.php" class="admin-card">
        <h2>📁 Upload Files</h2>
        <p>Store and manage company-specific private files.</p>
      </a>
      <a href="view_uploaded_files.php" class="admin-card">
        <h2>📁 Stored Files</h2>
        <p>View uploaded files and control employee access.</p>
      </a>
    </div>
  </main>

  <script>
    const toggleBtn = document.getElementById("theme-toggle");

    function updateIcon() {
      toggleBtn.textContent =
        document.documentElement.getAttribute("data-theme") === "dark" ? "🌞" : "🌙";
    }

    updateIcon();

    toggleBtn.addEventListener("click", () => {
      const currentTheme = document.documentElement.getAttribute("data-theme");
      const newTheme = currentTheme === "dark" ? "light" : "dark";
      document.documentElement.setAttribute("data-theme", newTheme);
      localStorage.setItem("theme", newTheme);
      updateIcon();
    });
  </script>
</body>
</html>
