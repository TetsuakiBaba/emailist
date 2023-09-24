<?php
include 'settings.php';
// セッションを開始
session_start();

// ログインしていない場合はリダイレクト
// if (!isset($_SESSION['authenticated'])) {
//     header('Location: dashboard.php');
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resetPassword'])) {
    // ランダムな8桁の新しいパスワードを生成
    $newPassword = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 8);

    // 新しいパスワードをハッシュ化
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // SQLite3データベースに接続
    $db = new SQLite3('emailist.db');

    // パスワードを更新
    $stmt = $db->prepare('UPDATE dashboard_passwords SET password = :password WHERE id = 1');
    $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);

    // ...
    if ($stmt->execute()) {
        // $admin_emailと$emailSenderはsettings.phpから取得すると仮定
        require_once 'settings.php';

        // メールを送信
        $subject = "Your Password Has Been Reset";
        $message = "Your new password is: " . $newPassword;
        $headers = "From: " . $emailSender;

        if (mail($admin_email, $subject, $message, $headers)) {
            echo "Success";
        } else {
            echo "MailFailed";
        }
    } else {
        echo "DBFailed";
    }
    // ...

}
