<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

// Check that the user is already logged
$user = getLoggedUser();

if ($user == null) {
    raiseNotFound();
}

// If the session is expired, return to the homepage
if (!check_csrf($_POST["csrf_token"])) {
    header("Location: /");
    die();
}

// Retrieve and invalidate the session
$db = DB::getInstance();
$db->exec('DELETE FROM `session` WHERE `token` = :token', [
    'token' => $_COOKIE["session"]
]);

setcookie("session", "", time() - 3600, "/");
setcookie("csrf_token", "", time() - 3600, "/");
header("Location: /");
