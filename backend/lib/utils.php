<?php

    /*
     * This file is used to store common methods and constant value across
     * all the application
     */

    require_once __DIR__ . '/../../vendor/autoload.php';
    use SendGrid\Mail\Mail;

    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');

    // if server is in DEV mode, on preflight requests return some CORS headers to
    // enable separate backend and frontend container
    if(getenv('DEV')){
        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 86400');

        if(strcmp($_SERVER['REQUEST_METHOD'], 'OPTIONS') === 0){
            die();
        }
    }

    // Push security headers
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');    

    // This should be created in an offline folder
    define('STORAGE',  '/var/www/html/ebooks/');
    define('OK', 200);
    define('BAD_REQUEST', 400);
    define('UNAUTHORIZED', 401);
    define('METHOD_NOT_ALLOWED', 405);
    define('NOT_FOUND', 404);
    define('INTERNAL_SERVER_ERROR', 500);

    // Exit if the page is requested directly instead of being imported
    function exitIfRequested($callingFile){
        if (strcasecmp(str_replace('\\', '/', $callingFile), $_SERVER['SCRIPT_FILENAME']) == 0) {
            http_response_code(NOT_FOUND);
            exit();
        }
    }
    exitIfRequested(__FILE__);

    // These function are needed to throw errors with the corresponding response code
    function throwDatabaseError(){
        http_response_code(500);
        exit('Database Error, please contact the administrator');
    }

    function raiseOK(){
        http_response_code(OK);
        die();
    }

    function raiseUnauthorized(){
        http_response_code(UNAUTHORIZED);
        die();
    }

    function raiseMethodNotAllowed(){
        http_response_code(METHOD_NOT_ALLOWED);
        die();
    }

    function raiseBadRequest(){
        http_response_code(BAD_REQUEST);
        die();
    }

    function raiseNotFound(){
        http_response_code(NOT_FOUND);
        die();
    }

    //Check if user is logged
    function getLoggedUser(){

        // Get authorization header
        if(!isset($_COOKIE['session'])){
            return null;
        }
        $auth = $_COOKIE['session'];

        if(!is_string($auth)){
            return null;
        }

        // Check for token in database
        require_once 'DB.php';

        $db = DB::getInstance();
        $ans = $db->exec('SELECT * FROM `session` WHERE `token` = :token', [
            'token' => $auth
        ]);

        // If nothing is found, the user in not logged
        if(count($ans) === 0){
            return false;
        }

        // Take the session token
        $session = $ans[0];

        // Check if the session is expired
        if (strtotime($session['valid_until']) < time()) {

            // If it is expired, delete the token from the database
            $db->exec('DELETE FROM `session` WHERE `id` = :id', [
                'id' => $session['id']
            ]);

            // Reset session token in the cookie and redirect to login page
            setcookie("session", "", time() - 3600, "/");
            setcookie("csrf_token", "", time() - 3600, "/");
            header("Location: /login.php");
            die();
        }
    
        // Otherwise, fetch user data
        $user = $db->exec('SELECT * FROM `user` WHERE `id` = :id', [
            'id' => $session['user_id']
        ]);

        // Check if the session is associated to a valid user
        if(count($user) === 0){
            security_log("Correct session {$session['token']} has no valid user {$session['user_id']} associated");
            return false;
        }

        // Done here in order to update cookies before rendering stuff
        get_csrf_token();

        return $user[0];
    }

    // Redirect the user to the homepage
    function redirect_authenticated(){
        header("Location: /bookshelf.php");
        die();
    }

    // Sanitize password
    function checkPassword($password){
        if(strlen($password) < 8 || !preg_match("/[a-z]/", $password) ||
            !preg_match("/[A-Z]/", $password) || !preg_match("/\d/", $password) ||
            !preg_match("/\W|_/", $password)){
            return false;
        }
        return true;
    }

    // TODO We can take the following functions as examples to check user inputs
    function checkCreditCardNumber($number){
        return is_string($number) && preg_match("/^\d{16}$/", $number);
    }

    function checkCreditCardExpirationDate($date){
        return is_string($date) && preg_match("/^\d{2}\/\d{2}$/", $date);
    }

    function checkCreditCardCVV($cvv){
        return is_string($cvv) && preg_match("/^\d{3}$/", $cvv);
    }

    function checkEmail($email){
        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    function checkCart($cart){
        if(!is_array($cart)){
            return false;
        }

        if(count($cart) === 0){
            return false;
        }

        if(count($cart) > 10){
            // to avoid DoS
            return false;
        }

        foreach($cart as $item){
            if(!is_array($item) || !isset($item['book_id']) || !isset($item['quantity'])){
                return false;
            }
            if(!is_numeric($item['book_id']) || !is_numeric($item['quantity']) || $item['quantity'] < 1){
                return false;
            }
        }

        return true;
    }

    function performPayment($total, $credit_card_number, $credit_card_expiration_date, $credit_card_cvv){
        return true;
    }

    // Check if the request method is POST
    function isPost(){
        return strcmp($_SERVER['REQUEST_METHOD'], 'POST') === 0;
    }

    // Check if the request method is GET
    function isGet(){
        return strcmp($_SERVER['REQUEST_METHOD'], 'GET') === 0;
    }

    // Use this function to print user inputs, avoiding XSS
    function p($string){
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    // Check if a string is an integer number
    function isNumber($str){
        return is_string($str) && preg_match("/^-?\d{1,}$/", $str);
    }

    // Common procedure to serve file via HTTP
    function serveFile($path, $filename){
        
        // Open the file and suppress warnings
        $fp = @fopen($path, 'r');

        // Check if fopen worked
        if($fp === FALSE){
            raiseNotFound();
        }

        // Set content type to serve the file
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Send the file content
        fpassthru($fp);
        fclose($fp);
        exit();
    }

    // Send email to user
    function send_mail($to, $object, $content, $content_type="text/plain"){
        $SENDGRID_API_KEY = getenv('SENDGRID_API_KEY');
        $GMAIL_EMAIL = getenv('GMAIL_EMAIL');

        if(!$SENDGRID_API_KEY){
            error_log('Missing SENDGRID_API_KEY env variable');
            return false;
        }
    
        if(!$GMAIL_EMAIL){
            error_log('Missing GMAIL_EMAIL env variable');
            return false;
        }
      
        // Create email
        $sendgrid = new \SendGrid($SENDGRID_API_KEY); #
        $email = new Mail(); #
        $email->setFrom($GMAIL_EMAIL, "SNH Project");
        $email->setSubject($object);
        $email->addTo($to);
        $email->addContent($content_type, $content);
      
        // Try to send the email
        try {
            $sendgrid->send($email);
        } catch (Exception $e) {
            security_log("Couldn't send email, error: {$e->getMessage()}");
            return false;
        }

        return true;
    }

    // Check or update the session token 
    function get_csrf_token(){
        if(!isset($_COOKIE['csrf_token'])){
            return set_csrf_token();
        }
        return $_COOKIE['csrf_token'];
    }

    // Set and return a new session token
    function set_csrf_token(){
        $csrf_token = bin2hex(random_bytes(32));
        setcookie("csrf_token", $csrf_token, time() + 30 * 24 * 60 * 60, "/", "", true, true);
        $_COOKIE['csrf_token'] = $csrf_token;
        return $csrf_token;
    }

    // Check if the session token is set
    function check_csrf($csrf_token) {
        if(!isset($_COOKIE['csrf_token'])){
            return false;
        }
        return $_COOKIE['csrf_token'] === $csrf_token;
    }

    // Log for security purpose
    function security_log($msg){
        $msg = sprintf("[%s] %s\n", date("Y-m-d H:i:s"), $msg);
        error_log($msg, 3, "/var/log/nginx/security.log");
    }
?>