<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
  <meta charset="UTF-8" />
  <title>WorkHub Admin Dashboard</title>
  <link rel="stylesheet" href="style.css">
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


  <!-- Main dashboard -->
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
    </div>
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
