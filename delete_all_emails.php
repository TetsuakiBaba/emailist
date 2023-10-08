<?php
include 'settings.php';
// セッションを開始
session_start();

// 認証状態をチェック
if (!isset($_SESSION['authenticated'])) {
    echo 'Not authorized';
    exit;
}

// POSTリクエストとdeleteAllパラメータが設定されている場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteAll'])) {
    $db = new SQLite3($db_path);
    $db->exec('DELETE FROM email_addresses');
    echo 'Success';
} else {
    echo 'Failed';
}
