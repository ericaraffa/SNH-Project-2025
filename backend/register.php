<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

$user = getLoggedUser();

if ($user != null) {
    redirect_authenticated();
}

// Registration request
function registerPost()
{
    if (!isset($_POST['email']) || !isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['confirm_password'])) {
        return "Invalid data";
    }

    $email =            $_POST["email"];
    $username =         $_POST["username"];
    $password =         $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Check types
    if (!is_string($email) || !is_string($username) || !is_string($password)) {
        return "Invalid data";
    }

    if (!checkPassword($password)) {
        return "Password doesn't meet requirements: at least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 symbol";
    }

    if ($password !== $confirm_password) {
        return "Password mismatch";
    }

    if (!checkEmail($email)) {
        return "Invalid email";
    }

    $db = DB::getInstance();

    // Check if user is already registered
    $ans = $db->exec('SELECT * FROM `user` WHERE `email` = :email OR `username` = :username', [
        'email' => $email,
        'username' => $username
    ]);

    if (count($ans) !== 0) {
        security_log("Attempt to register an existing user ({$email})");
        header("Location: /login.php");
        die();
    }

    // Before adding stuff to the DB let's try to send the verification email
    $token = bin2hex(random_bytes(32));
    $DEPLOYED_DOMAIN = getenv('DEPLOYED_DOMAIN');

    $ans = send_mail(
        $email,
        'Verify your account',
        "Click <a href=\"{$DEPLOYED_DOMAIN}/verify.php?token={$token}\">here</a> in order to verify your account!",
        'text/html'
    );

    if (!$ans) {
        return "Couldn't send verification email";
    }

    // add user to db
    $db->exec('INSERT INTO `user` (`email`, `username`, `password`) VALUES (:email, :username, :password)', [
        'email' => $email,
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ]);

    // Last logged user
    $user_id = $db->lastInsertId();

    // add verification token to db
    $db->exec('INSERT INTO `user_verification` (`user_id`, `token`) VALUES (:user_id, :token)', [
        'user_id' => $user_id,
        'token' => $token
    ]);

    header("Location: /login.php");
    die();
}

if (isPost()) {
    $error_msg = registerPost();
}


$description = "At least Poe-try register page";
$title = "Register";
require_once "template/header.php"; ?>

<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto my-auto lg:py-0">
    <a href="/" class="flex items-center my-6 text-2xl font-semibold text-gray-900 ext-white">
        <img class="w-8 h-8 mr-2" src="static/icon.png" alt="logo" />
        At least Poe-try
    </a>
    <div class="w-full bg-white rounded-lg shadow order md:mt-0 sm:max-w-md xl:p-0 g-gray-800 order-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl ext-white">
                Sign up to create an account
            </h1>
            <form class="space-y-4 md:space-y-6" action="" method="POST">
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 ext-white">Your email</label>
                    <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" placeholder="name@company.com" required="" />
                </div>
                <div>
                    <label for="username" class="block mb-2 text-sm font-medium text-gray-900 ext-white">Your username</label>
                    <input type="text" name="username" id="username" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" placeholder="username" required="" />
                </div>
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900 ext-white">Password</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" required="" />
                </div>
                <div>
                    <label for="confirm_password" class="block mb-2 text-sm font-medium text-gray-900 ext-white">Confirm password</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" required="" />
                </div>

                <?php if (isset($error_msg)) { ?>
                    <p class="mt-2 text-sm text-red-600 ext-red-500" id="error_msg">
                        <?php echo $error_msg; ?>
                    </p>
                <?php } ?>

                <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center g-blue-600 over:bg-blue-700 ocus:ring-blue-800">Sign up</button>
                <p class="text-sm font-light text-gray-500 ext-gray-400">
                    Already have an account? <a href="/login.php" class="font-medium text-blue-600 hover:underline ext-blue-500">Sign in</a>
                </p>
            </form>
        </div>
    </div>
</div>