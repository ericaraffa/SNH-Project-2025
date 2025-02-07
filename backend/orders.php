<?php
require_once './lib/utils.php';
$user = getLoggedUser();

if ($user == null) {
    raiseNotFound();
}

function fetchOrder($user_id, $order_id)
{
    require_once './lib/DB.php';
    $db = DB::getInstance();

    $ans = $db->exec('SELECT * FROM `order` WHERE `id` = :id AND `user_id` = :user_id', [
        'id' => $order_id,
        'user_id' => $user_id,
    ]);

    if (count($ans) === 0) {
        return null;
    }

    $order = $ans[0];

    $query = <<<QUERY
        SELECT `b`.`id`, `b`.`name`, `b`.`author`, `b`.`genre`, `b`.`picture`, `b`.`description`, `ob`.`quantity`
        FROM `order_book` `ob`
        INNER JOIN `book` `b` ON `ob`.`book_id` = `b`.`id`
        WHERE `ob`.`order_id` = :order_id
    QUERY;

    $ans = $db->exec($query, [
        'order_id' => $order_id
    ]);

    $items = [];

    foreach ($ans as $item) {
        $items[] = [
            'book_id' => $item['id'],
            'name' => $item['name'],
            'author' => $item['author'],
            'genre' => $item['genre'],
            'picture' => $item['picture'],
            'description' => $item['description'],
            'quantity' => $item['quantity']
        ];
    }

    return [
        "order_id" => $order_id,
        "total" => $order['total'],
        "items" => $items
    ];
}

function orderGet($user)
{
    if (isset($_GET["order_id"])) {
        $order_id = $_GET["order_id"];
        if (!is_numeric($order_id)) {
            raiseNotFound();
        }

        $order = fetchOrder($user['id'], $order_id);

        if ($order === null) {
            raiseNotFound();
        }

        return $order;
    }

    require_once './lib/DB.php';

    // fetch last 10 orders of user
    $db = DB::getInstance();
    $ans = $db->exec('SELECT * FROM `order` WHERE `user_id` = :user_id LIMIT 10', [
        'user_id' => $user['id']
    ]);

    if (!is_array($ans)) {
        $ans = [];
    }

    return $ans;
}

$ans = orderGet($user);

if (isset($_GET["order_id"])) {
    $description = "just b00k order n " . p($ans['order_id']);
    $title = "Order " . p($ans['order_id']);
} else {
    $description = "just b00k book list";
    $title = "Books";
}
require_once "template/header.php"; ?>

<div class="container px-3 mx-auto">


<?php if (count($ans) === 0) { ?>
    <h1 class="mb-4 text-3xl font-extrabold leading-none tracking-tight text-gray-900 md:text-5xl lg:text-5xl ext-white">
        No orders
    </h1>
    <p class="text-2xl font-bold text-center"></p>
<?php } else if (isset($ans['order_id'])) {
    $order = $ans[0]; ?>
    <div class="grid grid-cols-1 gap-4">
        <div>
            <h1 class="mb-4 text-3xl font-extrabold leading-none tracking-tight text-gray-900 md:text-5xl lg:text-5xl ext-white">
                Order #<?php echo $ans["order_id"]; ?>
            </h1>
        </div>
        <?php foreach ($ans['items'] as &$item) { ?>
            <div class="flex flex-row m-10">
                <div class="mr-10">
                    <a href="/book.php?book_id=<?php echo $item["book_id"]; ?>">
                        <img class="mx-auto my-auto h-[20rem] border-2 border-black" src="/static/books/<?php echo p($item['picture']); ?>" alt="cover of book" aria-label="Book cover for <?php echo p($item['name']); ?>" />
                    </a>
                </div>
                <div class="lg:col-span-2">
                    <a href="/book.php?book_id=<?php echo $item["book_id"]; ?>" class="mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 md:text-5xl lg:text-6xl ext-white">
                        <?php echo p($item['name']); ?>
                    </a>

                    <div class="my-3">
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-1.5 rounded g-blue-200 ext-blue-800">From: <?php echo p($item['author']); ?></span>
                    </div>

                    <div class="my-1">
                        <?php foreach (explode(", ", $item['genre']) as &$x) { ?>
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded g-blue-200 ext-blue-800 my-10 mr-1">
                                <?php echo p($x); ?>
                            </span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="flex flex-row justify-end">
            <h1 class="mb-4 text-3xl font-extrabold leading-none tracking-tight text-gray-900 md:text-5xl lg:text-5xl ext-white">
                $<?php printf("%.2f", $ans['total'] / 100); ?>
            </h1>
        </div>
    </div>

<?php } else { ?>  
    <div class="mt-10 grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-x-[10rem] gap-y-10">
        <?php foreach ($ans as &$order) { ?>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-x-[10rem] gap-y-10 bg-white border border-gray-200 rounded-lg p-5">
                <div class="flex flex-row place-content-between">
                    <p class="mr-10">Shipping to:</p>
                    <div>
                        <p class="w-full text-right"><?php echo p($order['shipping_address']); ?></p>
                        <p class="w-full text-right"><?php echo p($order['shipping_city']); ?></p>
                        <p class="w-full text-right"><?php echo p($order['shipping_state']); ?></p>
                    </div>
                </div>
                <div class="flex flex-row">
                    <h4 class="text-3xl font-extrabold ext-white">
                        total: $<?php printf("%.2f", $order['total'] / 100); ?>
                    </h4>
                </div>
                <div>
                    <a href="/orders.php?order_id=<?php echo $order['id']; ?>" class="inline-flex items-center text-lg text-blue-600 ext-blue-500 hover:underline">
                        Go to order
                        <svg class="w-3.5 h-3.5 ms-2 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
                        </svg>
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } ?>

</div>



<?php require_once "template/footer.php"; ?>