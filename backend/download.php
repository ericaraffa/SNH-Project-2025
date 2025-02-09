<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

// TODO This page should not be necessary, because the download is requested from the homepage
// Check if the user is logged
$user = getLoggedUser();

// TODO Get novel id
$book_id = $_GET['book_id'];

if (!is_numeric($book_id)) {
    raiseNotFound();
}



// TODO fetch all novels
$db = DB::getInstance();
$query = <<<QUERY
            SELECT DISTINCT `b`.`id`, `b`.`name`
            FROM `order_book` `ob`
            INNER JOIN `book` `b` ON `ob`.`book_id` = `b`.`id`
            INNER JOIN `order` `o` ON `o`.`id` = `ob`.`order_id`
            WHERE `o`.`user_id` = :user_id AND `b`.id = :book_id
        QUERY;

// TODO select the novel 
$ans = $db->exec($query, [
    'user_id' => $user['id'],
    'book_id' => $book_id
]);

if (count($ans) === 0) {
    raiseNotFound();
}


serveFile(STORAGE . $ans[0]['id'] . '.pdf', $ans[0]['name'] . '.pdf');
