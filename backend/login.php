<?php
require_once './lib/utils.php';
$user = getLoggedUser();

if ($user != null) {
    redirect_authenticated();
}

// if retuns, returns an error message to print
function loginPost()
{
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        return "Invalid username or password";
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    // check types
    if (!is_string($username) || !is_string($password)) {
        return "Invalid username or password";
    }

    // Check if user is already registered
    require_once './lib/DB.php';

    $db = DB::getInstance();
    $ans = $db->exec('SELECT * FROM `user` WHERE `username` = :username', [
        'username' => $username
    ]);

    if (count($ans) === 0) {
        security_log("Attempt to log with not existing user ({$username})");
        return 'Wrong username or password';
    }

    // check if user is locked
    if ($ans[0]['locked'] === 1) {
        security_log("Attempt to log with locked user ({$username})");
        return 'Wrong username or password';
    }

    $user = $ans[0];
    // check password
    if (!password_verify($password, $user['password'])) {
        security_log("Attempt to log with wrong password for user ({$username})");

        // Delete old logs
        $db->exec('DELETE FROM `wrong_login` WHERE `created_at` < DATE_ADD(NOW(), INTERVAL -24 HOUR)');

        // Add new wrong_login event
        $db->exec('INSERT INTO `wrong_login` (`user_id`) VALUES (:user_id)', [
            'user_id' => $user['id'],
        ]);

        // Check if 3 wrong attempts have been done
        $ans = $db->exec('SELECT COUNT(*) AS amount FROM `wrong_login` WHERE user_id = :user_id AND `created_at` > DATE_ADD(NOW(), INTERVAL -10 MINUTE)', [
            'user_id' => $user['id'],
        ]);

        if ($ans[0]['amount'] >= 3) {           
            // Send email to unlock user
            $token = bin2hex(random_bytes(32));
            $DEPLOYED_DOMAIN = getenv('DEPLOYED_DOMAIN');
        
            $ans = send_mail(
                $user['email'],
                'Unlock your account',
                "Click <a href=\"{$DEPLOYED_DOMAIN}/unlock.php?token={$token}\">here</a> in order to unlock your account!",
                'text/html'
            );

            if (!$ans) {
                return "Couldn't send unlock email";
            }

            $db->exec('INSERT INTO `user_lock` (`user_id`, `token`) VALUES (:user_id, :token)', [
                'user_id' => $user['id'],
                'token' => $token
            ]);        

            // lock the user
            $db->exec('UPDATE `user` SET `locked` = 1 WHERE `id` = :user_id', [
                'user_id' => $user['id'],
            ]);

            security_log("User ({$username}) has been locked");
            return 'Wrong username or password';
        }

        return 'Wrong username or password';
    }

    // Check if user is verified
    if ($user['verified'] === 0) {
        return "user is not verified, please check your email first";
    }

    // create session
    $token = bin2hex(random_bytes(32));
    $db->exec('INSERT INTO `session` (`user_id`, `token`, `valid_until`) VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 30 DAY))', [
        'user_id' => $user['id'],
        'token' => $token
    ]);

    // Expire in 1 month
    setcookie("session", $token, time() + 30 * 24 * 60 * 60, "/", "", true, true);
    redirect_authenticated();
}

if (isPost()) {
    $error_msg = loginPost();
}

// TODO: show error message

$description = "just b00k login page";
$title = "Login";
require_once "template/header.php"; ?>

<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto my-auto lg:py-0">
    <a href="/" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 ext-white">
        <img class="w-8 h-8 mr-2" src="static/icon.png" alt="logo" />
        Just b00k
    </a>
    <div class="w-full bg-white rounded-lg shadow order md:mt-0 sm:max-w-md xl:p-0 g-gray-800 order-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl ext-white">
                Sign in to read your books
            </h1>
            <form class="space-y-4 md:space-y-6" action="" method="POST">
                <div>
                    <label for="username" class="block mb-2 text-sm font-medium text-gray-900 ext-white">Your username</label>
                    <input type="text" name="username" id="username" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" placeholder="username" required="" />
                </div>
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900 ext-white">Password</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" required="" />
                </div>

                <?php if (isset($error_msg)) { ?>
                    <p class="mt-2 text-sm text-red-600 ext-red-500" id="error_msg">
                        <?php echo $error_msg; ?>
                    </p>
                <?php } ?>

                <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center g-blue-600 over:bg-blue-700 ocus:ring-blue-800">Sign in</button>
                <p class="text-sm font-light text-gray-500 ext-gray-400">
                    Don't have an account yet? <a href="/register.php" class="font-medium text-blue-600 hover:underline ext-blue-500">Sign up</a>
                </p>
                <p class="text-sm font-light text-gray-500 ext-gray-400">
                    Forgot your password? <a href="/recover_password.php" class="font-medium text-blue-600 hover:underline ext-blue-500">Recover it</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php require_once "template/footer.php"; ?>