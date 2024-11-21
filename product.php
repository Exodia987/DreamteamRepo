<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "Keszenallok01!";
$dbname = "user_auth";

try {
    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Kapcsolódási hiba: " . $conn->connect_error);
    }

    // Fetch categories first
    function getCategories($conn) {
        $sql = "SELECT DISTINCT products.category FROM products ORDER BY products.category";
        $result = $conn->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }
        $categories = array();
        
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        
        return $categories;
    }

    // Get product ID from URL
    $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Fetch product details
    $stmt = $conn->prepare("SELECT products.id, products.name, products.description, products.price, products.stock, products.category FROM products WHERE products.id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        // Redirect to 404 page or home page if product not found
        header("Location: index.php");
        exit();
    }

    // Fetch product images
    $stmt = $conn->prepare("SELECT product_images.id, product_images.product_id, product_images.image_url FROM product_images WHERE product_images.product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $images_result = $stmt->get_result();
    $product_images = $images_result->fetch_all(MYSQLI_ASSOC);

    // Fetch similar products
    $stmt = $conn->prepare("SELECT products.id, products.name, products.description, products.price, products.stock, products.category, 
                            (SELECT image_url FROM product_images WHERE product_id = products.id LIMIT 1) AS image_url
                            FROM products 
                            WHERE products.category = ? AND products.id != ? 
                            ORDER BY RAND() 
                            LIMIT 4");
    $stmt->bind_param("si", $product['category'], $product_id);
    $stmt->execute();
    $similar_products_result = $stmt->get_result();
    $similar_products = $similar_products_result->fetch_all(MYSQLI_ASSOC);

    // Update recently viewed products using cookies
    $recently_viewed = isset($_COOKIE['recently_viewed']) ? json_decode($_COOKIE['recently_viewed'], true) : array();
    if (!in_array($product_id, $recently_viewed)) {
        array_unshift($recently_viewed, $product_id);
        $recently_viewed = array_slice($recently_viewed, 0, 4);
    }
    setcookie('recently_viewed', json_encode($recently_viewed), time() + (86400 * 30), "/"); // 30 days expiration

    // Fetch recently viewed products
    $recently_viewed_products = array();
    if (!empty($recently_viewed)) {
        $recently_viewed_ids = implode(',', array_map('intval', $recently_viewed));
        $stmt = $conn->prepare("SELECT products.id, products.name, products.description, products.price, products.stock, products.category, 
                            (SELECT image_url FROM product_images WHERE product_id = products.id LIMIT 1) AS image_url
                            FROM products 
                            WHERE products.id IN ($recently_viewed_ids)
                            ORDER BY FIELD(products.id, $recently_viewed_ids)");
        $stmt->execute();
        $recently_viewed_result = $stmt->get_result();
        $recently_viewed_products = $recently_viewed_result->fetch_all(MYSQLI_ASSOC);
    }

    // Get categories
    $categories = getCategories($conn);

    // Close database connection after all queries
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    die("An error occurred: " . $e->getMessage());
}

// Function to format description with list items
function formatDescription($description) {
    $lines = explode("\n", $description);
    $formatted = '';
    $inList = false;

    foreach ($lines as $line) {
        if (strpos($line, '**') === 0) {
            if (!$inList) {
                $formatted .= '<ul class="list-disc pl-5 mb-4">';
                $inList = true;
            }
            $formatted .= '<li>' . htmlspecialchars(substr($line, 2)) . '</li>';
        } elseif (strpos($line, '##') === 0) {
            if ($inList) {
                $formatted .= '</ul>';
                $inList = false;
            }
            $formatted .= '<p class="mb-2 font-bold">' . htmlspecialchars(substr($line, 2)) . '</p>';
        } else {
            if ($inList) {
                $formatted .= '</ul>';
                $inList = false;
            }
            $formatted .= '<p class="mb-2">' . htmlspecialchars($line) . '</p>';
        }
    }

    if ($inList) {
        $formatted .= '</ul>';
    }

    return $formatted;
}

// Function to get cart count from cookie
function getCartCount() {
    if (isset($_COOKIE['cart'])) {
        $cart = json_decode($_COOKIE['cart'], true);
        return array_sum($cart);
    }
    return 0;
}

function calculateShippingDays() {
    $today = new DateTime();
    $dayOfWeek = (int)$today->format('N'); // 1 (Monday) to 7 (Sunday)
    
    if ($dayOfWeek === 6) { // Saturday
        return 2; // Wait till Monday
    } elseif ($dayOfWeek === 7) { // Sunday
        return 1; // Wait till Monday
    } else {
        return 1; // Next business day
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - PixelForge</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-100 font-roboto">
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
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-500" role="menuitem"><?php echo htmlspecialchars($category); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <form class="relative">
                    <input type="text" placeholder="Keresés..." class="bg-gray-800 text-white rounded-full py-2 px-4 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </form>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="cart" class="text-white hover:text-blue-500 transition duration-300 relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span id="cart-count" class="absolute -top-2 -right-2 bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                        <?php echo getCartCount(); ?>
                    </span>
                </a>
                <a href="bejelentkezes class="text-white hover:text-blue-500 transition duration-300">Bejelentkezés</a>
                <a href="regisztracio" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">Regisztráció</a>
            </div>
        </nav>
    </header>

    <main class="container mx-auto mt-8 p-4">
        <div class="bg-white rounded-lg shadow-lg p-6 md:flex">
            <div class="md:w-1/2">
                <?php if (!empty($product_images)): ?>
                    <div class="mb-4 h-96 overflow-hidden">
                        <img src="<?php echo htmlspecialchars($product_images[0]['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="w-full h-full object-contain rounded-lg shadow-md" 
                             id="main-image">
                    </div>
                    <?php if (count($product_images) > 1): ?>
                        <div class="grid grid-cols-4 gap-2">
                            <?php foreach ($product_images as $index => $image): ?>
                                <div class="h-24 overflow-hidden">
                                    <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="w-full h-full object-contain rounded-lg cursor-pointer shadow-sm hover:shadow-md transition duration-300" 
                                         onclick="changeMainImage(this.src)">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="h-96 overflow-hidden">
                        <img src="/placeholder.svg?height=400&width=400" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="w-full h-full object-contain rounded-lg shadow-md">
                    </div>
                <?php endif; ?>
            </div>
            <div class="md:w-1/2 md:pl-8 mt-4 md:mt-0">
                <h2 class="text-3xl font-bold mb-4 text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h2>
                <div class="text-gray-600 mb-4">
                    <?php echo formatDescription($product['description']); ?>
                </div>
                <div class="flex items-center space-x-4 mb-4">
                    
    <div class="flex items-center border rounded">
        <input type="number" 
               id="quantity" 
               min="1" 
               value="1" 
               class="w-20 px-3 py-2 text-center focus:outline-none focus:ring-2 focus:ring-blue-500"
               max="<?php echo $product['stock']; ?>">
    </div>
    <button id="add-to-cart" 
            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300" 
            data-product-id="<?php echo $product['id']; ?>"
            <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
        Kosárba
    </button>
</div>
<div class="text-gray-600">
    <?php
    $shippingDays = calculateShippingDays();
    echo "Szállítási idő: " . $shippingDays . " nap";
    if ($shippingDays > 1) {
        echo " (hétvége miatt)";
    }
    ?>
</div>
<div class="flex items-center mb-4">
                    <span class="text-2xl font-bold text-gray-800"><?php echo number_format($product['price'], 0, ',', ' '); ?> Ft</span>
                    <span class="ml-2 px-2 py-1 bg-<?php echo $product['stock'] > 0 ? 'green' : 'red'; ?>-500 text-white text-sm rounded">
                        <?php echo $product['stock'] > 0 ? 'Készleten' : 'Nincs készleten'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Similar Products Section -->
        <section class="mt-12">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Hasonló termékek</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($similar_products as $similar_product): ?>
                    <a href="product.php?id=<?php echo $similar_product['id']; ?>" class="bg-white rounded-lg shadow-md overflow-hidden block hover:shadow-lg transition-shadow duration-300">
                        <div class="h-48 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($similar_product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($similar_product['name']); ?>" 
                                 class="w-full h-full object-contain">
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-lg mb-2 text-gray-800"><?php echo htmlspecialchars($similar_product['name']); ?></h3>
                            <p class="text-gray-600 mb-2"><?php echo number_format($similar_product['price'], 0, ',', ' '); ?> Ft</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Recently Viewed Products Section -->
        <?php if (!empty($recently_viewed_products)): ?>
        <section class="mt-12">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Utoljára megtekintett termékek</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($recently_viewed_products as $recent_product): ?>
                    <a href="product.php?id=<?php echo $recent_product['id']; ?>" class="bg-white rounded-lg shadow-md overflow-hidden block hover:shadow-lg transition-shadow duration-300">
                        <div class="h-48 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($recent_product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($recent_product['name']); ?>" 
                                 class="w-full h-full object-contain">
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-lg mb-2 text-gray-800"><?php echo htmlspecialchars($recent_product['name']); ?></h3>
                            <p class="text-gray-600 mb-2"><?php echo number_format($recent_product['price'], 0, ',', ' '); ?> Ft</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <footer class="bg-gray-900 text-white py-8 mt-12">
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

    <script>
function changeMainImage(src) {
    document.getElementById('main-image').src = src;
}

document.addEventListener('DOMContentLoaded', function() {
    const addToCartButton = document.getElementById('add-to-cart');
    const quantityInput = document.getElementById('quantity');

    addToCartButton.addEventListener('click', function() {
        const productId = this.getAttribute('data-product-id');
        const quantity = parseInt(quantityInput.value) || 1;

        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId + '&quantity=' + quantity
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('A termék hozzáadva a kosárhoz!');
                updateCartCount(data.cartCount);
            } else {
                alert('Hiba történt a kosárhoz adás során.');
            }
        });
    });

    // Add input validation for quantity
    quantityInput.addEventListener('change', function() {
        const value = parseInt(this.value);
        const max = parseInt(this.getAttribute('max'));
        
        if (value < 1) this.value = 1;
        if (max && value > max) this.value = max;
    });

    function updateCartCount(count) {
        const cartCountElement = document.getElementById('cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = count;
        }
    }
});
</script>
</body>
</html>