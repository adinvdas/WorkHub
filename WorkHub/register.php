<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
  try {
    $stmt->execute([$name, $email, $password]);
    $message = "Registration successful. Please wait for admin approval.";
  } catch (PDOException $e) {
    $message = "Error: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Register - WorkHub</title>
  <link rel="stylesheet" href="style.css">
  <script src="theme-toggle.js" defer></script>
</head>
<body>
  <div class="card">
    <h2>Register</h2>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>
    <form method="POST">
      <input name="name" placeholder="Name" required>
      <input name="email" type="email" placeholder="Email" required>
      <input name="password" type="password" placeholder="Password" required>
      <button type="submit">Register</button>
    </form>
    <p><a href="login.php">Already have an account?</a></p>
  </div>
</body>
</html>
