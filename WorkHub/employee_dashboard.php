<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
  header("Location: login.php");
  exit;
}

require_once "db.php";

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$name = $user ? htmlspecialchars($user['name']) : 'Employee';
?>
<!DOCTYPE html>
<html>
<head>
  <title>Employee Dashboard - WorkHub</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #0f172a;
      color: #e2e8f0;
      transition: background-color 0.3s, color 0.3s;
    }

    /* Light Theme */
    body.light {
      background-color: #f4f7fb;
      color: #1f2937;
    }
    body.light .navbar {
      background: #ffffff;
      border-bottom: 1px solid #e5e7eb;
    }
    body.light .navbar-logo {
      color: #f59e0b;
    }
    body.light .welcome-message h4 {
      color: #374151;
    }
    body.light .menu-card {
      background-color: #ffffff;
      color: #1f2937;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    }
    body.light .menu-card:hover {
      background-color: #f9fafb;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }
    body.light #theme-toggle {
      color: #f59e0b;
    }
    body.light .logout-btn {
      background: #ef4444;
      color: white;
    }

    /* Navbar */
    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #1e293b;
      padding: 0.8rem 2rem;
      box-shadow: 0 0 15px rgba(0,0,0,0.3);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    .navbar-logo {
      font-size: 1.4rem;
      font-weight: bold;
      color: #fbbf24;
      text-decoration: none;
    }
    .welcome-message h4 {
      margin: 0;
      font-size: 1rem;
      color: #93c5fd;
    }

    /* Right Controls */
    .navbar-controls {
      display: flex;
      align-items: center;
      gap: 0.8rem;
    }

    /* Theme toggle */
    #theme-toggle {
      background: transparent;
      border: none;
      font-size: 1.3rem;
      cursor: pointer;
      color: #facc15;
      border-radius: 50%;
      padding: 0.3rem;
      transition: background 0.2s ease, transform 0.2s ease;
    }
    #theme-toggle:hover {
      background: rgba(255, 255, 255, 0.1);
      transform: rotate(15deg);
    }

    /* Logout Button */
    .logout-btn {
      background: #ef4444;
      color: white;
      padding: 0.4rem 0.9rem;
      border-radius: 20px;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
      transition: background 0.3s ease;
    }
    .logout-btn:hover {
      background: #dc2626;
    }

    /* Dashboard Container */
    .dashboard-container {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: calc(100vh - 70px);
      text-align: center;
    }

    /* Dashboard Title */
    .dashboard-title {
      font-size: 1.8rem;
      font-weight: 600;
      margin-bottom: 2rem;
      color: #facc15;
    }
    body.light .dashboard-title {
      color: #374151;
    }

    /* Dashboard Menu */
    .dashboard-menu {
      display: flex;
      justify-content: center;
      gap: 2rem;
      flex-wrap: wrap;
    }

    /* Cards */
    .menu-card {
      background-color: #191946;
      padding: 2rem;
      border-radius: 14px;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.3);
      width: 260px;
      transition: all 0.3s ease;
      text-decoration: none;
      color: inherit;
    }
    .menu-card:hover {
      transform: translateY(-8px);
      background-color: #0b0b2d;
      box-shadow: 0 6px 20px rgba(0, 255, 200, 0.3);
    }
    .menu-card h2 {
      font-size: 1.4rem;
      margin-bottom: 0.5rem;
    }
    .menu-card p {
      font-size: 0.95rem;
      opacity: 0.85;
    }
    body.light .menu-card p {
      color: #6b7280;
    }
  </style>
</head>
<body>
  <div class="navbar">
    <a href="#" class="navbar-logo">WorkHub</a>
    <div class="welcome-message">
      <h4>👋 Welcome, <?php echo $name; ?></h4>
    </div>
    <div class="navbar-controls">
      <button id="theme-toggle">🌓</button>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </div>

  <div class="dashboard-container">
    <h1 class="dashboard-title">Your Dashboard</h1>
    <div class="dashboard-menu">
      <a href="employee_tasks.php" class="menu-card">
        <h2>📝 Tasks</h2>
        <p>View and submit your assigned tasks</p>
      </a>
      <a href="employee_files.php" class="menu-card">
        <h2>📁 Permitted Files</h2>
        <p>Access files shared by the company</p>
      </a>
    </div>
  </div>

  <script>
    const themeToggle = document.getElementById('theme-toggle');
    themeToggle.addEventListener('click', () => {
      document.body.classList.toggle('light');
      localStorage.setItem('theme', document.body.classList.contains('light') ? 'light' : 'dark');
    });

    if (localStorage.getItem('theme') === 'light') {
      document.body.classList.add('light');
    }
  </script>
</body>
</html>
