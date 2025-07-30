<?php
session_start();
require 'db.php';

// Ensure only admin (company) user can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$error = '';
$success = '';

// Fetch all employees for the access dropdown
$employees = $pdo->query("SELECT id, name FROM users WHERE role = 'employee'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $description = trim($_POST['description']);

  if (empty($title) || empty($description)) {
    $error = "Title and Description are required.";
  } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
    $error = "File upload error.";
  } else {
    $fileName = basename($_FILES['file']['name']);
    $targetDir = "company_files/";

    if (!is_dir($targetDir)) {
      mkdir($targetDir, 0777, true);
    }

    $targetPath = $targetDir . time() . "_" . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
      // Insert into `files` table
      $stmt = $pdo->prepare("INSERT INTO files (title, description, file_path, uploaded_at) VALUES (?, ?, ?, NOW())");
      $stmt->execute([$title, $description, $targetPath]);

      $file_id = $pdo->lastInsertId();

      // Insert into `file_access` table
      if (!empty($_POST['access_employees'])) {
        $accessStmt = $pdo->prepare("INSERT INTO file_access (file_id, employee_id) VALUES (?, ?)");
        foreach ($_POST['access_employees'] as $empId) {
          $accessStmt->execute([$file_id, $empId]);
        }
      }

      $success = "File uploaded successfully and access granted.";
    } else {
      $error = "Failed to upload file.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Upload Private File</title>
  <style>
    body {
      font-family: sans-serif;
      background: #1a1a2e;
      padding: 30px;
    }
    .container {
      background: #fff;
      max-width: 600px;
      margin: 0 auto;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
      color: #333;
      margin-bottom: 20px;
    }
    label {
      font-weight: bold;
    }
    input[type="text"],
    textarea,
    input[type="file"],
    select,
    button {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      margin-bottom: 20px;
    }
    select[multiple] {
      height: 150px;
    }
    .message {
      padding: 10px;
      margin-bottom: 20px;
    }
    .success {
      background: #d4edda;
      color: #155724;
    }
    .error {
      background: #f8d7da;
      color: #721c24;
    }
    a.back {
      text-decoration: none;
      display: inline-block;
      color: #007bff;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Upload Private File</h2>

    <?php if ($error): ?>
      <div class="message error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="message success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
  <label for="title">File Title:</label>
  <input type="text" name="title" required>

  <label for="description">Description:</label>
  <textarea name="description" rows="4" required></textarea>

  <label for="file">Select File:</label>
  <input type="file" name="file" required>

  <label for="access_employees">Grant Access to Employees:</label>
  <select name="access_employees[]" multiple>
    <?php foreach ($employees as $emp): ?>
      <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
    <?php endforeach; ?>
  </select>

  <button type="submit">Upload File</button>
</form>


    <a href="admin_dashboard.php" class="back">← Back to Dashboard</a>
  </div>
</body>
</html>
