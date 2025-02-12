<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

$user = getLoggedUser();

if ($user == null) {
    raiseNotFound();
}

if (!$user['admin']) {
    die("Unauthorized access.");
}

if (!isset($_POST["csrf_token"]) || !check_csrf($_POST["csrf_token"])) {
    die("Invalid CSRF token.");
}

$db = DB::getInstance();

// Reset all users to non-premium
$db->exec("UPDATE `user` SET `premium` = 0");

if (isset($_POST['premium_users']) && is_array($_POST['premium_users'])) {
    // Sanitize the input by ensuring the user IDs are integers
    $premium_users = array_map('intval', $_POST['premium_users']);

    // Create a string of user IDs for the IN clause
    $user_ids = implode(',', $premium_users);

    // Directly construct the query string (Be careful with input sanitization)
    $query = "UPDATE `user` SET `premium` = TRUE WHERE `id` IN ($user_ids)";
    
    // Execute the query
    $db->exec($query);
}

header("Location: profile.php?msg=Premium users updated successfully");
exit;
