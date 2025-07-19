<?php
$host = "localhost";
$dbname = "codeassign";
$username = "root"; // change if using different user
$password = "";     // change if you have a password

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
?>
