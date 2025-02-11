<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

// Check if the user is logged
$user = getLoggedUser();

// TODO Change the part where it gets the book info
function novelGet()
{

    // fetch all books
    $db = DB::getInstance();
    $ans = $db->exec('SELECT * FROM `novel`');

    if (!is_array($ans)) {
        $ans = [];
    }

    return $ans;
}

// TODO This page will show the novels: change queries to fetch and show novels:
// - short ones are in "text" field and will be printed as is
// - for the full-length ones, the link to the download must be fetched
// The non-premium users can read just non-premium novels

$ans = novelGet();

$description = "At least Poe-try novels list";
$title = "Novels";
require_once "template/header.php"; ?>

<div class="mx-auto my-10 px-3">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-[10rem] gap-y-10">
        <?php foreach ($ans as &$novel) { ?>
            <div class="flex flex-col justify-between w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow g-gray-800 order-gray-700">
                <div>
                    <!-- TODO Adjust the way the novels are shown -->
                    <h2 class="text-lg font-semibold">
                        <?php echo p($novel['title']); ?>
                    </h2>
                    <?php if (!empty(p($novel['text']))) { ?>
                        <!-- Scrollable Short Story Container -->
                        <div class="mt-2 text-gray-600 border border-gray-300 p-2 rounded-md bg-gray-50"
                            style="height: 150px; overflow-y: auto; white-space: pre-wrap;">
                            <?php echo p($novel['text']); ?>
                        </div>
                    <?php } else { ?>
                        <!-- Display download link for full-length novel -->
                        <a href="/download.php?novel_id=<?php echo p($novel['id']); ?>.pdf" 
                            class="mt-3 inline-block text-blue-600 hover:underline">
                            Download PDF
                        </a>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
