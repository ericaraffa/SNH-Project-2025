<!DOCTYPE html>
<html lang="en">

<head>
    <title>At least Poe-try<?php echo (isset($title)) ? " | " . $title : ""; ?></title>
    <meta name="description" content="<?php echo (isset($description) ? $description : "at_least_poe-try"); ?>" />
    <meta charset="utf-8" />
    <link rel="icon" type="image/svg" href="/static/icon.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        const toggle_menu = () => {
            document.getElementById("mega-menu-full").classList.toggle("hidden");
        }
    </script>
</head>

<body>
    <div style="display: contents">

        <section class="bg-gray-50 g-gray-900 min-h-screen flex flex-col justify-between">
            <nav class="bg-white border-gray-200 order-gray-600 g-gray-900 border-y">
                <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl p-4">
                    <a href="/" class="flex items-center space-x-3 rtl:space-x-reverse">
                        <img src="static/icon.png" class="h-8" alt="at least poe-try logo" />
                        <span class="self-center text-2xl font-semibold whitespace-nowrap ext-white">At least Poe-try</span>
                    </a>
                    <button data-collapse-toggle="mega-menu-full" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 ext-gray-400 over:bg-gray-700 ocus:ring-gray-600" aria-controls="mega-menu-full" aria-expanded="false" onclick="toggle_menu()">
                        <span class="sr-only">Open main menu</span>
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15" />
                        </svg>
                    </button>
                    <div id="mega-menu-full" class="hidden items-center justify-between font-medium w-full md:flex md:w-auto md:order-1">
                        <ul class="flex flex-col p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-white g-gray-800 md:g-gray-900 order-gray-700">
                            <!-- TODO Change these links, some of which can be seen only by logged users-->
                            <li>
                                <a href="/" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ext-white md:over:text-blue-500 over:bg-gray-700 over:text-blue-500 md:over:bg-transparent order-gray-700" aria-current="page">Home</a>
                            </li>
                            <?php if ($user != null) { ?>
                                <li>
                                    <a href="/novel.php" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ext-white md:over:text-blue-500 over:bg-gray-700 over:text-blue-500 md:over:bg-transparent order-gray-700">Novels</a>
                                </li>
                                <li>
                                    <a href="/profile.php" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ext-white md:over:text-blue-500 over:bg-gray-700 over:text-blue-500 md:over:bg-transparent order-gray-700">Profile</a>
                                </li>
                                <li>
                                    <form action="/logout.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo p(get_csrf_token()); ?>">
                                        <button type="submit" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ext-white md:over:text-blue-500 over:bg-gray-700 over:text-blue-500 md:over:bg-transparent order-gray-700">Logout</button>
                                    </form>
                                </li>
                            <?php } else { ?>
                                <li>
                                    <a href="/login.php" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ext-white md:over:text-blue-500 over:bg-gray-700 over:text-blue-500 md:over:bg-transparent order-gray-700">Login</a>
                                </li>

                                <li>
                                    <a href="/register.php" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ext-white md:over:text-blue-500 over:bg-gray-700 over:text-blue-500 md:over:bg-transparent order-gray-700">Register</a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </nav>