<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password'])) {
    if ($user['is_approved']) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['role'] = $user['role'];
      header("Location: " . ($user['role'] === 'admin' ? "admin_dashboard.php" : "employee_dashboard.php"));
      exit;
    } else {
      $message = "Account not approved yet.";
    }
  } else {
    $message = "Invalid credentials.";
  }
}
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Login - WorkHub</title>
  <link rel="stylesheet" href="style.css">
  <script src="theme-toggle.js" defer></script>
</head>
<body>
  <div class="card">
    <h2>Login</h2>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>
    <form method="POST">
      <input name="email" type="email" placeholder="Email" required>
      <input name="password" type="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <p><a href="register.php">Don't have an account?</a></p>
  </div>
</body>
</html>
