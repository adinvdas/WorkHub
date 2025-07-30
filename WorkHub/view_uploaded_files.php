<?php
session_start();
require 'db.php';

$company_id = $_SESSION['user_id'];

// Handle revoke request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['revoke_access_id'])) {
        $revokeId = $_POST['revoke_access_id'];
        $pdo->prepare("DELETE FROM file_access WHERE id = ?")->execute([$revokeId]);
        header("Location: view_uploaded_files.php");
        exit;
    }

    if (isset($_POST['delete_file_id'])) {
        $fileId = $_POST['delete_file_id'];

        // Get the file path to delete it from the server
        $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = ? AND company_id = ?");
        $stmt->execute([$fileId, $company_id]);
        $file = $stmt->fetch();

        if ($file) {
            // Delete physical file
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }

            // Delete file access records
            $pdo->prepare("DELETE FROM file_access WHERE file_id = ?")->execute([$fileId]);

            // Delete file record
            $pdo->prepare("DELETE FROM files WHERE id = ? AND company_id = ?")->execute([$fileId, $company_id]);
        }

        header("Location: view_uploaded_files.php");
        exit;
    }
}

// Fetch all uploaded files by this company
$files = $pdo->query("SELECT * FROM files");
$files = $files->fetchAll();

// Fetch access list per file
$accessMap = [];
foreach ($files as $file) {
    $stmt = $pdo->prepare("
        SELECT fa.id as access_id, u.name FROM file_access fa
        JOIN users u ON fa.employee_id = u.id
        WHERE fa.file_id = ?
    ");
    $stmt->execute([$file['id']]);
    $accessMap[$file['id']] = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Uploaded Files</title>
  <style>
    body { font-family: Arial; padding: 20px; background: #f2f2f2; }
    .file-card {
      background: #fff;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 10px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }
    .file-card h3 { margin-top: 0; }
    .access-list { margin-top: 10px; }
    .access-item {
      display: flex;
      justify-content: space-between;
      padding: 5px 0;
    }
    form.revoke-form, form.delete-form {
      display: inline;
    }
    button.revoke-btn, button.delete-btn {
      background: #e74c3c;
      color: white;
      border: none;
      padding: 5px 10px;
      border-radius: 5px;
      cursor: pointer;
      margin-left: 10px;
    }
    .top-buttons {
      margin-top: 10px;
    }
    a.back {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      color: #007bff;
    }
  </style>
</head>
<body>

  <h2>Your Uploaded Files</h2>

  <?php foreach ($files as $file): ?>
    <div class="file-card">
      <h3><?= basename($file['file_path']) ?></h3>

      <p><a href="<?= $file['file_path'] ?>" target="_blank">View / Download</a></p>

      <div class="top-buttons">
        <form method="post" class="delete-form" onsubmit="return confirm('Delete this file permanently?')">
          <input type="hidden" name="delete_file_id" value="<?= $file['id'] ?>">
          <button class="delete-btn">Delete File</button>
        </form>
      </div>

      <div class="access-list">
        <strong>Access Given To:</strong>
        <?php if (empty($accessMap[$file['id']])): ?>
          <p>No access assigned yet.</p>
        <?php else: ?>
          <?php foreach ($accessMap[$file['id']] as $access): ?>
            <div class="access-item">
              <?= htmlspecialchars($access['name']) ?>
              <form method="post" class="revoke-form" onsubmit="return confirm('Revoke access from this employee?')">
                <input type="hidden" name="revoke_access_id" value="<?= $access['access_id'] ?>">
                <button class="revoke-btn">Revoke</button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <a href="admin_dashboard.php" class="back">← Back to Dashboard</a>

</body>
</html>
