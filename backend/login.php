<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

// Check user authentication
$user = getLoggedUser();

// If the user is already logged, redirect to homepage
if ($user != null) {
    redirect_authenticated();
}

// This function contains all the security checks to submit the login
function loginPost()
{
    // Username and password must be set
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        return "Invalid username or password";
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check input types
    if (!is_string($username) || !is_string($password)) {
        return "Invalid username or password";
    }

    // Check if user is already registered
    $db = DB::getInstance();
    $ans = $db->exec('SELECT * FROM `user` WHERE `username` = :username', [
        'username' => $username
    ]);

    // User not found
    if (count($ans) === 0) {
        security_log("Attempt to log with not existing user ({$username})");
        return 'Wrong username or password';
    }

    // Check if user is locked
    if ($ans[0]['locked'] === 1) {
        security_log("Attempt to log with locked user ({$username})");
        return 'Wrong username or password';
    }

    $user = $ans[0];

    // Check password
    if (!password_verify($password, $user['password'])) {
        security_log("Attempt to log with wrong password for user ({$username})");

        // Delete old logs of login attempt  (more than 24 hours ago)
        $db->exec('DELETE FROM `wrong_login` WHERE `created_at` < DATE_ADD(NOW(), INTERVAL -24 HOUR)');

        // Add new login attempt
        $db->exec('INSERT INTO `wrong_login` (`user_id`) VALUES (:user_id)', [
            'user_id' => $user['id'],
        ]);

        // Check if 3 wrong attempts have been done in the last 10 minutes
        $ans = $db->exec('SELECT COUNT(*) AS amount FROM `wrong_login` WHERE user_id = :user_id AND `created_at` > DATE_ADD(NOW(), INTERVAL -10 MINUTE)', [
            'user_id' => $user['id'],
        ]);

        // Too many wrong attempts, block account
        if ($ans[0]['amount'] >= 3) {

            // TODO Change the email service 
            // Send email to unlock user
            $token = bin2hex(random_bytes(32));
            $DEPLOYED_DOMAIN = getenv('DEPLOYED_DOMAIN');
        
            $ans = send_mail(
                $user['email'],
                'Unlock your account',
                "Click <a href=\"{$DEPLOYED_DOMAIN}/unlock.php?token={$token}\">here</a> in order to unlock your account!",
                'text/html'
            );

            // Error during email sending
            if (!$ans) {
                return "Couldn't send unlock email";
            }

            // Lock current user and token
            $db->exec('INSERT INTO `user_lock` (`user_id`, `token`) VALUES (:user_id, :token)', [
                'user_id' => $user['id'],
                'token' => $token
            ]);        

            $db->exec('UPDATE `user` SET `locked` = 1 WHERE `id` = :user_id', [
                'user_id' => $user['id'],
            ]);

            // Log and return error message
            security_log("User ({$username}) has been locked");
            return 'Wrong username or password';
        }

        // Login failed
        return 'Wrong username or password';
    }

    // Check if user is verified
    if ($user['verified'] === 0) {
        return "user is not verified, please check your email first";
    }

    // Create session
    $token = bin2hex(random_bytes(32));
    $db->exec('INSERT INTO `session` (`user_id`, `token`, `valid_until`) VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 30 DAY))', [
        'user_id' => $user['id'],
        'token' => $token
    ]);

    // The session expires in 1 month
    setcookie("session", $token, time() + 30 * 24 * 60 * 60, "/", "", true, true);

    // Login successful, redirect to homepage
    redirect_authenticated();
}

// Try login
if (isPost()) {
    $error_msg = loginPost();
}

// Login page front-end
$description = "At least Poe-try login page";
$title = "Login";
require_once "template/header.php"; ?>

<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto my-auto lg:py-0">
    <a href="/" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 ext-white">
        <img class="w-8 h-8 mr-2" src="static/icon.png" alt="logo" />
        At least Poe-try
    </a>
    <div class="w-full bg-white rounded-lg shadow order md:mt-0 sm:max-w-md xl:p-0 g-gray-800 order-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl ext-white">
                Sign in to read novels
            </h1>
            <!-- Login form -->
            <form class="space-y-4 md:space-y-6" action="" method="POST">
                <div>
                    <label for="username" class="block mb-2 text-sm font-medium text-gray-900 ext-white">Your username</label>
                    <input type="text" name="username" id="username" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" placeholder="username" required="" />
                </div>
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900 ext-white">Password</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" required="" />
                </div>

                <!-- To show error message -->
                <?php if (isset($error_msg)) { ?>
                    <p class="mt-2 text-sm text-red-600 ext-red-500" id="error_msg">
                        <?php echo $error_msg; ?>
                    </p>
                <?php } ?>

                <!-- Login button -->
                <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center g-blue-600 over:bg-blue-700 ocus:ring-blue-800">
                    Sign in
                </button>
                <!-- Sign in -->
                <p class="text-sm font-light text-gray-500 ext-gray-400">
                    Don't have an account yet? <a href="/register.php" class="font-medium text-blue-600 hover:underline ext-blue-500">Sign up</a>
                </p>
                <!-- Recover password -->
                <p class="text-sm font-light text-gray-500 ext-gray-400">
                    Forgot your password? <a href="/recover_password.php" class="font-medium text-blue-600 hover:underline ext-blue-500">Recover it</a>
                </p>
            </form>
        </div>
    </div>
</div>