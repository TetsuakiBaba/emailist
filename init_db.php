<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Initialize Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<?php
include 'config.php';
// Start a session
session_start();

$db_file = $db_path;
$table_exists = false;
$db_exists = file_exists($db_file);

if ($db_exists) {
    // Check if the table exists in the database
    $db = new SQLite3($db_file);
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='email_addresses';");
    if ($result->fetchArray()) {
        $table_exists = true;
    }
}

if (!$db_exists || !$table_exists) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['initialPassword'])) {
        $initialPassword = $_POST['initialPassword'];

        // Hash the password before storing it
        $hashedPassword = password_hash($initialPassword, PASSWORD_DEFAULT);


        $db = new SQLite3($db_file);
        // Create the email_addresses table
        $db->exec("CREATE TABLE IF NOT EXISTS email_addresses (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT UNIQUE NOT NULL);");
        // Create the passwords table and set the initial password
        $db->exec("CREATE TABLE IF NOT EXISTS dashboard_passwords (id INTEGER PRIMARY KEY AUTOINCREMENT, password TEXT NOT NULL);");
        // Insert the hashed password into the database
        $stmt = $db->prepare("INSERT INTO dashboard_passwords (password) VALUES (:password);");
        $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
        $stmt->execute();

        echo "Password set successfully. go to <a href='dashboard.php'>dashboard</a>.";
        exit;
    }
?>

    <body>
        <div class="container mt-5">
            <h1>Initialize Database</h1>
            <form method="post" action="">
                <div class="mb-3">
                    <label for="initialPassword" class="form-label">Set Initial Password</label>
                    <input type="password" class="form-control" id="initialPassword" name="initialPassword" required>
                </div>
                <button type="submit" class="btn btn-primary">Set Password</button>
            </form>
        </div>
    </body>

</html>
<?php
} else {
    // The database and table exist, redirect or do something else
    echo "The database and table already exist. Nothing is changed and done.";
}
?>