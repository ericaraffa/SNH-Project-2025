<?php
require_once './lib/utils.php';
$user = getLoggedUser();

$in_checkout=true;

if ($user == null) {
    raiseNotFound();
}

function orderPost($user)
{
    if (!isset($_POST["csrf_token"]) || !check_csrf($_POST["csrf_token"])) {
        return [
            "error" => "Invalid CSRF token",
        ];
    }

    if (!isset($_POST['cart']) || !isset($_POST['credit_card_number']) || !isset($_POST['credit_card_expiration_date']) || !isset($_POST['credit_card_cvv']) || !isset($_POST['shipping_address']) || !isset($_POST['shipping_city']) || !isset($_POST['shipping_state'])) {
        return [
            "error" => "Invalid data",
        ];
    }

    $cart = $_POST['cart'];
    $credit_card_number = $_POST['credit_card_number'];
    $credit_card_expiration_date = $_POST['credit_card_expiration_date'];
    $credit_card_cvv = $_POST['credit_card_cvv'];
    $shipping_address = $_POST['shipping_address'];
    $shipping_city = $_POST['shipping_city'];
    $shipping_state = $_POST['shipping_state'];

    // check types
    if (!checkCreditCardNumber($credit_card_number)) {
        return ["error" => "Invalid credit card number"];
    }
    if (!checkCreditCardExpirationDate($credit_card_expiration_date)) {
        return ["error" => "Invalid credit card expiration date"];
    }
    if (!checkCreditCardCVV($credit_card_cvv)) {
        return ["error" => "Invalid credit card CVV"];
    }
    if (!checkCart($cart)) {
        return ["error" => "Invalid cart"];
    }
    if (!is_string($shipping_address)) {
        return ["error" => "Invalid shipping address"];
    }
    if (!is_string($shipping_city)) {
        return ["error" => "Invalid shipping city"];
    }
    if (!is_string($shipping_state)) {
        return ["error" => "Invalid shipping state"];
    }

    require_once './lib/DB.php';
    $db = DB::getInstance();

    // check if user has enough money
    $total = 0;
    foreach ($cart as $item) {
        $ans = $db->exec('SELECT * FROM `book` WHERE `id` = :id', [
            'id' => $item['book_id']
        ]);

        if (count($ans) === 0) {
            return [
                "error" => "Invalid cart!",
            ];
        }

        $book = $ans[0];

        $total += $book['price'] * $item['quantity'];
    }

    if (!performPayment($total, $credit_card_number, $credit_card_expiration_date, $credit_card_cvv)) {
        return [
            "error" => "Payment failed",
        ];
    }

    // add order to db
    $ans = $db->exec('INSERT INTO `order` (`user_id`, `total`, `shipping_address`, `shipping_city`, `shipping_state`) VALUES (:user_id, :total, :shipping_address, :shipping_city, :shipping_state)', [
        'user_id' => $user['id'],
        'total' => $total,
        'shipping_address' => $shipping_address,
        'shipping_city' => $shipping_city,
        'shipping_state' => $shipping_state
    ]);

    $order_id = $db->lastInsertId();

    // add books to order
    foreach ($cart as $item) {
        $ans = $db->exec('INSERT INTO `order_book` (`order_id`, `book_id`, `quantity`) VALUES (:order_id, :book_id, :quantity)', [
            'order_id' => $order_id,
            'book_id' => $item['book_id'],
            'quantity' => $item['quantity']
        ]);
    }

    return [
        "msg" => "Checkout completed!",
        "id" =>  $order_id
    ];

}

if (isPost()) {
    $ans = orderPost($user);
}

$description = "just b00k profile page";
$title = "Change password";
require_once "template/header.php"; ?>

<?php if (isset($ans["msg"])) { ?>
    <div class="flex items-center justify-center flex-col text-lg">
        <p class="my-2 text-sm" id="msg">
            <?php echo $ans["msg"]; ?>
        </p>
        <a class="no-underline hover:underline text-blue-500 text-lg" href="/orders.php?order_id=<?php echo $ans["id"];?>">see order</a>
    </div>
    
    <script>
        window.onload = () => {
            cart.set([]);
        };
    </script>

<?php } else { ?>


    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto my-auto lg:py-0">
        <div class="w-full order md:mt-0 sm:max-w-md xl:p-0 g-gray-800 order-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl ext-white">
                    Fill the form to checkout
                </h1>

                <form id="myForm" class="space-y-4 md:space-y-6" action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    
                    <div id="first_step" class="p-4 bg-white rounded-lg shadow bg-white rounded-lg shadow">
                        <!-- Other content -->
                        <div>
                            <label for="credit_card_number" class="block mb-2 text-sm font-medium text-gray-900">Credit card number</label>
                            <input type="text" name="credit_card_number" id="credit_card_number" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5" placeholder="***1234" required="" pattern="\d{13,16}" />
                        </div>
                        <div>
                            <label for="credit_card_expiration_date" class="block mb-2 text-sm font-medium text-gray-900">Credit card expiration</label>
                            <input type="text" name="credit_card_expiration_date" id="credit_card_expiration_date" placeholder="12/05" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5" required="" pattern="(0[1-9]|1[0-2])\/\d{2}" />
                        </div>
                        <div>
                            <label for="credit_card_cvv" class="block mb-2 text-sm font-medium text-gray-900">Credit card CVV</label>
                            <input type="text" name="credit_card_cvv" id="credit_card_cvv" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5" placeholder="123" required="" pattern="\d{3,4}" />
                        </div>
                    </div>
                    <div id="second_step" class="p-4 bg-white rounded-lg shadow bg-white rounded-lg shadow">
                        <div>
                            <label for="shipping_address" class="block mb-2 text-sm font-medium text-gray-900">Shipping address</label>
                            <input type="text" name="shipping_address" id="shipping_address" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5" placeholder="Via le mani dal naso" required="" pattern="[A-Za-z0-9\s,\.]+" />
                        </div>
                        <div>
                            <label for="shipping_city" class="block mb-2 text-sm font-medium text-gray-900">Shipping City</label>
                            <input type="text" name="shipping_city" id="shipping_city" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5" placeholder="Pisa" required="" pattern="[A-Za-z\s]+" />
                        </div>
                        <div>
                            <label for="shipping_state" class="block mb-2 text-sm font-medium text-gray-900">Shipping State</label>
                            <input type="text" name="shipping_state" id="shipping_state" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5" placeholder="Italy" required="" pattern="[A-Za-z\s]+" />
                        </div>
                    </div>

                    <?php if (isset($ans["error"])) { ?>
                        <p class="mt-2 text-sm text-red-600 ext-red-500" id="current_password_error_box">
                            <?php echo $ans["error"]; ?>
                        </p>
                    <?php } ?>

                    <div id="third_step" class="p-4 bg-white rounded-lg shadow bg-white rounded-lg shadow">
                        <div class="bg-gray-100 p-5 rounded-lg mb-5">
                            <div class="mb-1">Order recap:</div>    
                            <div class="mb-1">----</div>    
                            <div id="cart_checkout"></div>
                            <div class="my-1">----</div>  
                            <div id="cart_checkout_total"></div>  
                        </div>
                        
                        <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center g-blue-600 over:bg-blue-700 ocus:ring-blue-800 flex flex-col justify-center items-center button">
                            <img src="data:image/png;base64,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" alt="" />
                        </button>
                    </div>
                    <div class="flex justify-between">
                        <div>
                            &nbsp;
                            <button id="prev" type="button" class="rounded-md border border-transparent bg-blue-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-blue-700">
                                Previous
                            </button>
                        </div>
                        <button id="next" type="button" class="rounded-md border border-transparent bg-blue-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-blue-700">
                            Next
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        const first_step = document.getElementById("first_step");
        const second_step = document.getElementById("second_step");
        const third_step = document.getElementById("third_step");
        const steps = [first_step, second_step, third_step];

        const next = document.getElementById("next");
        const prev = document.getElementById("prev");
        

        const report = (el) => {
            let res = true;
            el.querySelectorAll('input, select, textarea').forEach(element => {
                if (!element.willValidate) return;

                if (!element.reportValidity()) res = false;
            });
            return res;

        }

        const renderStep = () => {
            if (step == 0){
                first_step.classList.remove("hidden");
                second_step.classList.add("hidden");
                third_step.classList.add("hidden");
                
                prev.classList.add("hidden");
                next.classList.remove("hidden");
                return;
            }
            if (step == 1){
                first_step.classList.add("hidden");
                second_step.classList.remove("hidden");
                third_step.classList.add("hidden");
                
                prev.classList.remove("hidden");
                next.classList.remove("hidden");
                return;
            }
            if (step == 2){
                first_step.classList.add("hidden");
                second_step.classList.add("hidden");
                third_step.classList.remove("hidden");

                prev.classList.remove("hidden");
                next.classList.add("hidden");
                return;
            }
        }

        
        next.onclick = () => {
            if (!report(steps[step])) return;
            step+=1;
            renderStep();
        }
        prev.onclick = () => {
            step-=1;
            renderStep();
        }

        let step = 0;
        renderStep();

    </script>

<?php }?>

<?php require_once "template/footer.php"; ?>