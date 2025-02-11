<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

// Check if the user is logged
$user = getLoggedUser();

// TODO Delete this page
function bookGet()
{

    // 
    if (isset($_GET["book_id"])) {
        $book_id = $_GET["book_id"];
        if (!is_numeric($book_id)) {
            raiseNotFound();
        }

        $db = DB::getInstance();
        $ans = $db->exec('SELECT * FROM `book` WHERE `id` = :id', [
            'id' => $book_id
        ]);

        if (count($ans) === 0) {
            raiseNotFound();
        }

        return [$ans[0]];
    }

    // fetch all books
    $db = DB::getInstance();
    $ans = $db->exec('SELECT * FROM `book`');

    if (!is_array($ans)) {
        $ans = [];
    }

    return $ans;
}

// TODO This page will show the novels: change queries to fetch and show novels:
// - short ones are in "text" field and will be printed as is
// - for the full-length ones, the link to the download must be fetched
// The non-premium users can read just non-premium novels

$ans = bookGet();

if (count($ans) === 1) {
    $description = p($ans[0]['name']);
    $title = p($ans[0]['name']);
} else {
    $description = "At least Poe-try novels list";
    $title = "Novels";
}
require_once "template/header.php"; ?>

<!-- TODO Delete the part where it shows the book info
<?php if (count($ans) === 1) {
    $book = $ans[0]; ?>
    <div class="lg:mx-20 md:mx-10 my-10 px-3">
        <div class="grid lg:grid-cols-3 md:grid-cols-1 gap-4">
            <div>
                <img class="mx-auto my-auto h-[30rem] border-2 border-black" src="/static/books/<?php echo p($book['picture']); ?>" alt="cover of book" aria-label="Book cover for <?php echo p($book['name']); ?>" />
            </div>
            <div class="lg:col-span-2">
                <h1 class="mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 md:text-5xl lg:text-6xl ext-white">
                    <?php echo p($book['name']); ?>
                </h1>

                <div class="my-1">
                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-1.5 rounded g-blue-200 ext-blue-800">From: <?php echo p($book['author']); ?></span>
                </div>

                <div class="my-1">
                    <?php foreach (explode(", ", $book['genre']) as &$x) { ?>
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded g-blue-200 ext-blue-800 my-10 mr-1">
                            <?php echo p($x); ?>
                        </span>
                    <?php } ?>
                </div>

                <?php foreach (explode("\n", $book['description']) as &$x) { ?>
                    <p class="mb-3 text-gray-500 ext-gray-400"><?php echo p($x); ?></p>
                <?php } ?>


                <div class="flex flex-row justify-end my-3 gap-3">
                    <span class="text-3xl font-bold text-gray-900 ext-white">$<?php printf("%.2f", $book['price'] / 100); ?></span>
                    <button class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center g-blue-600 over:bg-blue-700 ocus:ring-blue-800" onclick="add_cart()">Add to cart</button>
                    <input type="hidden" id="book_id" value="<?php echo $book['id']; ?>">
                    <input type="hidden" id="book_name" value="<?php echo p($book['name']); ?>">
                    <input type="hidden" id="book_price" value="<?php echo p($book['price']); ?>">
                    <input type="hidden" id="book_author" value="<?php echo p($book['author']); ?>">
                    <input type="hidden" id="book_picture" value="<?php echo p($book['picture']); ?>">
                </div>
            </div>
        </div>

    </div>
<?php } else { ?>
    -->
    <div class="mx-auto my-10 px-3">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-[10rem] gap-y-10">
            <?php foreach ($ans as &$book) { ?>
                <div class="flex flex-col justify-between w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow g-gray-800 order-gray-700">
                    <div>
                        <a href="/book.php?book_id=<?php echo $book['id']; ?>" class="flex">
                            <img class="p-8 rounded-t-lg mx-auto my-auto h-[30rem]" src="/static/books/<?php echo p($book['picture']); ?>" alt="cover of book" aria-label="Book cover for <?php echo p($book['name']); ?>" />
                        </a>
                        <div class="px-5 pb-5 block">
                            <h5 class="text-xl font-semibold tracking-tight text-gray-900 ext-white">
                                <?php echo p($book['name']); ?>
                            </h5>
                            <div class="flex items-center mt-2.5 mb-5">
                                <?php foreach (explode(", ", $book['genre']) as &$x) { ?>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded g-blue-200 ext-blue-800 mr-1">
                                        <?php echo p($x); ?>
                                    </span>
                                <?php } ?>
                            </div>
                            <p class="mb-3 text-gray-500 ext-gray-400">
                                <?php echo p(substr($book['description'], 0, 100)) . "..."; ?>
                            </p>
                        </div>
                    </div>
                    <div class="px-5 pb-5">
                        <span class="text-3xl font-bold text-gray-900 ext-white">$<?php printf("%.2f", $book['price'] / 100); ?></span>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <!--- 
<?php } ?>
-->
