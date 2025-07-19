<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

if (!isset($_GET['submission_id'])) {
  die('No submission selected.');
}

$submission_id = $_GET['submission_id'];

// Fetch task and submission details
$stmt = $pdo->prepare("
  SELECT s.id, s.task_id, s.user_id, t.title, s.file_path 
  FROM submissions s 
  JOIN tasks t ON s.task_id = t.id 
  WHERE s.id = ?
");
$stmt->execute([$submission_id]);
$submission = $stmt->fetch();

if (!$submission) {
  die('Submission not found.');
}
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Modify Submission - WorkHub</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="card">
    <h2>Modify Request for: <?= htmlspecialchars($submission['title']) ?></h2>

    <p>File submitted: <a href="<?= htmlspecialchars($submission['file_path']) ?>" target="_blank">View File</a></p>

    <form action="handle_modify.php" method="POST">
      <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">

      <label for="comment">Comment (what needs to be changed):</label><br>
      <textarea name="comment" id="comment" rows="5" required></textarea><br><br>

      <label for="new_due_date">New Due Date (optional):</label><br>
      <input type="date" name="new_due_date" id="new_due_date"><br><br>

      <button type="submit">Submit Modification Request</button>
    </form>
  </div>
</body>
</html>
