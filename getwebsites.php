<?php
$host = 'localhost';
$dbname = 'webarchiver';
$username = 'webuser';
$password = 'pass@webuser';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->query("SELECT * FROM websites ORDER BY url, date ASC");
  $websites = $stmt->fetchAll(PDO::FETCH_ASSOC);

  header("Content-Type: application/json");
  echo json_encode($websites);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
?>
