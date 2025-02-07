<?php
require_once './lib/utils.php';

$user = getLoggedUser();

$book_id = $_GET['book_id'];

if (!is_numeric($book_id)) {
    raiseNotFound();
}

require_once './lib/DB.php';

// fetch all books
$db = DB::getInstance();
$query = <<<QUERY
            SELECT DISTINCT `b`.`id`, `b`.`name`
            FROM `order_book` `ob`
            INNER JOIN `book` `b` ON `ob`.`book_id` = `b`.`id`
            INNER JOIN `order` `o` ON `o`.`id` = `ob`.`order_id`
            WHERE `o`.`user_id` = :user_id AND `b`.id = :book_id
        QUERY;

$ans = $db->exec($query, [
    'user_id' => $user['id'],
    'book_id' => $book_id
]);

if (count($ans) === 0) {
    raiseNotFound();
}

serveFile(STORAGE . $ans[0]['id'] . '.pdf', $ans[0]['name'] . '.pdf');
