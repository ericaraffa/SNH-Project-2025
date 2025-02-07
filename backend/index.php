<?php
require_once './lib/utils.php';
$user = getLoggedUser();

$description = "just b00k home page";
$title = "Home";
require_once "template/header.php"; ?>

<div class="relative max-w-5xl mx-auto">
    <div class="flex justify-center flex-col items-center">
        <img src="/static/icon.png" class="h-44 mb-10" alt="Just bOOk logo" />
        <h1
            class="text-slate-900 font-bold text-4xl sm:text-5xl lg:text-6xl tracking-tight text-center"
        >
            <code class="text-sky-500 font-mono">Just bOOk</code> - A Safe Haven
            for Book Lovers Everywhere
        </h1>
    </div>

    <p class="mt-6 text-lg text-slate-600 text-center max-w-3xl mx-auto">
        Welcome to <code class="font-mono font-medium text-sky-500"
            >JustbOOk</code
        >, the ultimate online destination for book enthusiasts who prioritize
        safety, authenticity, and a wide selection of literature. Our website is
        designed with a user-friendly interface, making your journey from
        browsing to buying both enjoyable and secure.
    </p>
    <div class="mt-6 sm:mt-10 flex justify-center text-sm">
        <a
            class="bg-slate-900 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 focus:ring-offset-slate-50 text-white font-semibold h-12 px-6 rounded-lg w-full flex items-center justify-center sm:w-auto"
            href="/book.php">Get started</a
        >
    </div>
</div>


<?php require_once "template/footer.php"; ?>