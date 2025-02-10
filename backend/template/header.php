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
                            <li>
                                <a href="/novel.php" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ext-white md:over:text-blue-500 over:bg-gray-700 over:text-blue-500 md:over:bg-transparent order-gray-700">Novels</a>
                            </li>
                            <!-- Upload is here for debugging. Only logged in users can upload or download. -->
                            <li>
                                <a href="/upload.php" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ext-white md:over:text-blue-500 over:bg-gray-700 over:text-blue-500 md:over:bg-transparent order-gray-700">Upload</a>
                            </li>
                            <?php if ($user != null) { ?>
                                <li>
                                    <a href="/bookshelf.php" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ext-white md:over:text-blue-500 over:bg-gray-700 over:text-blue-500 md:over:bg-transparent order-gray-700">Bookshelf</a>
                                </li>
                                <li>
                                    <a href="/profile.php" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ext-white md:over:text-blue-500 over:bg-gray-700 over:text-blue-500 md:over:bg-transparent order-gray-700">Profile</a>
                                </li>
                                <li>
                                    <a href="/orders.php" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ext-white md:over:text-blue-500 over:bg-gray-700 over:text-blue-500 md:over:bg-transparent order-gray-700">Orders</a>
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
                                <!--TODO Probably this can be deleted 
                                <li class="<?php echo (isset($in_checkout)) ? "hidden" : ""; ?>">
                                    <button class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ext-white md:over:text-blue-500 over:bg-gray-700 over:text-blue-500 md:over:bg-transparent order-gray-700" onclick="toggle_cart()">
                                        <div class="flex justify-center items-center">
                                            <div class="relative">
                                                <div class="t-0 absolute left-3">
                                                    <p class="flex h-2 w-2 items-center justify-center rounded-full bg-red-500 p-3 text-xs text-white" id="cart_size">
                                                        0
                                                    </p>
                                                </div>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </button>
                                </li>
                                -->
                        </ul>
                    </div>
                </div>
                <!--TODO Probably this can be deleted 
                <div class="relative z-10 hidden" id="cart_menu" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />

                    <div class="fixed inset-0 overflow-hidden">
                        <div class="absolute inset-0 overflow-hidden">
                            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                                <div class="pointer-events-auto w-screen max-w-md">
                                    <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                        <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
                                            <div class="flex items-start justify-between">
                                                <h2 class="text-lg font-medium text-gray-900" id="slide-over-title">
                                                    Shopping cart
                                                </h2>
                                                <div class="ml-3 flex h-7 items-center">
                                                    <button type="button" class="relative -m-2 p-2 text-gray-400 hover:text-gray-500" onclick="toggle_cart()">
                                                        <span class="absolute -inset-0.5" />
                                                        <span class="sr-only">Close panel</span>
                                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="mt-8">
                                                <div class="flow-root">
                                                    <ul role="list" class="-my-6 divide-y divide-gray-200" id="cart_list">
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="border-t border-gray-200 px-4 py-6 sm:px-6">
                                            <div class="flex justify-between text-base font-medium text-gray-900">
                                                <p>Subtotal</p>
                                                <p id="cart_total"></p>
                                            </div>
                                            <p class="mt-0.5 text-sm text-gray-500">
                                                Shipping and taxes calculated at checkout.
                                            </p>
                                            <div class="mt-6">
                                                <button onclick="redirectTo()" class="rounded-md border border-transparent bg-blue-600 px-6 py-3 text-base font-medium text-white shadow-sm w-full hover:bg-blue-700 disabled:cursor-not-allowed disabled:hover:none
                                        disabled:opacity-10" id="checkout_button">Checkout</button>
                                        <script>
                                            var to_redirect = "<?php echo ($user === null ? "/login.php" : "/checkout.php") ?>"
                                            const redirectTo = () => {
                                                window.location.href = to_redirect
                                            }
                                        </script>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                -->
            </nav>