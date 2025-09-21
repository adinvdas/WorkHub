<?php
session_start();
require 'db.php';

// Only allow admins to access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle revoke/delete/assign
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Revoke Access
    if (isset($_POST['revoke_access_id'])) {
        $revokeId = (int)$_POST['revoke_access_id'];
        $pdo->prepare("DELETE FROM file_access WHERE id = ?")->execute([$revokeId]);
        header("Location: view_uploaded_files.php");
        exit;
    }

    // Delete File
    if (isset($_POST['delete_file_id'])) {
        $fileId = (int)$_POST['delete_file_id'];

        $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch();

        if ($file && is_file($file['file_path'])) {
            @unlink($file['file_path']);
        }

        $pdo->prepare("DELETE FROM file_access WHERE file_id = ?")->execute([$fileId]);
        $pdo->prepare("DELETE FROM files WHERE id = ?")->execute([$fileId]);

        header("Location: view_uploaded_files.php");
        exit;
    }

    // Assign Access
    if (isset($_POST['assign_file_id'], $_POST['assign_user_id'])) {
        $fileId = (int)$_POST['assign_file_id'];
        $userId = (int)$_POST['assign_user_id'];

        // Avoid duplicate assignment
        $check = $pdo->prepare("SELECT 1 FROM file_access WHERE file_id = ? AND employee_id = ?");
        $check->execute([$fileId, $userId]);
        if (!$check->fetch()) {
            $pdo->prepare("INSERT INTO file_access (file_id, employee_id) VALUES (?, ?)")->execute([$fileId, $userId]);
        }

        header("Location: view_uploaded_files.php");
        exit;
    }
}

// Fetch all uploaded files
$files = $pdo->query("SELECT * FROM files ORDER BY id DESC")->fetchAll();

// Build access map
$accessMap = [];
if ($files) {
    $stmt = $pdo->prepare("
        SELECT fa.id AS access_id, fa.file_id, u.name, u.id AS user_id
        FROM file_access fa
        JOIN users u ON fa.employee_id = u.id
        WHERE fa.file_id = ?
    ");
    foreach ($files as $f) {
        $stmt->execute([$f['id']]);
        $accessMap[$f['id']] = $stmt->fetchAll();
    }
}

// Get all employees for dropdown
$allEmployees = $pdo->query("SELECT id, name FROM users WHERE role = 'employee'")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Uploaded Files</title>
  <style>
    /* Keep your previous styles here (unchanged) */
    :root {
      --background: #f0f4f8;
      --text: #1a1a1a;
      --card-bg: #ffffff;
      --border: #ccc;
      --accent: #0066cc;
    }
    [data-theme="dark"] {
      --background: #0a0a23;
      --text: #ffffff;
      --card-bg: #1c1c3c;
      --border: #444;
      --accent: #2dd4bf;
    }
    body {
      margin: 0;
      font-family: "Segoe UI", system-ui, -apple-system, Arial, sans-serif;
      background-color: var(--background);
      color: var(--text);
    }
    .navbar {
      background-color: var(--card-bg);
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      position: fixed;
      top: 0; left: 0; width: 100%;
      z-index: 999;
    }
    .navbar-logo { font-size: 22px; font-weight: 700; color: var(--accent); text-decoration: none; }
    .logout-btn {
      color: #fff; background: crimson; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-right: 80px;
    }
    .theme-toggle {
      background: transparent; border: 1px solid var(--border); padding: 6px 10px; border-radius: 10px; cursor: pointer;
      color: var(--text);
    }
    .files-container { padding: 120px 20px 40px; max-width: 1000px; margin: 0 auto; }
    .files-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
    .file-card { background-color: var(--card-bg); padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .file-title { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
    .file-link { color: var(--accent); font-weight: 600; text-decoration: none; display: inline-block; margin-bottom: 12px; }
    .access-list { border-top: 1px solid var(--border); margin-top: 12px; padding-top: 12px; }
    .access-item { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; }
    .btn { border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; color: white; font-weight: 600; }
    .btn-danger { background: #e74c3c; }
    .btn-danger:hover { background: #c0392b; }
    .assign-form { margin-top: 10px; }
    select { padding: 5px; border-radius: 5px; border: 1px solid var(--border); }
  </style>
</head>
<body>

<div class="navbar">
  <a href="admin_dashboard.php" class="navbar-logo">Admin Dashboard</a>
</div>

<div class="files-container">
  <h1>Uploaded Files</h1>
  <?php if (empty($files)): ?>
    <p>No files uploaded yet.</p>
  <?php else: ?>
    <div class="files-grid">
      <?php foreach ($files as $file): ?>
        <div class="file-card">
          <h3 class="file-title"><?= htmlspecialchars(basename($file['file_path'])) ?></h3>
          <a class="file-link" href="<?= htmlspecialchars($file['file_path']) ?>" target="_blank">View / Download</a>

          <!-- Delete File -->
          <form method="post" style="display:inline;" onsubmit="return confirm('Delete this file permanently?')">
            <input type="hidden" name="delete_file_id" value="<?= (int)$file['id'] ?>">
            <button class="btn btn-danger">Delete File</button>
          </form>

          <!-- Current Access List -->
          <div class="access-list">
            <strong>Access Given To:</strong>
            <?php if (empty($accessMap[$file['id']])): ?>
              <p>No access assigned yet.</p>
            <?php else: ?>
              <?php foreach ($accessMap[$file['id']] as $access): ?>
                <div class="access-item">
                  <?= htmlspecialchars($access['name']) ?>
                  <form method="post" style="display:inline;" onsubmit="return confirm('Revoke access from this employee?')">
                    <input type="hidden" name="revoke_access_id" value="<?= (int)$access['access_id'] ?>">
                    <button class="btn btn-danger">Revoke</button>
                  </form>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- Assign Access Form -->
          <form method="post" class="assign-form">
            <input type="hidden" name="assign_file_id" value="<?= (int)$file['id'] ?>">
            <select name="assign_user_id" required>
              <option value="">-- Select Employee --</option>
              <?php
                // Show only employees without access to this file
                $assignedUserIds = array_column($accessMap[$file['id']] ?? [], 'user_id');
                foreach ($allEmployees as $emp):
                  if (!in_array($emp['id'], $assignedUserIds)):
              ?>
                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
              <?php endif; endforeach; ?>
            </select>
            <button class="btn" style="background: var(--accent);">Assign</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
  const htmlEl = document.documentElement;
  const toggleBtn = document.getElementById('theme-toggle');
  const saved = localStorage.getItem('theme');
  if (saved) htmlEl.setAttribute('data-theme', saved);
  toggleBtn.addEventListener('click', () => {
    const next = htmlEl.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    htmlEl.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
  });
</script>

</body>
</html>
