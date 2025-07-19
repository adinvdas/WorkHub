<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $submission_id = $_POST['submission_id'];
  $action = $_POST['action'];

  if (!in_array($action, ['accept', 'reject'])) {
    die('Invalid action.');
  }

  // Update status in the submissions table
  $stmt = $pdo->prepare("UPDATE submissions SET status = ? WHERE id = ?");
  $stmt->execute([$action === 'accept' ? 'accepted' : 'rejected', $submission_id]);

  // Optional: notify the user
  $stmt_user = $pdo->prepare("SELECT user_id FROM submissions WHERE id = ?");
  $stmt_user->execute([$submission_id]);
  $user_id = $stmt_user->fetchColumn();

  $message = $action === 'accept' 
    ? "Your submission has been accepted."
    : "Your submission has been rejected.";

  $notify = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
  $notify->execute([$user_id, $message]);

  // Redirect back to the review page
  header("Location: review_submissions.php");
  exit;
}
?>
