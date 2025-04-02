<?php
include 'config.php';
// include 'SMTPMailer/config.php';
// セッションを開始
session_set_cookie_params([
    'lifetime' => 0,         // ブラウザを閉じるまで有効
    'path' => '/',           // すべてのパスで有効
    'domain' => '.adada.info', // サブドメイン間でセッション共有
    'secure' => true,        // HTTPSのみ
    'httponly' => true       // JavaScriptからアクセス不可
]);
// GETパラメータに session_id がある場合のみ適用
if (isset($_GET['session_id']) && !empty($_GET['session_id'])) {
    session_id($_GET['session_id']); // ここでセッションIDを設定
}
session_start();

if (
    !isset($_SESSION['authenticated']) &&
    (!isset($_SESSION['sender_authenticated']) || $_SESSION['sender_authenticated'] !== true)
) {
    // header('Location: dashboard.php');
    print(('You are not autherized.'));
    // print(var_dump($_SESSION['authenticated']));
    // print(var_dump($_SESSION['sender_authenticated']));
    // print(session_id());    
    exit();
}



// config.phpを読み込む
require('./SMTPMailer/config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 必要なファイルを読み込む
require('./SMTPMailer/PHPMailer/src/PHPMailer.php');
require('./SMTPMailer/PHPMailer/src/Exception.php');
require('./SMTPMailer/PHPMailer/src/SMTP.php');

// メールアドレスの数を取得
$db = new SQLite3($db_path);
$result = $db->query('SELECT COUNT(*) as count FROM email_addresses');
$row = $result->fetchArray();
$emailCount = $row['count'];

$sender_email_address = $SMTP_SENDER_ADDRESS;
// 最初はmyadadaから来たユーザのメールアドレスで送信しようとしたが，送信元と実際の送信元がことなることでgoogleのセキュリティに弾かれたため，下記はコメントアウトして一律 smptの正しいアドレスから送信するように変更
// if (isset($_SESSION['sender_email']) && !empty($_SESSION['sender_email'])) {
//     $sender_email_address = $_SESSION['sender_email'];
// }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Email Distribution</title>
    <link href="./css/custom.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1 class="display-2">Send Email to Subscribers</h1>

        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-people"></i> Send to <?php echo htmlspecialchars($emailCount); ?> email addresses<?php if (isset($_GET['max_send']) && !empty($_GET['max_send'])) {
                                                                                                                    echo " (limit: " . htmlspecialchars($_GET['max_send']) . ")";
                                                                                                                } ?> by <?php echo htmlspecialchars($service_name); ?>.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <!-- Email Content Form -->
        <form id="email-form" method="post" action="">
            <div class="mb-3">
                <label for="sender_email" class="form-label">Sender Email Address</label>
                <input type="email" class="form-control" id="sender_email" name="sender_email" value="<?php echo htmlspecialchars($sender_email_address); ?>" required disabled>
            </div>
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
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <strong class="me-2">Important</strong>SMTP server may take a few seconds per one email, so it may take some time to send all addresses.
                        </div>
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

            echo "<script>document.body.innerHTML = '<div class=\'alert alert-info\' role=\'alert\'>Sending to $emailCount adresses... <a href=\'log.html\' target=\'_blank\' onclick=\'window.close();\'>Check progress</a></div>';window.close();window.open('log.html');</script>";

            // クライアントへの応答を終了する前にバッファをクリアして送信
            ob_end_flush();
            flush();
            fastcgi_finish_request();  // クライアントへの応答を終了

            $current_timeout = ini_get("max_execution_time");
            // echo "Current timeout setting is: " . $current_timeout . " seconds.";


            $subject = $_POST['subject'];
            $messageBase = $_POST['message'];
            $headers = "From: $SMTP_SENDER_ADDRESS\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n"; // HTMLメールのためのヘッダー
            $db = new SQLite3('emailist.db');

            $result = $db->query('SELECT * FROM email_addresses');
            $errors = [];
            $list_sent_emails = [];
            $mail = new PHPMailer(true);
            $loop_count = 0;


            // タイムアウト時間を無効にする
            set_time_limit(0);
            file_put_contents("log.html", "<html><head><meta http-equiv='refresh' content='1'><style>body, html{height: 100%;margin: 0;display: flex;     justify-content: center;align-items: center;} .centered-text {font-size: 24px;font-weight: bold;text-align: center;}</style></head><body><div class='centered-text'>Progressing...</div></body></html>");

            while ($row = $result->fetchArray()) {
                $loop_count++;
                $to = $row['email'];
                $hostName = $_SERVER['HTTP_HOST'];
                $requestUri = $_SERVER['REQUEST_URI'];
                $scriptPath = dirname($requestUri);

                $unsubscribeLink = "<a href='https://$hostName$scriptPath/unsubscribe.php?email=" . urlencode($to) . "'>unsubscribe</a>";

                $message = nl2br($messageBase) . "<br><br>To " . $unsubscribeLink;

                // SMTP設定
                $mail->isSMTP();
                $mail->Host = $SMTP_SERVER;
                $mail->SMTPAuth = true;
                $mail->Username = $SMTP_USERNAME; // SMTPサーバーのユーザー名
                $mail->Password = $SMTP_PASSWORD; // SMTPサーバーのパスワード
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
                $mail->Port = $SMTP_PORT;

                // 送信者情報
                $mail->setFrom($SMTP_SENDER_ADDRESS, $service_name);

                // 日本語の文字エンコーディングを設定
                $mail->CharSet = 'UTF-8';

                // メールをHTML形式で送信
                $mail->isHTML(true);  // HTML形式を有効にする
                $mail->Subject = $subject;

                // HTML形式の本文
                $mail->Body = "
    <html>
    <body>
        <h1>{$subject}</h1>
        <p>{$message}</p>
    </body>
    </html>
";

                // HTMLが見れない環境用の代替テキスト
                $mail->AltBody = strip_tags($message); // プレーンテキスト形式

                // 受信者をクリア
                $mail->clearAddresses(); // 既存のアドレスをすべてクリア

                // 受信者を追加
                $mail->addAddress($to);

                // メール送信
                $mail->send();
                file_put_contents("log.html", "<html><head><meta http-equiv='refresh' content='1'><style>body, html{height: 100%;margin: 0;display: flex;     justify-content: center;align-items: center;} .centered-text {font-size: 24px;font-weight: bold;text-align: center;}</style></head><body><div class='centered-text'>Sent $loop_count emails.</div></body></html>");
                // usleep(10000);



                // if (!mb_send_mail($to, $subject, $message, $headers)) {
                //     $errors[] = $to;
                // }
                $list_sent_emails[] = $to;
                if (isset($_GET['max_send']) && is_numeric($_GET['max_send'])) {
                    if ($loop_count >= intval($_GET['max_send'])) {
                        break;
                    }
                }
            }

            $emailListHTML = "";
            if (isset($_GET['max_send']) && is_numeric($_GET['max_send'])) {
                $emailListHTML = "<br>Email List: " . implode(', ', $list_sent_emails);
            }
            file_put_contents("log.html", "<html><head><meta http-equiv='refresh' content='1'><style>body, html{height: 100%;margin: 0;display: flex;justify-content: center;align-items: center;} .centered-text {font-size: 24px;font-weight: bold;text-align: center;}</style></head><body><div class='centered-text'>DONE<br>Sent $loop_count emails." . $emailListHTML . "<br>Close the window</div></body></html>");

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

        <!-- Add bootstrap JS at the end -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </div>
</body>

</html>