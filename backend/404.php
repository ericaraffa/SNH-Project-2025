<?php
require_once './lib/utils.php';
$user = getLoggedUser();

// TODO Change description
$description = "just b00k page not found";
$title = "Page not found!";
require_once "template/header.php"; ?>

<div class="relative max-w-5xl mx-auto">
    <div class="flex justify-center flex-col items-center">
        <h1
            class="text-slate-900 font-bold text-4xl sm:text-5xl lg:text-6xl tracking-tight text-center"
        >
            <code class="text-sky-500 font-mono">Error</code> - What you were looking for is not here!
        </h1>
    </div>

</div>


<?php require_once "template/footer.php"; ?>