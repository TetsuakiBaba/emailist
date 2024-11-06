<?php
// Include the settings file
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Subscribe</title>
    <link href="./css/custom.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1>Subscribe to <?php echo $service_name; ?></h1>
        <form method="post" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary">Subscribe</button>
        </form>

        <?php
        // subscribe.php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $db = new SQLite3($db_path);

            // Check if email already exists
            $stmt = $db->prepare('SELECT * FROM email_addresses WHERE email = :email');
            $stmt->bindValue(':email', $email, SQLITE3_TEXT);
            $result = $stmt->execute();
            if ($result->fetchArray()) {
                echo "<div class='alert alert-danger mt-3'>Email already exists.</div>";
            } else {
                // Insert email
                $stmt = $db->prepare('INSERT INTO email_addresses (email) VALUES (:email)');
                $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                // $stmt->execute();
                // echo "<div class='alert alert-success mt-3'>Email added.</div>";

                // If insertion was successful
                if ($stmt->execute()) {
                    // Send confirmation email
                    $to = $email;
                    $subject = $confirmationSubject;
                    $message = $confirmationMessage;
                    $headers = "From: $emailSender\r\n";

                    if (mail($to, $subject, $message, $headers)) {
                        echo "<div class='mt-3 alert alert-success'>Subscription successful. A confirmation email has been sent.</div>";
                    } else {
                        echo "<div class='mt-3 alert alert-warning'>Subscription successful, but the confirmation email could not be sent.</div>";
                    }
                }
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