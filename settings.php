<?php
// settings.php
$service_name = "Your Service Name";

$db_path = "./emailist.db";

// email address to receive password reset link
$admin_email = "admin@example.com";

// email address to be used as send.php
$emailSender = "no-reply@example.com";

// email subject and message for confirmation and unsubscription
$confirmationSubject = "Subscription Confirmation";
$confirmationMessage = "You have successfully subscribed to our mailing list.";

// email subject and message for unsubscription confirmation
$unsubscribeConfirmationSubject = "Unsubscription Confirmation";
$unsubscribeConfirmationMessage = "You have successfully unsubscribed from our mailing list.";
