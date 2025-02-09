<?php
require_once './lib/utils.php';

function verifyGet()
{
    if (!isset($_GET['token'])) {
        return "Invalid token";
    }

    $token = $_GET['token'];

    // check types
    if (!is_string($token)) {
        return "Invalid token";
    }

    require_once './lib/DB.php';

    // retrieve user associated with the token
    $db = DB::getInstance();
    $ans = $db->exec('SELECT * FROM `user_verification` WHERE `token` = :token', [
        'token' => $token
    ]);

    if (count($ans) === 0) {
        return "Invalid token";
    }

    $user = $ans[0];
    $user_id = $user['user_id'];

    // set user as verified
    $db->exec('UPDATE `user` SET `verified` = 1 WHERE `id` = :user_id', [
        'user_id' => $user_id
    ]);

    // delete token from db
    $db->exec('DELETE FROM `user_verification` WHERE `user_id` = :user_id', [
        'user_id' => $user_id
    ]);

    header("Location: /login.php");
    die();
}

$error_msg = verifyGet();

// TODO Change description
$description = "At least Poe-try account verification page";
$title = "Verify account";
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