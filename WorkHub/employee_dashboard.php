<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
  header("Location: login.php");
  exit;
}

require_once "db.php";

// Fetch employee name based on ID
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$name = $user ? htmlspecialchars($user['name']) : 'Employee';
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Employee Dashboard - WorkHub</title>
  <link rel="stylesheet" href="style.css">
  <script src="theme-toggle.js" defer></script>
  <style>
    .dashboard-menu {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 70vh;
      gap: 2rem;
    }
    .menu-card {
      background-color: #191946;
      padding: 3rem 4rem;
      border-radius: 20px;
      box-shadow: 0 0 15px rgba(0,255,200,0.2);
      text-align: center;
      color: white;
      text-decoration: none;
      transition: transform 0.2s;
    }
    .menu-card:hover {
      transform: scale(1.05);
      background-color: #0b0b2d;
    }
    .welcome-message {
      text-align: center;
      color: #ffffffff;
    }
  </style>
</head>
<body>
  <div class="navbar">
    <a href="#" class="navbar-logo" style="color :white">WorkHub</a>
    <div class="welcome-message" >
    <h4>👋 Welcome, <?php echo $name; ?></h4>
  </div>
    <div>
      <button id="theme-toggle">🌓</button>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    
  </div>

  

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
</body>
</html>
