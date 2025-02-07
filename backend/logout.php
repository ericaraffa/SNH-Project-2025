<?php
require_once './lib/utils.php';
$user = getLoggedUser();

if ($user == null) {
    raiseNotFound();
}

if (!check_csrf($_POST["csrf_token"])) {
    header("Location: /");
    die();
}

require_once './lib/DB.php';
$db = DB::getInstance();
$db->exec('DELETE FROM `session` WHERE `token` = :token', [
    'token' => $_COOKIE["session"]
]);

setcookie("session", "", time() - 3600, "/");
setcookie("csrf_token", "", time() - 3600, "/");
header("Location: /");
