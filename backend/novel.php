<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

// Check if the user is logged
$user = getLoggedUser();

// User not authenticated, page not found
if ($user == null) {
    raiseNotFound();
}

function novelGet()
{

    // fetch all novels
    $db = DB::getInstance();
    $ans = $db->exec('SELECT * FROM `novel`');

    if (!is_array($ans)) {
        $ans = [];
    }

    return $ans;
}

// This page will show the novels: change queries to fetch and show novels:
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
            <!-- Check of the premium users -->
            <?php if( ($user['premium']) || ($novel['premium'] === $user['premium']) ) { ?>
                <div class="flex flex-col justify-between w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow g-gray-800 order-gray-700">
                    <div>
                        <!-- Novel title -->
                        <h2 class="text-lg font-semibold">
                            <?php echo p($novel['title']); ?>
                        </h2>
                        <?php if (!empty(p($novel['text']))) { ?>
                            <!-- Scrollable Short Story Container -->
                            <div class="mt-2 text-gray-600 border border-gray-300 p-2 rounded-md bg-gray-50"
                                style="height: 150px; overflow-y: auto;">
                                <?php echo p($novel['text']); ?>
                            </div>
                        <?php } else { ?>
                            <!-- Display download link for full-length novel -->
                            <a href="/download.php?novel_id=<?php echo p($novel['id']); ?>.pdf&novel_title=<?php echo p($novel['title']); ?>" 
                                class="mt-3 inline-block text-blue-600 hover:underline">
                                Download PDF
                            </a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>
