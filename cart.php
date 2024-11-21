<?php
// Database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "Keszenallok01!";
$dbname = "user_auth";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// Fetch products with images
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

// Get cart items from cookie
function getCartItems() {
    if (isset($_COOKIE['cart'])) {
        return json_decode($_COOKIE['cart'], true);
    }
    return array();
}

// Remove item from cart
function removeFromCart($productId) {
    $cart = getCartItems();
    if (isset($cart[$productId])) {
        unset($cart[$productId]);
        setcookie('cart', json_encode($cart), time() + (86400 * 30), "/");
    }
}

// Update cart item quantity
function updateCartItemQuantity($productId, $quantity) {
    $cart = getCartItems();
    if (isset($cart[$productId])) {
        if ($quantity > 0) {
            $cart[$productId] = $quantity;
        } else {
            unset($cart[$productId]);
        }
        setcookie('cart', json_encode($cart), time() + (86400 * 30), "/");
    }
}

// Get cart total
function getCartTotal() {
    $total = 0;
    $cartItems = getCartItems();
    foreach ($cartItems as $productId => $quantity) {
        $product = getProducts(1, false, null, $productId)[0];
        $total += $product['price'] * $quantity;
    }
    return $total;
}

// Function to get cart count
function getCartCount() {
    $cartItems = getCartItems();
    return array_sum($cartItems);
}

// Handle cart actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    switch ($action) {
        case 'remove':
            removeFromCart($productId);
            break;
        case 'update':
            updateCartItemQuantity($productId, $quantity);
            break;
    }

    // Redirect to prevent form resubmission
    header("Location: cart.php");
    exit();
}

$cartItems = getCartItems();
$cartTotal = getCartTotal();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kosár - PixelForge</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body class="font-roboto bg-gray-100">
<header class="bg-gray-900 text-white shadow-lg">
        <nav class="container mx-auto px-4 py-4 flex items-center justify-between">
            <a href="https://shador.hu/vizsgaremek/pixelforge" class="text-2xl font-bold text-blue-500">PixelForge</a>
            
            <div class="hidden md:flex items-center space-x-6">
                <div class="relative group">
                    <button class="flex items-center space-x-1 hover:text-blue-500 transition duration-300">
                        <span>Kategóriák</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden group-hover:block">
                        <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                            <?php foreach ($categories as $category): ?>
                                <a href="?category=<?php echo urlencode($category); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-500" role="menuitem"><?php echo htmlspecialchars($category); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <form class="relative" action="index.php" method="GET">
                    <input type="text" name="search" placeholder="Keresés..." class="bg-gray-800 text-white rounded-full py-2 px-4 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
                    <button type="submit" class="absolute left-3 top-1/2 transform -translate-y-1/2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="cart.php" class="text-white hover:text-blue-500 transition duration-300 relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span id="cart-count" class="absolute -top-2 -right-2 bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                        <?php echo getCartCount(); ?>
                    </span>
                </a>
                <a href="login.php" class="text-white hover:text-blue-500 transition duration-300">Bejelentkezés</a>
                <a href="register.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">Regisztráció</a>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Kosár</h1>
        <?php if (empty($cartItems)): ?>
            <p>A kosár üres.</p>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <?php foreach ($cartItems as $productId => $quantity): 
                    $product = getProducts(1, false, null, $productId)[0];
                ?>
                    <div class="flex items-center justify-between border-b py-4">
                        <div class="flex items-center">
                            <img src="<?php echo htmlspecialchars($product['images'][0]); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-16 h-16 object-cover mr-4">
                            <div>
                                <h3 class="font-semibold"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-gray-600"><?php echo number_format($product['price'], 0, ',', ' '); ?> Ft</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <form action="cart.php" method="POST" class="flex items-center">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                <input type="number" name="quantity" value="<?php echo $quantity; ?>" min="1" class="w-16 text-center border rounded-l px-2 py-1">
                                <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded-r hover:bg-blue-600">Frissítés</button>
                            </form>
                            <form action="cart.php" method="POST" class="ml-4">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700">Törlés</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="mt-6 text-right">
                    <p class="text-xl font-semibold">Összesen: <?php echo number_format($cartTotal, 0, ',', ' '); ?> Ft</p>
                    <a href="checkout.php" class="bg-green-500 text-white px-6 py-2 rounded mt-4 inline-block hover:bg-green-600">Fizetés</a>
                </div>
            </div>
        <?php endif; ?>
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
</body>
</html>