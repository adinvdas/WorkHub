<?php
session_start();
require 'db.php';

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

// Fetch employees for access dropdown
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
            $stmt = $pdo->prepare("INSERT INTO files (title, description, file_path, uploaded_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$title, $description, $targetPath]);
            $file_id = $pdo->lastInsertId();

            if (!empty($_POST['access_employees'])) {
                // If "none" is chosen, skip inserting employees
                if (!(count($_POST['access_employees']) === 1 && $_POST['access_employees'][0] === "none")) {
                    $accessStmt = $pdo->prepare("INSERT INTO file_access (file_id, employee_id) VALUES (?, ?)");
                    foreach ($_POST['access_employees'] as $empId) {
                        if ($empId !== "none") {
                            $accessStmt->execute([$file_id, $empId]);
                        }
                    }
                }
            }


            $success = "✅ File uploaded successfully and access granted.";
        } else {
            $error = "Failed to upload file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark"> <!-- default dark, will be updated by JS -->

<head>
    <meta charset="UTF-8">
    <title>Upload Private File - WorkHub</title>
    <style>
        /* === MAIN THEME VARIABLES === */
        :root {
            --bg-color: #f8fafc;
            --card-color: #ffffff;
            --text-color: #0f172a;
            --accent-color: #1dbf73;
            --error-bg: #fee2e2;
            --error-text: #b91c1c;
            --success-bg: #dcfce7;
            --success-text: #065f46;
            --muted-text: #555;
            --border-color: #e2e8f0;
        }

        /* === DARK THEME === */
        :root[data-theme='dark'] {
            --bg-color: #0f172a;
            --card-color: #1e293b;
            --text-color: #e2e8f0;
            --accent-color: #16c79a;
            --error-bg: #7f1d1d;
            --error-text: #fecaca;
            --success-bg: #065f46;
            --success-text: #d1fae5;
            --muted-text: #cccccc;
            --border-color: #334155;
        }

        /* === LIGHT THEME === */
        :root[data-theme='light'] {
            --bg-color: #f8fafc;
            --card-color: #ffffff;
            --text-color: #0f172a;
            --accent-color: #fca311ff;
            --error-bg: #fee2e2;
            --error-text: #b91c1c;
            --success-bg: #dcfce7;
            --success-text: #065f46;
            --muted-text: #555;
            --border-color: #e2e8f0;
        }

        /* === Global Styles === */
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            transition: background-color 0.3s, color 0.3s;
        }

        header {
            background-color: var(--card-color);
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            /* align items to the left */
            border-bottom: 2px solid #ccc;
        }

        header h1 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .header-right {
            margin-left: auto;
            /* pushes this section to the right */
            display: flex;
            align-items: center;
        }

        .theme-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.3s ease;
        }

        .theme-toggle:hover {
            opacity: 0.7;
        }


        .container {
            max-width: 650px;
            margin: 40px auto;
            background: var(--card-color);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            transition: background 0.3s;
        }

        h2 {
            text-align: center;
            color: var(--accent-color);
            margin-bottom: 25px;
            font-size: 1.4rem;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--text-color);
        }

        input[type="text"],
        textarea,
        input[type="file"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-color);
            color: var(--text-color);
            margin-bottom: 18px;
            font-size: 14px;
            transition: border 0.3s, background 0.3s;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(29, 191, 115, 0.2);
        }

        select[multiple] {
            height: 150px;
            cursor: pointer;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            background: var(--accent-color);
            color: white;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s, background 0.3s;
        }

        button:hover {
            transform: translateY(-2px);
            background: #17a765;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
        }

        .error {
            background: var(--error-bg);
            color: var(--error-text);
        }

        .success {
            background: var(--success-bg);
            color: var(--success-text);
        }

        a.back {
            display: inline-block;
            margin-top: 20px;
            color: var(--accent-color);
            font-weight: 500;
            text-decoration: none;
        }

        a.back:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        // === THEME HANDLER ===
        document.addEventListener("DOMContentLoaded", () => {
            const root = document.documentElement;
            const btn = document.getElementById("themeBtn");

            // Load saved theme
            const savedTheme = localStorage.getItem("theme") || "dark";
            root.setAttribute("data-theme", savedTheme);
            btn.textContent = savedTheme === "light" ? "☀️" : "🌙";

            // Toggle theme
            btn.addEventListener("click", () => {
                const currentTheme = root.getAttribute("data-theme");
                const newTheme = currentTheme === "light" ? "dark" : "light";
                root.setAttribute("data-theme", newTheme);
                localStorage.setItem("theme", newTheme);
                btn.textContent = newTheme === "light" ? "☀️" : "🌙";
            });
        });
    </script>
</head>

<body>

    <header>
        <h1>WorkHub</h1>
    </header>

    <div class="container">
        <h2>Upload Private File</h2>

        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
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
                <option value="none">🚫 Nobody (only Admin)</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                <?php endforeach; ?>
            </select>


            <button type="submit">📤 Upload File</button>
        </form>

        <a href="admin_dashboard.php" class="back">⬅ Back to Dashboard</a>
    </div>

</body>

</html>