<?php
include 'config.php';

// Start session
session_start();

// Check for logout
if (isset($_GET['logout'])) {
    unset($_SESSION['authenticated']);
    header('Location: dashboard.php');  // Redirect back to the login page
    exit;
}

// Check for authentication
if (isset($_POST['password'])) {
    $inputPassword = $_POST['password'];

    // Fetch the hashed password from the database
    $db = new SQLite3($db_path);
    $result = $db->query("SELECT password FROM dashboard_passwords WHERE id = 1");
    $row = $result->fetchArray();
    $hashedPassword = $row['password'];

    if (password_verify($inputPassword, $hashedPassword)) {  // Compare using password_verify
        $_SESSION['authenticated'] = true;
    } else {
        $error = "Invalid password.";
    }
}

// Redirect if not authenticated
if (!isset($_SESSION['authenticated'])) {
?>
    <!DOCTYPE html>
    <html data-bs-theme="light">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Dashboard: Login</title>
        <link href="./css/custom.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        <!-- manifest.jsonの読み込み -->
        <link rel="manifest" href="./manifest.json">
    </head>

    <body>
        <div class="container mt-5">

            <h1 class="display-1">Emailist Dashboard</h1>
            <form class="row g-3" method="post" action="">

                <div class="col-auto">
                    <input class="form-control" type="password" name="password">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary mb-3" type="submit">Login</button>
                </div>

            </form>
            <?php if (isset($error)) echo "<p>$error</p>"; ?>
            <!-- Add this where you want the Reset Password button to appear -->
            <button type="button" class="btn btn-danger" id="resetPassword">Reset Password</button>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const admin_email = "<?php echo $admin_email; ?>"; // Get emailSender from PHP

                    document.getElementById('resetPassword').addEventListener('click', function() {
                        fetch('reset_password.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'resetPassword=true'
                            })
                            .then(response => response.text())
                            .then(data => {
                                if (data === 'Success') {
                                    alert(`Password has been reset and sent to ${admin_email}.`);
                                } else {
                                    alert(`Failed to reset password.:${data}`);
                                }
                            })
                            .catch((error) => console.error('Error:', error));
                    });
                });
            </script>
        </div>
    </body>

    </html>
<?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link href="./css/custom.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>
    <div class="container-fluid">

        <nav class="navbar bg-body-transparent">
            <span class="fs-4 fw-bold">
                <i data-dc-id="mailing_line"></i>emailist
            </span>
            <form class="d-flex" role="search">
                <!-- Button trigger modal -->
                <button type="button" class="btn btn-outline-success me-2" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    <i class="bi bi-file-text"></i> README
                </button>

                <!-- Modal -->
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel"><?php echo $readme_title; ?> </h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php echo $readme_text; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="?logout=1" class="btn btn-outline-danger"><i class="bi bi-door-closed"></i> Logout</a>
            </form>

        </nav>

        <div class="row">
            <h1 class="mt-4">Dashboard</h1>
            <div class="col-md-6">
                <div class="card">
                    <h5 class="card-header">Add email address to the list</h5>
                    <div class="card-body">

                        <!-- Add Email Form -->
                        <form method="post" action="">
                            <div class="mb-3">
                                <div class="input-group mb-3">
                                    <input type="email" class="form-control" placeholder="email address to add" aria-label="email address to add" aria-describedby="button-addon2" id="addEmail" name="addEmail" required>
                                    <button class="btn btn-primary" type="submit" id="button-addon2">
                                        <i class="bi bi-plus-circle"></i> Add Email</button>
                                </div>
                            </div>

                            <!-- <label for="addEmail" class="form-label">Add Email</label>
                <input type="email" class="form-control" id="addEmail" name="addEmail" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Email</button> -->
                        </form>


                        <!-- ... (HTML and other PHP code) -->
                        <?php
                        $db = new SQLite3('emailist.db');

                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addEmail'])) {
                            $addEmail = $_POST['addEmail'];

                            // Check if email already exists
                            $stmt = $db->prepare('SELECT * FROM email_addresses WHERE email = :email');
                            $stmt->bindValue(':email', $addEmail, SQLITE3_TEXT);
                            $result = $stmt->execute();

                            if ($result->fetchArray()) {
                                echo "<div class='alert alert-danger mt-3'>Email already exists.</div>";
                            } else {
                                // Insert email
                                $stmt = $db->prepare('INSERT INTO email_addresses (email) VALUES (:email)');
                                $stmt->bindValue(':email', $addEmail, SQLITE3_TEXT);
                                $stmt->execute();
                                echo "<div class='alert alert-success mt-3'>Email added.</div>";
                            }
                        }

                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeEmail'])) {
                            $removeEmail = $_POST['removeEmail'];
                            $stmt = $db->prepare('DELETE FROM email_addresses WHERE email = :email');
                            $stmt->bindValue(':email', $removeEmail, SQLITE3_TEXT);
                            $stmt->execute();
                            echo "<div class='alert alert-success mt-3'>Email removed.</div>";
                        }




                        ?>
                    </div>
                </div>

                <?php
                // ... (other PHP code)

                // Initialize SQLite database
                try {
                    $db = new SQLite3('emailist.db');
                } catch (Exception $e) {
                    die("Could not connect to database: " . $e->getMessage());
                }

                // Check if the table exists
                $tableCheck = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='email_addresses';");

                if (!$tableCheck) {
                    echo "<div class='alert alert-danger mt-3'>Error: The table for email addresses does not exist. Please start at <a href='./init_db.php'>init_db.php</a> from scratch.</div>";
                    exit;
                }

                // ... (other PHP code for handling email addresses)
                ?>


                <div class="card mt-3">
                    <h5 class="card-header">Controls</h5>
                    <div class="card-body">

                        <div class="card mt-2">
                            <div class="card-body">
                                <div class="d-flex w-100 justify-content-between">
                                    <!-- Copy to Clipboard Button -->
                                    <button id="copyButton" class="btn btn-primary mb-2">
                                        <i class="bi bi-clipboard"></i> Copy Email Addresses to Clipboard
                                    </button>
                                </div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', () => {
                                        const copyButton = document.getElementById('copyButton');

                                        copyButton.addEventListener('click', async () => {
                                            // Fetch email addresses from the database (PHP)
                                            <?php
                                            $db = new SQLite3('emailist.db');
                                            $result = $db->query('SELECT * FROM email_addresses');
                                            $emailArray = [];

                                            while ($row = $result->fetchArray()) {
                                                $emailArray[] = $row['email'];
                                            }
                                            $emailList = implode(',', $emailArray);
                                            ?>

                                            // Copy email addresses to clipboard
                                            const emailList = "<?php echo $emailList; ?>";
                                            await navigator.clipboard.writeText(emailList);

                                            alert('Email addresses copied to clipboard.');
                                        });
                                    });
                                </script>
                                <p class="mb-1 small">
                                    This is useful, for example, when you want to send a simultaneous e-mail with BCC, etc. in your own e-mail client. Press the Copy button and paste it into the BCC field of your mail client.
                                </p>

                            </div>
                        </div>


                        <!-- download csv  -->
                        <div class="card mt-2">
                            <div class="card-body">
                                <!-- ... existing HTML ... -->
                                <button id="downloadCsv" class="btn btn-primary mb-2"><i class="bi bi-filetype-csv"></i> Download CSV</button>
                                <!-- ... existing HTML ... -->
                                <script>
                                    document.addEventListener('DOMContentLoaded', () => {
                                        const downloadCsvButton = document.getElementById('downloadCsv');

                                        downloadCsvButton.addEventListener('click', () => {
                                            const table = document.querySelector('.table');
                                            let csv = [];
                                            for (let row of table.rows) {
                                                if (row.rowIndex === 0) continue; // skip header row (index 0)

                                                // 1列目(email列）だけ取得
                                                let cellData = row.cells[1].textContent;
                                                csv.push(cellData);
                                            }

                                            const csvString = csv.join('\n');
                                            const blob = new Blob([csvString], {
                                                type: 'text/csv;charset=utf-8;'
                                            });
                                            const url = URL.createObjectURL(blob);
                                            const a = document.createElement('a');
                                            a.href = url;
                                            a.download = 'email_list.csv';
                                            document.body.appendChild(a);
                                            a.click();
                                            document.body.removeChild(a);
                                        });
                                    });
                                </script>
                                <p class="mb-1 small">
                                    A list of registered email addresses can be downloaded in csv format. Please use it for system backup, etc.
                                </p>
                            </div>
                        </div>

                        <div class="card mt-2">
                            <div class="card-body">
                                <!-- ... CSV Upload ... -->
                                <form id="csvUploadForm" class="mb-2" enctype="multipart/form-data">
                                    <div class="input-group">
                                        <input type="file" id="csvFile" class="form-control" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04" aria-label="Upload" name="csvFile" accept=".csv" required>
                                        <button class="btn btn-primary" type="submit" id="inputGroupFileAddon04"> <i class="bi bi-upload"></i> Upload csv file</button>
                                    </div>

                                    <!-- <input type="file" id="csvFile" name="csvFile" accept=".csv" required>
                    <button type="submit" class="btn btn-primary mt-3">Upload</button> -->
                                </form>
                                <!-- ... existing HTML ... -->
                                <script>
                                    document.addEventListener('DOMContentLoaded', () => {
                                        // ... existing JavaScript ...

                                        // CSV Upload
                                        const csvUploadForm = document.getElementById('csvUploadForm');

                                        csvUploadForm.addEventListener('submit', async (e) => {
                                            e.preventDefault();
                                            const csvFile = document.getElementById('csvFile').files[0];
                                            const reader = new FileReader();

                                            reader.onload = async function(event) {
                                                const csvData = event.target.result;
                                                const emails = csvData.split('\n');

                                                // Here you can send `emails` to the server for saving them into the database.
                                                // For example, you could make an AJAX call to a PHP script that handles the database insertion.
                                                const formData = new FormData();
                                                formData.append('emails', JSON.stringify(emails));

                                                const response = await fetch('upload_emails.php', {
                                                    method: 'POST',
                                                    body: formData
                                                });

                                                if (response.ok) {
                                                    alert('Emails uploaded successfully.');
                                                    location.reload(); // ここでページをリロード
                                                } else {
                                                    alert('Failed to upload emails.');
                                                }
                                            };

                                            reader.readAsText(csvFile);
                                        });
                                    });
                                </script>
                                <p class="mb-1 small">
                                    This is used when you want to register all email addresses at once, and save each row of email addresses.
                                </p>
                            </div>
                        </div>
                        <hr>


                        <div class="accordion custom-accordion" id="accordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Danger Zone
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <!-- Delete All Emails Button -->
                                        <button id="deleteAllButton" class="btn btn-danger mb-2">Delete all emails on the table</button>
                                        <!-- Delete Database Button -->
                                        <button id="deleteDatabaseButton" class="btn btn-danger mb-2"><i class="bi bi-database-x"></i> Delete Database</button>


                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const deleteAllButton = document.getElementById('deleteAllButton');

                                deleteAllButton.addEventListener('click', function() {
                                    if (confirm('Are you sure you want to delete all email addresses?')) {
                                        // Ajax request to delete all emails
                                        const xhr = new XMLHttpRequest();
                                        xhr.open('POST', 'delete_all_emails.php', true);
                                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                        xhr.onload = function() {
                                            if (this.status === 200) {
                                                alert('All email addresses have been deleted.');
                                                location.reload(); // Reload the page to update the email list
                                            } else {
                                                alert('Failed to delete email addresses.');
                                            }
                                        };
                                        xhr.send('deleteAll=true');
                                    }
                                });
                            });
                        </script>


                        <script>
                            // ... existing JavaScript code ...

                            // Confirm and Delete Database
                            const deleteDatabaseButton = document.getElementById('deleteDatabaseButton');

                            deleteDatabaseButton.addEventListener('click', () => {
                                if (confirm('Are you sure you want to delete the entire database? This action cannot be undone.')) {
                                    // Perform AJAX request to a PHP script to delete the database
                                    fetch('delete_database.php')
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.status === 'success') {
                                                alert('Database deleted successfully.');
                                                // Optionally, refresh the page or navigate the user to another page
                                            } else {
                                                alert('Failed to delete the database.');
                                            }
                                        });
                                }
                            });
                        </script>






                    </div>
                </div>

                <div class="card mt-3">
                    <h5 class="card-header">Page links </h5>
                    <div class="card-body">


                        <small class="me-2"><i class="bi bi-lock me"></i>: admin auth</small> <small><i class="bi bi-unlock"></i>: open access</small>
                        <div class="list-group">
                            <a href="init_db.php" target="_blank" class="list-group-item list-group-item-action" aria-current="true">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">init_db.php</h5>
                                    <small><i class="bi bi-lock"></i></small>
                                </div>
                                <p class="mb-1 small">
                                    Once the administrator and database have been created, nothing will happen when the database is accessed again unless it is deleted.
                                </p>
                            </a>

                            <a href="showall.php" target="_blank" class="list-group-item list-group-item-action" aria-current="true">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">showall.php</h5>
                                    <small><i class="bi bi-lock"></i></small>
                                </div>
                                <p class="mb-1 small">
                                    Displays all information in the sqlite database
                                </p>
                            </a>

                            <a href="send.php" target="_blank" class="list-group-item list-group-item-action" aria-current="true">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">send.php</h5>
                                    <small><i class="bi bi-lock"></i></small>
                                </div>
                                <p class="mb-1 small">
                                    This page is for administrators to send emails to all registered email addresses.Depending on the server settings, there is a high possibility that e-mails sent with this function will be classified as junk mail. Please be careful when using this function.
                                </p>
                            </a>

                            <a href="subscribe.php" target="_blank" class="list-group-item list-group-item-action" aria-current="true">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">subscribe.php</h5>
                                    <small><i class="bi bi-unlock"></i></small>
                                </div>
                                <p class="mb-1 small">
                                    This page is for general users who want to register their own e-mail address
                                </p>
                            </a>
                            <a href="unsubscribe.php" target="_blank" class="list-group-item list-group-item-action" aria-current="true">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">unsubscribe.php</h5>
                                    <small><i class="bi bi-unlock"></i></small>
                                </div>
                                <p class="mb-1 small">
                                    This page is for general users who want to cancel their own e-mail address registration.
                                </p>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-md-6">
                <!-- Email List -->
                <div class="card">
                    <h5 class="card-header">
                        Email list
                    </h5>
                    <div class="card-body">
                        <p class="text-start">
                            <!-- Button trigger modal -->
                            <button type="button" class="btn btn btn-outline-primary me-2" onclick="window.location.href = window.location.href;">
                                <i class="bi bi-arrow-clockwise"></i> Reload
                            </button>


                        </p>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $db->query('SELECT * FROM email_addresses');
                                while ($row = $result->fetchArray()) {
                                    echo "<tr><td>" . $row['id'] . "</td><td>" . $row['email'] . "</td>";
                                    echo "<td><form method='post' action='' onsubmit='return confirm(\"Are you sure you want to remove this email address?\");'><input type='hidden' name='removeEmail' value='" . $row['email'] . "'><button type='submit' class='btn btn-danger btn-sm'><i class='bi bi-x-circle'></i> Remove</button></form></td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

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


        <!-- Add bootstrap JS at the end -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/gh/TetsuakiBaba/daicon@61541d7/daicon.js" crossorigin="anonymous" type="text/javascript"></script>

</body>

</html>