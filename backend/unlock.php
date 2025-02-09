<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

function unlockGet()
{
    if (!isset($_GET['token'])) {
        return "Invalid token";
    }

    $token = $_GET['token'];

    // check types
    if (!is_string($token)) {
        return "Invalid token";
    }

    // retrieve user associated with the token
    $db = DB::getInstance();
    $ans = $db->exec('SELECT * FROM `user_lock` WHERE `token` = :token', [
        'token' => $token
    ]);

    if (count($ans) === 0) {
        return "Invalid token";
    }

    $user = $ans[0];
    $user_id = $user['user_id'];

    // set user as verified
    $db->exec('UPDATE `user` SET `locked` = 0 WHERE `id` = :user_id', [
        'user_id' => $user_id
    ]);

    // delete token from db
    $db->exec('DELETE FROM `user_lock` WHERE `user_id` = :user_id', [
        'user_id' => $user_id
    ]);

    // Send email to inform the user
    $ans = send_mail($user['email'], "User unlocked", "Your user has been unlocked. If you didn't do this, please contact us.");

    if (!$ans) {
        return "Couldn't send email, please try again later";
    }

    header("Location: /login.php");
    die();
}

// Unlock request
$error_msg = unlockGet();

$description = "At least Poe-try account unlock page";
$title = "Unlock account";
require_once "template/header.php"; ?>

<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto my-auto lg:py-0">
    <div class="w-full bg-white rounded-lg shadow order md:mt-0 sm:max-w-md xl:p-0 g-gray-800 order-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <form class="space-y-4 md:space-y-6" action="" method="POST">

                <p class="mt-2 text-sm" id="msg">
                    <?php echo $error_msg; ?>
                </p>
            </form>
        </div>
    </div>
</div>