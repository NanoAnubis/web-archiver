<?php
// Establish database connection
$host = 'localhost';
$dbname = 'webarchiver';
$username = 'webuser';
$password = 'pass@webuser';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Fetch websites from the database
  $stmt = $pdo->query("SELECT * FROM websites");
  $websites = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Send websites as JSON response
  header("Content-Type: application/json");
  echo json_encode($websites);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
?>
