<?php
include 'config.php';
$defaultEmail = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Unsubscribe</title>
    <link href="./css/custom.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1>Unsubscribe from <?php echo $service_name; ?></h1>

        <!-- Unsubscribe Form -->
        <form method="post" action="">
            <div class="mb-3">
                <label for="removeEmail" class="form-label">Email address</label>
                <input type="email" class="form-control" id="removeEmail" name="removeEmail" value="<?php echo $defaultEmail; ?>" required>
            </div>
            <button type="submit" class="btn btn-danger">Unsubscribe</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeEmail'])) {
            $removeEmail = $_POST['removeEmail'];
            $db = new SQLite3($db_path);

            // Check if email exists
            $stmt = $db->prepare('SELECT * FROM email_addresses WHERE email = :email');
            $stmt->bindValue(':email', $removeEmail, SQLITE3_TEXT);
            $result = $stmt->execute();

            if ($result->fetchArray()) {
                // Remove email
                $stmt = $db->prepare('DELETE FROM email_addresses WHERE email = :email');
                $stmt->bindValue(':email', $removeEmail, SQLITE3_TEXT);
                $stmt->execute();
                echo "<div class='alert alert-success mt-3'> $unsubscribeConfirmationMessage </div>";
            } else {
                echo "<div class='alert alert-danger mt-3'>Email not found.</div>";
            }
        }
        ?>

        <hr class="mt-5">
        <footer class="mt-2 mb-4">
            <div class="text-center small text-muted">
                <?php echo $footer_text; ?>
            </div>
        </footer>
        <!-- manifest.jsonをfetchで読み込み、Versionの値を取得 -->
        <script>
            fetch('./manifest.json')
                .then(response => response.json())
                .then(data => {
                    console.log(data.version);
                    document.querySelector('footer').innerHTML += `<div class="text-center text-muted small">${data.name} v.${data.version}</div>`;
                });
        </script>
    </div>
</body>

</html>