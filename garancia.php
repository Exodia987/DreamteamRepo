<?php
// Database connection
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "Keszenallok01!";
$dbname = "user_auth";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// Check for logged-in user
$username = '';
$isLoggedIn = false;
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

// Fetch categories
function getCategories() {
    global $conn;
    $sql = "SELECT DISTINCT category FROM products ORDER BY category";
    $result = $conn->query($sql);
    $categories = array();
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    
    return $categories;
}

// Get cart items from cookie
function getCartItems() {
    if (isset($_COOKIE['cart'])) {
        return json_decode($_COOKIE['cart'], true);
    }
    return array();
}

// Add item to cart
function addToCart($productId, $quantity = 1) {
    $cart = getCartItems();
    if (isset($cart[$productId])) {
        $cart[$productId] += $quantity;
    } else {
        $cart[$productId] = $quantity;
    }
    setcookie('cart', json_encode($cart), time() + (86400 * 30), "/"); // 30 days expiration
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
        case 'add':
            addToCart($productId, $quantity);
            break;
        case 'remove':
            removeFromCart($productId);
            break;
        case 'update':
            updateCartItemQuantity($productId, $quantity);
            break;
    }

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$featuredProducts = getProducts(4, true);
$categories = getCategories();

// Get the current category from the URL parameter
$currentCategory = isset($_GET['category']) ? $_GET['category'] : null;
$products = $currentCategory ? getProducts(null, false, $currentCategory) : $featuredProducts;

// Category icons (you may need to adjust these or use actual image URLs)
$categoryIcons = [
    'Processzor' => 'fas fa-microchip',
    'Videókártya' => 'fas fa-tv',
    'Alaplap' => 'fas fa-server',
    'Memória' => 'fas fa-memory',
    'Tárhely' => 'fas fa-hdd',
    'Periféria' => 'fas fa-keyboard',
    'Monitor' => 'fas fa-desktop',
    'Ház' => 'fas fa-box',
];

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelForge - Prémium Gamer Hardverek</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
</head>
<body class="font-roboto bg-gray-100">
<header class="bg-gray-900 text-white shadow-lg relative z-10">
    <nav class="container mx-auto px-4 py-4 flex flex-wrap items-center justify-between">
        <a href="https://shador.hu/vizsgaremek/pixelforge" class="text-2xl font-bold text-blue-500">PixelForge</a>
        
        <div class="flex items-center space-x-6 order-3 w-full md:w-auto md:order-2">
            <div class="relative group z-50">
                <button class="flex items-center space-x-1 hover:text-blue-500 transition duration-300">
                    <span>Kategóriák</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
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
            <a href="cart" class="text-white hover:text-blue-500 transition duration-300 relative">
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
<main class="container mx-auto px-4 py-10 bg-white shadow-md rounded-lg">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Garanciális Információk</h1>
    <div class="space-y-8 text-gray-700 leading-relaxed">
        <section>
            <h2 class="text-xl font-semibold text-blue-600">Garanciális Feltételek</h2>
            <p>A PixelForge-nál minden termékünkre gyártói garanciát biztosítunk. A garancia időtartama termékkategóriánként változó, de általában 12-36 hónap.</p>
            <ul class="list-disc ml-6">
                <li><strong>Hardverek:</strong> 24 hónap gyártói garancia.</li>
                <li><strong>Perifériák:</strong> 12 hónap gyártói garancia.</li>
                <li><strong>Kiegészítők:</strong> 6 hónap garancia.</li>
            </ul>
        </section>
        <section>
            <h2 class="text-xl font-semibold text-blue-600">Garanciális Ügyintézés</h2>
            <p>A garanciális ügyintézés gördülékeny lebonyolítása érdekében kérjük, kövesse az alábbi lépéseket:</p>
            <ul class="list-decimal ml-6">
                <li>Vegye fel a kapcsolatot ügyfélszolgálatunkkal az alábbi elérhetőségeken:</li>
                <ul class="list-disc ml-6">
                    <li><strong>Email:</strong> support@pixelforge.hu</li>
                    <li><strong>Telefon:</strong> +36 1 234 5678</li>
                </ul>
                <li>Készítse elő a vásárlást igazoló számlát vagy nyugtát.</li>
                <li>Küldje vissza a terméket eredeti csomagolásban, amennyiben lehetséges.</li>
            </ul>
        </section>
        <section>
            <h2 class="text-xl font-semibold text-blue-600">Garancia Érvényesítése</h2>
            <p>A garancia kizárólag az alábbi feltételek mellett érvényes:</p>
            <ul class="list-disc ml-6">
                <li>A terméket rendeltetésszerűen használták.</li>
                <li>A meghibásodás gyártási hiba következménye.</li>
                <li>A vásárlást igazoló dokumentumok rendelkezésre állnak.</li>
            </ul>
            <p>Nem vonatkozik a garancia az alábbi esetekre:</p>
            <ul class="list-disc ml-6">
                <li>Sérülések, amelyek nem megfelelő használatból erednek.</li>
                <li>Helytelen tárolás vagy szállítás okozta károk.</li>
                <li>Nem hivatalos szerviz által végzett javítások.</li>
            </ul>
        </section>
        <section>
            <h2 class="text-xl font-semibold text-blue-600">Csere és Javítás</h2>
            <p>Amennyiben a termék hibás és a garancia érvényes, a következő opciók közül választhat:</p>
            <ul class="list-disc ml-6">
                <li><strong>Javítás:</strong> Az adott hibát megjavítjuk.</li>
                <li><strong>Csere:</strong> Amennyiben a javítás nem lehetséges, csereterméket biztosítunk.</li>
                <li><strong>Pénzvisszatérítés:</strong> Ha a termék javítása és cseréje nem lehetséges, a vételár visszatérítésére kerül sor.</li>
            </ul>
        </section>
        <section>
            <h2 class="text-xl font-semibold text-blue-600">Fontos Tudnivalók</h2>
            <ul class="list-disc ml-6">
                <li>Kérjük, minden esetben őrizze meg a vásárlási bizonylatot!</li>
                <li>A garanciális ügyintézés folyamata 7-14 munkanapot vehet igénybe.</li>
                <li>Nemzetközi vásárlások esetén a garancia feltételei eltérhetnek.</li>
            </ul>
        </section>
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
                        <li><a href="szallitas" class="hover:text-blue-500 transition duration-300">Szállítás</a></li>
                        <li><a href="garancia" class="hover:text-blue-500 transition duration-300">Garancia</a></li>
                        <li><a href="aszf" class="hover:text-blue-500 transition duration-300">ÁSZF</a></li>
                        <li><a href="adatvedelem" class="hover:text-blue-500 transition duration-300">Adatvédelem</a></li>
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
    </body>
</html>