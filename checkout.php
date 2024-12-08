<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// SMTP settings
$mailHost = 'smtp.rackhost.hu';
$mailUsername = 'pixelforge@shador.hu';
$mailPassword = 'Keszenallok01';
$mailPort = 465;

$servername = "localhost";
$username = "root";
$password = "Keszenallok01!";
$dbname = "user_auth";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// Check for logged-in user
$isLoggedIn = false;
$username = '';
if (isset($_COOKIE['auth_token'])) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE token = ?");
    $stmt->bind_param("s", $_COOKIE['auth_token']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $username = htmlspecialchars($user['username']);
        $isLoggedIn = true;
    }
}

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$errors = [];
$success_message = '';

// Process the order when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        $customer_details = [
            'full_name' => $_POST['full_name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'address' => $_POST['address'],
            'city' => $_POST['city'],
            'zip' => $_POST['zip'],
            'country' => $_POST['country'],
        ];
        
        if (!isset($_POST['same_shipping'])) {
            $customer_details['shipping_address'] = $_POST['shipping_address'];
            $customer_details['shipping_city'] = $_POST['shipping_city'];
            $customer_details['shipping_zip'] = $_POST['shipping_zip'];
            $customer_details['shipping_country'] = $_POST['shipping_country'];
        }
        
        setcookie('customer_details', json_encode($customer_details), time() + 3600, '/');
        header('Location: checkout.php?step=2');
        exit;
    } elseif ($step === 2) {
        if (!isset($_POST['delivery_method'])) {
            $errors[] = "Kérjük, válasszon szállítási módot.";
        } else {
            setcookie('delivery_method', $_POST['delivery_method'], time() + 3600, '/');
            header('Location: checkout.php?step=3');
            exit;
        }
    } elseif ($step === 3) {
        if (!isset($_POST['payment_method'])) {
            $errors[] = "Kérjük, válasszon fizetési módot.";
        } else {
            $payment_method = $_POST['payment_method'];
            setcookie('payment_method', $payment_method, time() + 3600, '/');
            
            // Process the order
            if (isset($_POST['place_order'])) {
                $customer_details = isset($_COOKIE['customer_details']) ? json_decode($_COOKIE['customer_details'], true) : null;
                $delivery_method = isset($_COOKIE['delivery_method']) ? $_COOKIE['delivery_method'] : null;
                
                if (!$customer_details || !$delivery_method) {
                    $errors[] = "Hiányzó rendelési adatok. Kérjük, kezdje újra a folyamatot.";
                } else {
                    // Get cart items
                    $cart_items = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
                    
                    // Create order data
                    $order_data = [
                        'customer_details' => $customer_details,
                        'delivery_method' => $delivery_method,
                        'payment_method' => $payment_method,
                        'cart_items' => $cart_items,
                        'order_total' => calculateOrderTotal($delivery_method, $payment_method, $cart_items)
                    ];

                    // Save to database
                    $sql = "INSERT INTO customer_orders (customer_name, customer_email, order_data, order_total, order_status) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $order_json = json_encode($order_data);
                    $orderStatus = 'pending';
                    
                    $stmt->bind_param("sssds", 
                        $customer_details['full_name'],
                        $customer_details['email'],
                        $order_json,
                        $order_data['order_total'],
                        $orderStatus
                    );

                    if ($stmt->execute()) {
                        $order_id = $conn->insert_id;
                        
                        // Send confirmation email
                        if (sendOrderConfirmationEmail($customer_details['email'], $customer_details['full_name'], $order_id, $order_data)) {
                            $success_message = "Rendelés sikeresen leadva. Visszaigazoló e-mail elküldve.";
                            
                            // Clear cart and order data from cookies
                            setcookie('cart', '', time() - 3600, '/');
                            setcookie('customer_details', '', time() - 3600, '/');
                            setcookie('delivery_method', '', time() - 3600, '/');
                            setcookie('payment_method', '', time() - 3600, '/');
                            
                            // Redirect to thank you page
                            header("Location: thank_you.php?order_id=" . $order_id);
                            exit;
                        } else {
                            $errors[] = "Hiba történt a visszaigazoló e-mail küldése során.";
                        }
                    } else {
                        $errors[] = "Hiba történt a rendelés mentése során: " . $stmt->error;
                    }
                }
            }
        }
    }
}

function sendOrderConfirmationEmail($customerEmail, $customerName, $order_id, $order_data) {
    global $mailHost, $mailUsername, $mailPassword, $mailPort;
    
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $mailHost;
        $mail->SMTPAuth = true;
        $mail->Username = $mailUsername;
        $mail->Password = $mailPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $mailPort;

        $mail->CharSet = 'UTF-8';

        $mail->setFrom($mailUsername, 'PixelForge');
        $mail->addAddress($customerEmail, $customerName);

        $mail->isHTML(true);
        $mail->Subject = 'Rendelés visszaigazolás - PixelForge';
        
        // Load and customize HTML email template
        $html_template = file_get_contents('email-template.html');
        $html_template = str_replace('{CUSTOMER_NAME}', htmlspecialchars($customerName), $html_template);
        $html_template = str_replace('{ORDER_ID}', $order_id, $html_template);
        
        // Generate order items HTML
        $order_items_html = '';
        $cart_items = $order_data['cart_items'];
        foreach ($cart_items as $productId => $quantity) {
            $product = getProducts(1, false, null, $productId)[0];
            $order_items_html .= "<tr><td>{$product['name']}</td><td>{$quantity}</td><td>{$product['price']} Ft</td></tr>";
        }
        $html_template = str_replace('{ORDER_ITEMS}', $order_items_html, $html_template);
        
        $html_template = str_replace('{TOTAL_AMOUNT}', number_format($order_data['order_total'], 0, ',', ' '), $html_template);
        $html_template = str_replace('{SHIPPING_NAME}', htmlspecialchars($order_data['customer_details']['full_name']), $html_template);
        $html_template = str_replace('{SHIPPING_ADDRESS}', htmlspecialchars($order_data['customer_details']['address']), $html_template);
        $html_template = str_replace('{SHIPPING_CITY}', htmlspecialchars($order_data['customer_details']['city']), $html_template);
        $html_template = str_replace('{SHIPPING_ZIP}', htmlspecialchars($order_data['customer_details']['zip']), $html_template);
        $html_template = str_replace('{SHIPPING_COUNTRY}', htmlspecialchars($order_data['customer_details']['country']), $html_template);
        $html_template = str_replace('{PAYMENT_METHOD}', getPaymentMethodName($order_data['payment_method']), $html_template);
        
        $mail->Body = $html_template;
        $mail->AltBody = "Kedves {$customerName},\n\nKöszönjük a rendelését! A rendelés azonosítója: {$order_id}. A rendelés összege: {$order_data['order_total']} Ft.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

function getPaymentMethodName($method) {
    $methods = [
        'bank_card' => 'Bankkártya',
        'paypal' => 'PayPal',
        'simplepay' => 'SimplePay',
        'bank_transfer' => 'Banki átutalás',
        'cash_on_delivery' => 'Utánvét',
    ];
    return isset($methods[$method]) ? $methods[$method] : $method;
}

function calculateOrderTotal($delivery_method, $payment_method, $cart_items) {
    $total = 0;
    // Add cart total
    foreach ($cart_items as $productId => $quantity) {
        $product = getProducts(1, false, null, $productId)[0];
        $total += $product['price'] * $quantity;
    }
    
    // Add delivery cost
    $total += calculateDeliveryCost($delivery_method);
    
    // Add payment method fee
    if ($payment_method === 'cash_on_delivery') {
        $total += 990;
    }
    
    return $total;
}

function calculateDeliveryCost($delivery_method) {
    switch ($delivery_method) {
        case 'gls':
            return 2200;
        case 'gls_parcel':
            return 1890;
        case 'mpl':
            return 1890;
        case 'mpl_parcel':
            return 1490;
        case 'pixelforge':
            return 1000;
        case 'fedex':
            return 5000;
        default:
            return 0;
    }
}

$categories = ['Processzor', 'Videókártya', 'Alaplap', 'Memória', 'Tárhely'];

function getCartItems() {
    if (isset($_COOKIE['cart'])) {
        return json_decode($_COOKIE['cart'], true);
    }
    return array();
}

function getCartCount() {
    $cartItems = getCartItems();
    return array_sum($cartItems);
}

function getProducts($limit = null, $random = false, $category = null, $productId = null) {
    global $conn;
    $sql = "SELECT products.id, products.name, products.description, products.price, products.category, products.stock, product_images.image_url 
            FROM products 
            LEFT JOIN product_images ON products.id = product_images.product_id";
    
    $where = [];
    $params = [];
    $types = '';

    if ($category) {
        $where[] = "products.category = ?";
        $params[] = $category;
        $types .= 's';
    }

    if ($productId) {
        $where[] = "products.id = ?";
        $params[] = $productId;
        $types .= 'i';
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    
    if ($random) {
        $sql .= " ORDER BY RAND()";
    }
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
        $types .= 'i';
    }
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $products = array();
    
    while ($row = $result->fetch_assoc()) {
        $productId = $row['id'];
        if (!isset($products[$productId])) {
            $products[$productId] = array(
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'price' => $row['price'],
                'category' => $row['category'],
                'stock' => $row['stock'],
                'images' => array()
            );
        }
        if ($row['image_url']) {
            $products[$productId]['images'][] = $row['image_url'];
        }
    }
    
    return array_values($products);
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fizetés - PixelForge</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <header class="bg-gray-900 text-white shadow-lg relative z-10">
        <nav class="container mx-auto px-4 py-4 flex flex-wrap items-center justify-between">
            <a href="https://shador.hu/vizsgaremek/pixelforge" class="text-2xl font-bold text-blue-500">PixelForge</a>
            
            <div class="flex items-center space-x-6 order-3 w-full md:w-auto md:order-2">
                <div class="relative group z-50">
                    <button class="flex items-center space-x-1 hover:text-blue-500 transition duration-300">
                        <span>Kategóriák</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round"stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition duration-200">
                        <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                            <?php foreach ($categories as $category): ?>
                                <a href="category.php?category=<?php echo urlencode($category); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-500" role="menuitem"><?php echo htmlspecialchars($category); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <form class="relative flex items-center" action="index.php" method="GET">
                    <input type="text" name="search" placeholder="Keresés..." class="bg-gray-800 text-white rounded-full py-2 px-4 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full md:w-64">
                    <button type="submit" class="absolute left-3 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>
            </div>
            
            <div class="flex items-center space-x-4 order-2 md:order-3">
                <a href="cart.php" class="text-white hover:text-blue-500 transition duration-300 relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span id="cart-count" class="absolute -top-2 -right-2 bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                        <?php echo getCartCount(); ?>
                    </span>
                </a>
                <?php if ($isLoggedIn): ?>
                    <div class="relative group z-50">
                        <button class="flex items-center space-x-1 text-white hover:text-blue-500 transition duration-300">
                            <span><?php echo htmlspecialchars($username); ?></span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Adataim</a>
                            <a href="orders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Rendeléseim</a>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Kijelentkezés</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="text-white hover:text-blue-500 transition duration-300">Bejelentkezés</a>
                    <a href="register.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">Regisztráció</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Fizetés</h1>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Kérjük, javítsa a következő hibákat:</strong>
                <ul class="mt-2 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold"><?php echo htmlspecialchars($success_message); ?></strong>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <form action="checkout.php?step=1" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h2 class="text-2xl font-bold mb-4">Vásárlói adatok</h2>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="full_name">
                        Teljes név
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="full_name" name="full_name" type="text" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        E-mail cím
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                        Telefonszám
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone" name="phone" type="tel" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                        Cím
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="address" name="address" type="text" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="city">
                        Város
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="city" name="city" type="text" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="zip">
                        Irányítószám
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="zip" name="zip" type="text" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="country">
                        Ország
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="country" name="country" type="text" required>
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="same_shipping" id="same_shipping" class="mr-2" checked>
                        <span class="text-sm">A szállítási cím megegyezik a számlázási címmel</span>
                    </label>
                </div>
                <div id="shipping_details" class="hidden">
                    <h3 class="text-xl font-bold mb-4">Szállítási adatok</h3>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="shipping_address">
                            Szállítási cím
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="shipping_address" name="shipping_address" type="text">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="shipping_city">
                            Szállítási város
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="shipping_city" name="shipping_city" type="text">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="shipping_zip">
                            Szállítási irányítószám
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="shipping_zip" name="shipping_zip" type="text">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="shipping_country">
                            Szállítási ország
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="shipping_country" name="shipping_country" type="text">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                        Tovább
                    </button>
                </div>
            </form>
        <?php elseif ($step === 2): ?>
            <form action="checkout.php?step=2" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h2 class="text-2xl font-bold mb-4">Szállítási mód</h2>
                <div class="mb-4">
                    <?php
                    $delivery_methods = [
                        'gls' => ['name' => 'GLS', 'price' => 1500, 'image' => 'szallitaslogo/gls_logo.png'],
                        'gls_parcel' => ['name' => 'GLS csomagpont', 'price' => 1200, 'image' => 'szallitaslogo/gls_parcel_logo.png'],
                        'mpl' => ['name'=> 'MPL', 'price' => 1400, 'image' => 'szallitaslogo/mpl_logo.jpg'],
                        'mpl_parcel' => ['name' => 'MPL csomagpont', 'price' => 1100, 'image' => 'szallitaslogo/mpl_parcel_logo.png'],
                        'pixelforge' => ['name' => 'PixelForge futárszolgálat', 'price' => 2000, 'image' => 'pixelforge_logo.png'],
                    ];

                    $customer_details = json_decode($_COOKIE['customer_details'], true);
                    if ($customer_details['country'] !== 'Magyarország') {
                        $delivery_methods['fedex'] = ['name' => 'FedEx', 'price' => 5000, 'image' => 'szallitaslogo/fedex_logo.png'];
                    }

                    foreach ($delivery_methods as $key => $method):
                    ?>
                        <label class="block mb-2">
                            <input type="radio" name="delivery_method" value="<?php echo $key; ?>" required>
                            <img src="<?php echo $method['image']; ?>" alt="<?php echo $method['name']; ?>" class="inline-block h-8 ml-2">
                            <?php echo $method['name']; ?> - <?php echo number_format($method['price'], 0, ',', ' '); ?> Ft
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="flex items-center justify-between">
                    <a href="checkout.php?step=1" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Vissza
                    </a>
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                        Tovább
                    </button>
                </div>
            </form>
        <?php elseif ($step === 3): ?>
            <form action="checkout.php?step=3" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h2 class="text-2xl font-bold mb-4">Fizetési mód</h2>
                <div class="mb-4">
                    <?php
                    $payment_methods = [
                        'bank_card' => 'Bankkártya',
                        'paypal' => 'PayPal',
                        'simplepay' => 'SimplePay',
                        'bank_transfer' => 'Banki átutalás',
                        'cash_on_delivery' => 'Utánvét (+990 Ft)',
                    ];

                    foreach ($payment_methods as $key => $method):
                    ?>
                        <label class="block mb-2">
                            <input type="radio" name="payment_method" value="<?php echo $key; ?>" required>
                            <?php echo $method; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div id="payment_details" class="mb-4 hidden">
                    <div id="bank_card_details" class="hidden">
                        <h3 class="text-xl font-bold mb-4">Bankkártya adatok</h3>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="card_number">
                                Kártyaszám
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="card_number" name="card_number" type="text" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="card_holder">
                                Kártyatulajdonos neve
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="card_holder" name="card_holder" type="text" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="expiry_date">
                                Lejárati dátum
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="expiry_date" name="expiry_date" type="text" placeholder="MM/YY" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="cvv">
                                CVV
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="cvv" name="cvv" type="text" required>
                        </div>
                    </div>
                    <div id="paypal_details" class="hidden">
                        <h3 class="text-xl font-bold mb-4">PayPal fiók</h3>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="paypal_email">
                                PayPal e-mail cím
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="paypal_email" name="paypal_email" type="email" required>
                        </div>
                    </div>
                    <div id="simplepay_details" class="hidden">
                        <p>A SimplePay fizetéshez átirányítjuk a SimplePay oldalára.</p>
                    </div>
                    <div id="bank_transfer_details" class="hidden">
                        <h3 class="text-xl font-bold mb-4">Banki átutalás adatai</h3>
                        <p>Kérjük, utalja az összeget a következő számlaszámra:</p>
                        <p><strong>Számlatulajdonos:</strong> PixelForge Kft.</p>
                        <p><strong>Bankszámlaszám:</strong> 12345678-87654321-00000000</p>
                        <p><strong>Közlemény:</strong> Rendelésszám (a rendelés leadása után kapja meg)</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <a href="checkout.php?step=2" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Vissza
                    </a>
                    <button type="submit" name="place_order" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Rendelés leadása
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <!-- Order Summary -->
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 mt-8">
            <h2 class="text-2xl font-bold mb-4">Rendelés összesítő</h2>
            <?php
            $subtotal = 0;
            $cart_items = getCartItems();
            foreach ($cart_items as $productId => $quantity) {
                $product = getProducts(1, false, null, $productId)[0];
                $item_total = $product['price'] * $quantity;
                $subtotal += $item_total;
                ?>
                <div class="flex justify-between mb-2">
                    <span><?php echo htmlspecialchars($product['name']); ?> (<?php echo intval($quantity); ?>x)</span>
                    <span><?php echo number_format($item_total, 0, ',', ' '); ?> Ft</span>
                </div>
                <?php
            }
            
            $delivery_cost = isset($_COOKIE['delivery_method']) ? calculateDeliveryCost($_COOKIE['delivery_method']) : 0;
            $payment_fee = isset($_COOKIE['payment_method']) && $_COOKIE['payment_method'] === 'cash_on_delivery' ? 990 : 0;
            $total = $subtotal + $delivery_cost + $payment_fee;
            ?>
            <div class="border-t pt-2 mt-2">
                <div class="flex justify-between mb-2">
                    <span>Részösszeg:</span>
                    <span><?php echo number_format($subtotal, 0, ',', ' '); ?> Ft</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span>Szállítási költség:</span>
                    <span><?php echo number_format($delivery_cost, 0, ',', ' '); ?> Ft</span>
                </div>
                <?php if ($payment_fee > 0): ?>
                <div class="flex justify-between mb-2">
                    <span>Fizetési mód díja:</span>
                    <span><?php echo number_format($payment_fee, 0, ',', ' '); ?> Ft</span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between font-bold text-lg mt-2 pt-2 border-t">
                    <span>Teljes összeg:</span>
                    <span><?php echo number_format($total, 0, ',', ' '); ?> Ft</span>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-900 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Kapcsolat</h3>
                    <p>Email: info@pixelforge.hu</p>
                    <p>Telefon: +36 1 234 5678</p>
                    <p>Cím: 1234 Budapest, Gamer utca 42.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Információk</h3>
                    <ul>
                        <li><a href="#" class="hover:text-blue-500 transition duration-300">Szállítás</a></li>
                        <li><a href="#" class="hover:text-blue-500 transition duration-300">Garancia</a></li>
                        <li><a href="#" class="hover:text-blue-500 transition duration-300">ÁSZF</a></li>
                        <li><a href="#" class="hover:text-blue-500 transition duration-300">Adatvédelem</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Kövess minket</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="hover:text-blue-500 transition duration-300"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="hover:text-blue-500 transition duration-300"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="hover:text-blue-500 transition duration-300"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="hover:text-blue-500 transition duration-300"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="mt-8 text-center">
                <p>&copy; 2024 PixelForge. Minden jog fenntartva.</p>
            </div>
        </div>
    </footer>

    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sameShippingCheckbox = document.getElementById('same_shipping');
            const shippingDetails = document.getElementById('shipping_details');

            if (sameShippingCheckbox && shippingDetails) {
                sameShippingCheckbox.addEventListener('change', function() {
                    shippingDetails.style.display = this.checked ? 'none' : 'block';
                });
            }

            const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
            const paymentDetails = document.getElementById('payment_details');
            const bankCardDetails = document.getElementById('bank_card_details');
            const paypalDetails = document.getElementById('paypal_details');
            const simplepayDetails = document.getElementById('simplepay_details');
            const bankTransferDetails = document.getElementById('bank_transfer_details');

            function setRequiredFields(method) {
                const bankCardInputs = bankCardDetails.querySelectorAll('input');
                const paypalInputs = paypalDetails.querySelectorAll('input');

                bankCardInputs.forEach(input => input.required = (method === 'bank_card'));
                paypalInputs.forEach(input => input.required = (method === 'paypal'));
            }

            paymentMethodRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    paymentDetails.style.display = 'block';
                    bankCardDetails.style.display = 'none';
                    paypalDetails.style.display = 'none';
                    simplepayDetails.style.display = 'none';
                    bankTransferDetails.style.display = 'none';

                    setRequiredFields(this.value);

                    switch (this.value) {
                        case 'bank_card':
                            bankCardDetails.style.display = 'block';
                            break;
                        case 'paypal':
                            paypalDetails.style.display = 'block';
                            break;
                        case 'simplepay':
                            simplepayDetails.style.display = 'block';
                            break;
                        case 'bank_transfer':
                            bankTransferDetails.style.display = 'block';
                            break;
                        default:
                            paymentDetails.style.display = 'none';
                    }
                });
            });

            // Set initial state
            const initialPaymentMethod = document.querySelector('input[name="payment_method"]:checked');
            if (initialPaymentMethod) {
                initialPaymentMethod.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>

