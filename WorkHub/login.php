<?php
session_start();
require 'db.php';

// Prevent browser from caching login page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


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
  <script src="theme-toggle.js" defer></script>
  <style>
    :root {
      --bg-color: #f5f5f5;
      --card-bg: #ffffff;
      --text-color: #222;
      --accent-color: #fca311;
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
      font-family: sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
    }

    .card {
      background-color: var(--card-bg);
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      width: 300px;
      margin: 100px auto;
    }

    input, button {
      width: 100%;
      margin: 0.5rem 0;
      padding: 0.5rem;
      border: none;
      border-radius: 5px;
    }

    button {
      background-color: var(--accent-color);
      color: white;
      font-weight: bold;
      cursor: pointer;
    }

    button:hover {
      opacity: 0.9;
    }

    a {
      color: var(--accent-color);
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    h2 {
      text-align: center;
      margin-bottom: 1rem;
    }

    p {
      text-align: center;
      font-size: 14px;
    }
  </style>
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
  <script>
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }
</script>

</body>
</html>
