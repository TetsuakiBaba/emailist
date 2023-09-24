<?php
session_start();

// Check if the user is authenticated in dashboard.php
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    header('Content-Type: application/json');

    $db_file = 'emailist.db';

    if (file_exists($db_file)) {
        if (unlink($db_file)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Could not delete the database file.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database file does not exist.']);
    }
} else {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
}

?>