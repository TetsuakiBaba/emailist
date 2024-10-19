<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['emails'])) {
  $emails = json_decode($_POST['emails'], true);
  $db = new SQLite3($db_path);

  foreach ($emails as $email) {
    $email = trim($email); // Remove any extra spaces or newlines
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $stmt = $db->prepare('INSERT OR IGNORE INTO email_addresses (email) VALUES (:email)');
      $stmt->bindValue(':email', $email, SQLITE3_TEXT);
      $stmt->execute();
    }
  }
}
