<?php
require_once './lib/utils.php';
$user = getLoggedUser();

if ($user != null) {
    redirect_authenticated();
}

function ask_recover_account()
{
    // If we are here, we are sure of having the email set
    $email = $_POST['email'];

    // check types
    if (!is_string($email)) {
        return [
            "error" => "Invalid email",
        ];
    }

    require_once './lib/DB.php';

    // check if email exists
    $db = DB::getInstance();
    $ans = $db->exec('SELECT * FROM `user` WHERE `email` = :email', [
        'email' => $email
    ]);

    if (count($ans) === 0) {
        security_log("Attempt of recovering password for non existing user ({$email})");
        return [
            "msg" => "Check your email for the password recovery link",
        ];
    }

    $user = $ans[0];

    // Cleanup expired requests
    $db->exec('DELETE FROM `user_recover` WHERE `user_id` = :user_id AND `valid_until` < NOW()', [
        'user_id' => $user['id']
    ]);

    // Check if user has a pending request
    $ans = $db->exec('SELECT * FROM `user_recover` WHERE `user_id` = :user_id', [
        'user_id' => $user['id']
    ]);

    if(count($ans) > 0) {
        return [
            "msg" => "Check your email for the password recovery link",
        ];
    }

    // create token
    $token = bin2hex(random_bytes(32));
    $DEPLOYED_DOMAIN = getenv('DEPLOYED_DOMAIN');

    // send code via email
    $ans = send_mail(
        $email,
        'Recover account',
        "Click <a href=\"{$DEPLOYED_DOMAIN}/recover_password.php?token={$token}&user_id={$user['id']}\">here</a> to reset your password!",
        'text/html'
    );

    if (!$ans) {
        return [
            "error" => "Couldn't send email, please try again later",
        ];
    }

    $ans = $db->exec('INSERT INTO `user_recover` (`user_id`, `token`, `valid_until`) VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 24 HOUR))', [
        'user_id' => $user['id'],
        'token' => password_hash($token, PASSWORD_DEFAULT),
    ]);

    security_log("User {$user['id']} requested password recovery");

    return [
        "msg" => "Check your email for the password recovery link",
    ];
}

function recover_account()
{
    if (!isset($_POST['token']) || !isset($_POST['user_id']) || !isset($_POST['password']) || !isset($_POST['confirm_password'])) {
        return [
            "error" => "Invalid token or password"
        ];
    }

    $token = $_POST['token'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_POST['user_id'];

    // check types
    if (!is_string($token) || !is_numeric($user_id) || !is_string($new_password) || !is_string($confirm_password)) {
        return [
            "error" => "Invalid token or password"
        ];
    }

    if (!checkPassword($new_password)) {
        return [
            "error" => "Password doesn't meet requirements: at least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 symbol"
        ];
    }

    if ($new_password !== $confirm_password) {
        return [
            "error" => "Password mismatch"
        ];
    }

    require_once './lib/DB.php';

    // check if token actually exists
    $db = DB::getInstance();
    $ans = $db->exec('SELECT * FROM `user_recover` WHERE `user_id` = :user_id', [
        'user_id' => $user_id
    ]);

    if (count($ans) === 0) {
        security_log("Attempt of recovering password for user with no pending requests ({$user_id})");
        return [
            "error" => "Invalid token"
        ];
    }

    $user_recover = $ans[0];

    if(!password_verify($token, $user_recover['token'])) {
        security_log("Attempt of recovering password with invalid token ({$user_id})");
        return [
            "error" => "Invalid token"
        ];
    }

    // delete token from requests
    $db->exec('DELETE FROM `user_recover` WHERE `id` = :id', [
        'id' => $user_recover['id']
    ]);

    if (strtotime($user_recover['valid_until']) < time()) {
        security_log("Attempt of recovering password with expired token ({$user_id})");
        return [
            "error" => "Token is expired"
        ];
    }

    $ans = $db->exec('SELECT email FROM `user` WHERE `id` = :user_id', [
        'user_id' => $user_recover['user_id']
    ]);


    if (count($ans) === 0) {
        security_log("Attempt of recovering password for non existing user ({$user_recover['user_id']})");
        return [
            "error" => "Invalid token"
        ];
    }
    $user = $ans[0];

    $ans = send_mail($user['email'], "Password changed", "Your password has been changed successfully. If you didn't do this, please contact us.");

    if (!$ans) {
        return [
            "error" => "Couldn't send email, please try again later",
        ];
    }

    // update password
    $db->exec('UPDATE `user` SET `password` = :password WHERE `id` = :id', [
        'password' => password_hash($new_password, PASSWORD_DEFAULT),
        'id' => $user_recover['user_id']
    ]);

    header("Location: /login.php");
    die();
}

if (isPost()) {
    $out = (isset($_POST["email"])) ? ask_recover_account() : recover_account();
}

$description = "just b00k password recover page";
$title = "Recover account";
require_once "template/header.php"; ?>

<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto my-auto lg:py-0">
    <a href="#" class="flex items-center my-6 text-2xl font-semibold text-gray-900 ext-white">
        <img class="w-8 h-8 mr-2" src="static/icon.png" alt="logo" />
        Just b00k
    </a>
    <div class="w-full bg-white rounded-lg shadow order md:mt-0 sm:max-w-md xl:p-0 g-gray-800 order-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl ext-white">
                Recover account
            </h1>
            <form class="space-y-4 md:space-y-6" action="" method="POST">
                <?php if (isset($_GET["token"])) { ?>
                    <input type="hidden" name="token" id="token"value="<?php echo p($_GET["token"]); ?>" />
                    <input type="hidden" name="user_id" id="user_id"value="<?php echo p($_GET["user_id"]); ?>" />
                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 ext-white">Password</label>
                        <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" required="" />
                    </div>
                    <div>
                        <label for="confirm_password" class="block mb-2 text-sm font-medium text-gray-900 ext-white">Confirm password</label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" required="" />
                    </div>
                    <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center g-blue-600 over:bg-blue-700 ocus:ring-blue-800">Recover</button>

                    <?php if (isset($out["msg"])) { ?>
                        <p class="mt-2 text-sm text-green-600" id="msg">
                            <?php echo $out["msg"]; ?>
                        </p>
                    <?php } else if (isset($out["error"])) { ?>
                        <p class="mt-2 text-sm text-red-600 ext-red-500" id="error_msg">
                            <?php echo $out["error"]; ?>
                        </p>
                    <?php } ?>

                <?php } else { ?>
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 ext-white">Your email</label>
                        <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" placeholder="name@company.com" required="" />
                    </div>

                    <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center g-blue-600 over:bg-blue-700 ocus:ring-blue-800">Recover</button>

                    <?php if (isset($out["msg"])) { ?>
                        <p class="mt-2 text-sm text-green-600" id="msg">
                            <?php echo $out["msg"]; ?>
                        </p>
                    <?php } else if (isset($out["error"])) { ?>
                        <p class="mt-2 text-sm text-red-600 ext-red-500" id="error_msg">
                            <?php echo $out["error"]; ?>
                        </p>
                    <?php } ?>

                <?php } ?>
            </form>
        </div>
    </div>
</div>

<?php require_once "template/footer.php"; ?>