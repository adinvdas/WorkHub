<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$task_id = $_GET['task_id'] ?? null;

if (!$task_id) {
  echo "Invalid task ID.";
  exit;
}

// Fetch task info
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND assigned_to = ?");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch();

if (!$task) {
  echo "Task not found or not assigned to you.";
  exit;
}

// Check if submission already exists
$stmt = $pdo->prepare("SELECT * FROM submissions WHERE task_id = ? AND user_id = ?");
$stmt->execute([$task_id, $user_id]);
$submission = $stmt->fetch();

$uploadError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
  $uploadDir = "uploads/";
  $fileName = basename($_FILES['file']['name']);
  $targetPath = $uploadDir . time() . "_" . $fileName;

  if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
    if ($submission) {
      // Update
      $stmt = $pdo->prepare("UPDATE submissions SET file_path = ?, submitted_at = NOW(), status = 'pending' WHERE id = ?");
      $stmt->execute([$targetPath, $submission['id']]);
    } else {
      // Insert
      $stmt = $pdo->prepare("INSERT INTO submissions (task_id, user_id, file_path) VALUES (?, ?, ?)");
      $stmt->execute([$task_id, $user_id, $targetPath]);
    }
    header("Location: employee_dashboard.php");
    exit;
  } else {
    $uploadError = "Failed to upload file.";
  }
}
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Submit Task - WorkHub</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="card">
    <h2>Submit Task</h2>
    <p><strong><?= htmlspecialchars($task['title']) ?></strong></p>
    <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>

    <form method="post" enctype="multipart/form-data">
      <label>Select file to upload:</label><br>
      <input type="file" name="file" required><br><br>
      <button type="submit">Submit</button>
    </form>

    <?php if ($uploadError): ?>
      <p style="color: red;"><?= $uploadError ?></p>
    <?php endif; ?>

    <p><a href="employee_dashboard.php">Back to Dashboard</a></p>
  </div>
</body>
</html>
