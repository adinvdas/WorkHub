...
  <?php
session_start();
require 'db.php'; // or wherever your DB connection is

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$uploadError = '';

$task_id = $_GET['task_id'] ?? null;

if (!$task_id || !is_numeric($task_id)) {
  die("Invalid Task ID.");
}

// Fetch the task from the DB
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND assigned_to = ?");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch();

if (!$task) {
  die("Task not found or not assigned to you.");
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    $fileName = basename($_FILES['file']['name']);
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
      mkdir($targetDir, 0777, true);
    }
    $targetFile = $targetDir . time() . "_" . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
      // Insert submission
      $stmt = $pdo->prepare("INSERT INTO submissions (task_id, user_id, file_path, submitted_at) VALUES (?, ?, ?, NOW())");
      $stmt->execute([$task_id, $user_id, $targetFile]);
      header("Location: employee_dashboard.php?status=success");
      exit;
    } else {
      $uploadError = "File upload failed.";
    }
  } else {
    $uploadError = "No file uploaded or upload error.";
  }
}
?>

  <style>
    :root {
      --bg-color: #f5f5f5;
      --card-bg: #ffffff;
      --text-color: #222;
      --accent-color: rgba(252, 162, 17, 1);
    }

    [data-theme="dark"] {
      --bg-color: #1a1a2e;
      --card-bg: #25274d;
      --text-color: #ffffff;
      --accent-color: #16c79b;
    }

    body {
      margin: 0;
      font-family: "Segoe UI", sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
    }

    .navbar {
      background-color: var(--card-bg);
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 999;
    }

    .navbar-left {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .navbar-logo {
      font-size: 22px;
      font-weight: bold;
      color: var(--accent-color);
      text-decoration: none;
    }

    .theme-toggle {
      background: transparent;
      border: none;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 18px;
      transition: background 0.3s ease;
      color: var(--accent-color);
    }

    .theme-toggle:hover {
      background: rgba(0,0,0,0.05);
    }

    .logout-btn {
      color: white;
      background: crimson;
      padding: 8px 14px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: 0.2s;
      margin-right: 50px
    }

    .logout-btn:hover {
      background: rgb(200, 0, 0);
    }

    .card {
      background-color: var(--card-bg);
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      width: 90%;
      max-width: 500px;
      margin: 120px auto 50px;
    }

    input[type="file"], button {
      width: 100%;
      padding: 0.75rem;
      margin-top: 10px;
      border-radius: 6px;
      border: none;
      font-size: 16px;
    }

    button[type="submit"] {
      background-color: var(--accent-color);
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button[type="submit"]:hover {
      opacity: 0.9;
    }

    .back-link {
      text-decoration: none;
      display: inline-block;
      margin-top: 15px;
      font-weight: 500;
      color: var(--accent-color);
    }

    .back-link:hover {
      text-decoration: none;
    }
    .navbar-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

  </style>
</head>
<body data-theme="light">
  <div class="navbar">
    <div class="navbar-left">
  <a class="navbar-logo" href="employee_dashboard.php">WorkHub</a>
</div>
<div class="navbar-right">
  <button id="theme-toggle" class="theme-toggle">🌓</button>
  <a href="logout.php" class="logout-btn">Logout</a>
</div>
  </div>

  <div class="card">
    <h2>Submit Task</h2>
    <p><strong><?= htmlspecialchars($task['title']) ?></strong></p>
    <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>

    <form method="post" enctype="multipart/form-data">
      <label>Select file to upload:</label><br>
      <input type="file" name="file" required><br>
      <button type="submit">Submit</button>
    </form>

    <?php if ($uploadError): ?>
      <p style="color: red;"><?= $uploadError ?></p>
    <?php endif; ?>

    <p><a class="back-link" href="employee_dashboard.php">← Back to Dashboard</a></p>
  </div>

  <script>
    const toggleBtn = document.getElementById('theme-toggle');
    const html = document.documentElement;

    // Load saved theme from localStorage
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
      html.setAttribute('data-theme', savedTheme);
    }

    toggleBtn.addEventListener('click', () => {
      const current = html.getAttribute('data-theme');
      const next = current === 'dark' ? 'light' : 'dark';
      html.setAttribute('data-theme', next);
      localStorage.setItem('theme', next);
    });
  </script>
</body>
</html>
