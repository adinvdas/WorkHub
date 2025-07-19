<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $submission_id = $_POST['submission_id'];
  $comment_text = trim($_POST['comment']);
  $new_due_date = !empty($_POST['new_due_date']) ? $_POST['new_due_date'] : null;

  if (empty($comment_text)) {
    die('Comment is required.');
  }

  // Update submission status to 'modify'
  $stmt = $pdo->prepare("UPDATE submissions SET status = 'modify' WHERE id = ?");
  $stmt->execute([$submission_id]);

  // Insert comment and optional new due date
  $stmt = $pdo->prepare("INSERT INTO comments (submission_id, comment_text, new_due_date) VALUES (?, ?, ?)");
  $stmt->execute([$submission_id, $comment_text, $new_due_date]);

  // Get user_id for notification
  $stmt = $pdo->prepare("SELECT user_id FROM submissions WHERE id = ?");
  $stmt->execute([$submission_id]);
  $user_id = $stmt->fetchColumn();

  // Insert notification
  $message = "Your submission requires changes. Admin left a comment.";
  $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
  $stmt->execute([$user_id, $message]);

  header("Location: review_submissions.php");
  exit;
}
?>
