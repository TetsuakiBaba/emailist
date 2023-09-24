<?php
include 'settings.php';
// セッションを開始
session_start();

// ログインしていない場合はリダイレクト
if (!isset($_SESSION['authenticated'])) {
    header('Location: dashboard.php');
}

// メールアドレスの数を取得
$db = new SQLite3('emailist.db');
$result = $db->query('SELECT COUNT(*) as count FROM email_addresses');
$row = $result->fetchArray();
$emailCount = $row['count'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Email Distribution</title>
    <link href="./css/custom.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1>Send Email to Subscribers</h1>

        <!-- Email Content Form -->
        <form id="email-form" method="post" action="">
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#previewModal">Preview</button>
        </form>

        <!-- Preview Modal -->
        <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="previewModalLabel">Email Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="previewContent">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" form="email-form">Send Email</button>
                    </div>
                </div>
            </div>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject = $_POST['subject'];
            $messageBase = $_POST['message'];
            $headers = "From: $emailSender\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n"; // HTMLメールのためのヘッダー
            $db = new SQLite3('emailist.db');

            $result = $db->query('SELECT * FROM email_addresses');
            $errors = [];

            while ($row = $result->fetchArray()) {
                $to = $row['email'];
                $hostName = $_SERVER['HTTP_HOST'];
                $requestUri = $_SERVER['REQUEST_URI'];
                $scriptPath = dirname($requestUri);

                $unsubscribeLink = "<a href='http://$hostName$scriptPath/unsubscribe.php?email=" . urlencode($to) . "'>unsubscribe</a>";

                $message = nl2br($messageBase) . "<br><br>To " . $unsubscribeLink;

                if (!mb_send_mail($to, $subject, $message, $headers)) {
                    $errors[] = $to;
                }
            }

            if (empty($errors)) {
                echo "<div class='alert alert-success mt-3'>Emails sent successfully.</div>";
            } else {
                echo "<div class='alert alert-danger mt-3'>Failed to send emails to: " . implode(', ', $errors) . "</div>";
            }
        }
        ?>

        <script>
            // Update preview content when the Preview button is clicked
            document.addEventListener("DOMContentLoaded", function() {
                var previewButton = document.querySelector('[data-bs-target="#previewModal"]');
                var previewContent = document.getElementById('previewContent');

                previewButton.addEventListener('click', function() {
                    var subject = document.getElementById('subject').value;
                    var message = document.getElementById('message').value;
                    message = message.replace(/\n/g, '<br>');
                    var unsubscribeLink = "<a href='http://localhost:8000/unsubscribe.php?email=YOUR_EMAIL'>unsubscribe</a>";

                    // メールアドレス件数を表示
                    var emailCount = <?php echo $emailCount; ?>;
                    previewContent.innerHTML = 'Sending to ' + emailCount + ' email addresses.<br><br>';
                    previewContent.innerHTML += 'Subject: ' + subject + '<br><br>' + message + '<br><br>To ' + unsubscribeLink;
                });
            });
        </script>


        <!-- Add bootstrap JS at the end -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </div>
</body>

</html>