<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

$user = getLoggedUser();

if ($user == null) {
    raiseNotFound();
}

// Change the password
function profilePost($user)
{

    // Check current session token
    if (!isset($_POST["csrf_token"]) || !check_csrf($_POST["csrf_token"])) {
        return [
            "error" => "Invalid CSRF token",
        ];
    }

    // Check user input
    if (!isset($_POST['current_password']) || !isset($_POST['new_password']) || !isset($_POST['confirm_password'])) {
        return [
            "error" => "Invalid data",
        ];
    }

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check types
    if (!is_string($current_password) || !is_string($new_password) || !is_string($confirm_password)) {
        return [
            "error" => "Invalid data",
        ];
    }

    if ($new_password !== $confirm_password) {
        return [
            "error" => "The two passwords does not match",
        ];
    }

    // Check new password
    if (!checkPassword($new_password)) {
        return [
            "error" => "Password doesn't meet requirements: at least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 symbol",
        ];
    }

    // Check current password
    if (!password_verify($current_password, $user['password'])) {
        return [
            "error" => "Wrong password",
        ];
    }

    // TODO Change email service
    $ans = send_mail($user['email'], "Password changed", "Your password has been changed successfully. If you didn't do this, please contact us.");

    if (!$ans) {
        return [
            "error" => "Couldn't send email, please try again later",
        ];
    }

    // Update password in the database
    $db = DB::getInstance();
    $ans = $db->exec('UPDATE `user` SET `password` = :password WHERE `id` = :id', [
        'password' => password_hash($new_password, PASSWORD_DEFAULT),
        'id' => $user['id']
    ]);

    return [
        "msg" => "Password changed successfully",
    ];
}

// Load this page only through a POST request
if (isPost()) {
    $out = profilePost($user);
}

$description = "At least Poe-try profile page";
$title = "Profile page";
require_once "template/header.php"; ?>

<!-- Profile front-end -->
<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto my-auto lg:py-0">
    <a href="#" class="flex items-center my-6 text-2xl font-semibold text-gray-900 ext-white">
        <img class="w-8 h-8 mr-2" src="/static/icon.png" alt="logo" />
        At least Poe-try
    </a>
    <div class="w-full bg-white rounded-lg shadow order md:mt-0 sm:max-w-md xl:p-0 g-gray-800 order-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl ext-white">
                Change password
            </h1>
            <form class="space-y-4 md:space-y-6" action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900 ext-white">Old password</label>
                    <input type="password" name="current_password" id="current_password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" required="" />
                </div>
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900 ext-white">New password</label>
                    <input type="password" name="new_password" id="new_password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" required="" />
                </div>
                <div>
                    <label for="confirm_password" class="block mb-2 text-sm font-medium text-gray-900 ext-white">Confirm password</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 g-gray-700 order-gray-600 laceholder-gray-400 ext-white ocus:ring-blue-500 ocus:border-blue-500" required="" />
                </div>
                <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center g-blue-600 over:bg-blue-700 ocus:ring-blue-800">Change</button>

                <!-- To show error messages -->
                <?php if (isset($out["msg"])) { ?>
                    <p class="mt-2 text-sm text-green-600" id="msg">
                        <?php echo $out["msg"]; ?>
                    </p>
                <?php } else if (isset($out["error"])) { ?>
                    <p class="mt-2 text-sm text-red-600 ext-red-500" id="error_msg">
                        <?php echo $out["error"]; ?>
                    </p>
                <?php } ?>

            </form>

            <?php if ($user['admin']){ // Check if user is admin ?>
                <h2 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
                    Admin Panel: Manage Premium Users
                </h2>

                <form action="set_premium.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 border border-gray-300">
                            <thead class="bg-gray-100 text-gray-900">
                                <tr>
                                    <th class="px-4 py-2">User ID</th>
                                    <th class="px-4 py-2">Username</th>
                                    <th class="px-4 py-2 text-center">Premium</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                $db = DB::getInstance();
                                $users = $db->exec("SELECT * FROM `user`");

                                foreach ($users as $u) {
                                    $checked = $u['premium'] ? "checked" : ""; ?>
                                    <tr class='border-b'>
                                        <td class='px-4 py-2'><?php echo p($u['id']); ?></td>
                                        <td class='px-4 py-2'><?php echo p($u['username']); ?></td>
                                        <td class='px-4 py-2 text-center'>
                                            <input type='checkbox' name='premium_users[]' value='<?php echo p($u['id']); ?>' <?php echo $checked; ?>>
                                        </td>
                                    </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <button type="submit" class="w-full mt-4 text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-5 py-2.5">
                        Update Premium Users
                    </button>
                </form>
            <?php } ?>


            <!-- TODO The admin has a panel to change the other users privilege -->
        </div>
    </div>
</div>