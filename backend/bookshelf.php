<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

// Check if the user is logged
$user = getLoggedUser();

// User not authenticated, page not found
if ($user == null) {
    raiseNotFound();
}

// TODO This page will show the novels: change queries to fetch and show novels:
// - short ones are in "text" field and will be printed as is
// - for the full-length ones, the link to the download must be fetched
$db = DB::getInstance();
$query = <<<QUERY
    SELECT DISTINCT
        `b`.`id`, `b`.`name`, `b`.`picture`
    FROM `order_book` `ob`
    INNER JOIN `book` `b` ON `ob`.`book_id` = `b`.`id`
    INNER JOIN `order` `o` ON `o`.`id` = `ob`.`order_id`
    WHERE `o`.`user_id` = :user_id
QUERY;
$ans = $db->exec($query, [
    'user_id' => $user['id']
]);

$description = "At least Poe-try homepage";
$title = "Homepage";
require_once "template/header.php"; ?>

<?php if (count($ans) === 0) { ?>
    <p class="text-2xl font-bold text-center">Are you even poe-trying?</p>
<?php } else { ?>

    <!-- Grid with the novels -->
    <div class="mx-auto my-10 px-3">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-[10rem] gap-y-10">
            <?php foreach ($ans as &$book) { ?>
                <div class="flex flex-col justify-between w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow g-gray-800 order-gray-700">
                    <div>
                        <a href="/book.php?book_id=<?php echo $book['id']; ?>" class="flex">
                            <img class="p-3 rounded-t-lg mx-auto my-auto h-[25rem]" src="/static/books/<?php echo p($book['picture']); ?>" alt="novel" aria-label="<?php echo p($book['name']); ?>" />
                        </a>
                        <div class="px-5 pb-5 block">
                            <h5 class="text-xl font-semibold tracking-tight text-gray-900 ext-white">
                                <?php echo p($book['name']); ?>
                            </h5>
                        </div>
                    </div>
                    <div>
                        <div class="px-5 pb-5 flex justify-center">
                            <a href="/download.php?book_id=<?php echo $book['id']; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-8 h-8" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>