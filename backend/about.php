<?php
require_once './lib/utils.php';
$user = getLoggedUser();

$description = "just b00k login page";
$title = "Login";
require_once "template/header.php"; ?>

<div class="flex flex-col items-center justify-center">
    <figure class="max-w-lg mx-auto group">
        <div class="relative">
            <!-- TODO: mettere immagine nostra, kek -->
            <img src="/static/prison.jpg" alt="Background" class="h-auto max-w-full rounded-lg transition duration-500 group-hover:sepia-[0.3] group-hover:contrast-[0.8]" />
            <div class="top-0 left-0 w-full h-full absolute opacity-0 transition duration-500 group-hover:opacity-80 bg-repeat bg-[length:30rem_8rem] contrast-125 drop-shadow-lg" style="background-image: url('/static/prison.jpg');"></div>
        </div>
        <figcaption class="mt-2 text-sm text-center text-gray-500 dark:text-gray-400">
            This is us
        </figcaption>
    </figure>
</div>

<?php require_once "template/footer.php"; ?>