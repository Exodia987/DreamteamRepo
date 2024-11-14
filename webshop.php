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
if (isset($_COOKIE['auth_token'])) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE token = ?");
    $stmt->bind_param("s", $_COOKIE['auth_token']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $username = htmlspecialchars($user['username']);
    }
}

// Fetch products from database
function getProducts($category = null, $limit = null, $random = false) {
    global $conn;
    $sql = "SELECT * FROM products";
    if ($category) {
        $sql .= " WHERE category = ?";
    }
    if ($random) {
        $sql .= " ORDER BY RAND()";
    }
    if ($limit) {
        $sql .= " LIMIT ?";
    }
    $stmt = $conn->prepare($sql);
    if ($category && $limit) {
        $stmt->bind_param("si", $category, $limit);
    } elseif ($category) {
        $stmt->bind_param("s", $category);
    } elseif ($limit) {
        $stmt->bind_param("i", $limit);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$categories = [
    ['name' => 'Egerek', 'icon' => 'mouse.png'],
    ['name' => 'Fejhallgatók', 'icon' => 'headphones.png'],
    ['name' => 'Billentyűzetek', 'icon' => 'keyboard.png'],
    ['name' => 'Monitorok', 'icon' => 'monitor.png'],
    ['name' => 'Gamer PC-k', 'icon' => 'pc.png'],
    ['name' => 'Egérpadok', 'icon' => 'mousepad.png']  
];
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelForge - Prémium Gamer Hardverek</title>
    <link rel="stylesheet" href="webshop.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>
    <header>
        <div class="container">
            <a href="#" class="logo">
                <img src="path_to_your_logo.png" alt="PixelForge Logo">
            </a>
            <nav>
                <button class="mobile-menu-toggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <ul class="nav-links">
                    <li><a href="#home">Főoldal</a></li>
                    <li><a href="#products">Termékek</a></li>
                    <li><a href="#featured">Kiemelt termékek</a></li>
                    <li><a href="#about">Rólunk</a></li>
                    <li><a href="#contact">Kapcsolat</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <form action="search.php" method="GET" class="search-form">
                    <input type="text" name="q" placeholder="Keresés..." aria-label="Keresés" required>
                    <button type="submit" aria-label="Keresés"><i class="fas fa-search"></i></button>
                </form>
                <a href="cart.php" class="cart-icon" aria-label="Kosár">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                </a>
                <div class="auth-container">
                    <?php if ($username): ?>
                        <div class="dropdown">
                            <button class="dropdown-toggle"><?php echo htmlspecialchars($username); ?></button>
                            <ul class="dropdown-menu">
                                <li><a href="profile.php">Profil szerkesztése</a></li>
                                <li><a href="logout.php">Kijelentkezés</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-secondary">Bejelentkezés</a>
                        <a href="register.php" class="btn btn-primary">Regisztráció</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section id="home" class="hero">
            <div class="container">
                <h1>Üdvözöljük a PixelForge-nál</h1>
                <p>Fedezze fel prémium gamer hardvereinket a tökéletes játékélményért.</p>
                <a href="#products" class="btn btn-primary">Vásároljon most</a>
            </div>
        </section>

        <section id="products" class="products">
            <div class="container">
                <h2>Termékeink</h2>
                <div class="category-nav">
                    <?php foreach ($categories as $category): ?>
                        <a href="?category=<?php echo urlencode($category['name']); ?>" class="category-btn">
                            <img src="images/icons/<?php echo $category['icon']; ?>" alt="" class="category-icon">
                            <span><?php echo htmlspecialchars($category['name']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="product-grid">
                    <?php
                    $selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;
                    $products = getProducts($selectedCategory);
                    foreach ($products as $product):
                    ?>
                        <article class="product-card">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="product-link">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <span class="price"><?php echo number_format($product['price'], 0, ',', ' '); ?> Ft</span>
                                <span class="stock-status"><?php echo $product['stock'] > 0 ? 'Készleten' : 'Nincs készleten'; ?></span>
                            </a>
                            <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">Kosárba</button>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    <section id="featured" class="featured">
        <div class="container">
            <h2>Kiemelt termékeink</h2>
            <div class="product-grid">
                <?php
                $featuredProducts = getProducts(null, 4, true);
                foreach ($featuredProducts as $product):
                ?>
                    <div class="product-card">
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="product-link">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <span class="price"><?php echo number_format($product['price'], 0, ',', ' '); ?> Ft</span>
                            <span class="stock-status"><?php echo $product['stock'] > 0 ? 'Készleten' : 'Nincs készleten'; ?></span>
                        </a>
                        <a href="add_to_cart.php?id=<?php echo $product['id']; ?>" class="btn">Kosárba</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

        <section id="about" class="about">
            <div class="container">
                <h2>A PixelForge-ról</h2>
                <p>A PixelForge az Ön egyablakos boltja prémium gamer hardverekhez. Válogatott, kiváló minőségű egereket, fejhallgatókat, billentyűzeteket, monitorokat és gamer PC-ket kínálunk a játékélmény fokozásához.</p>
            </div>
        </section>

        <section id="contact" class="contact">
            <div class="container">
                <h2>Kapcsolat</h2>
                <form action="send_message.php" method="POST">
                    <input type="text" name="name" placeholder="Az Ön neve" required>
                    <input type="email" name="email" placeholder="Az Ön e-mail címe" required>
                    <textarea name="message" placeholder="Az Ön üzenete" required></textarea>
                    <button type="submit" class="btn">Üzenet küldése</button>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3>Kapcsolat</h3>
                    <ul>
                        <li>Email: pixelforge@shador.hu</li>
                        <li>Tel: +36 00 0000 000</li>
                        <li>Cím: 1234 Budapest, Gamer utca 42.</li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Információk</h3>
                    <ul>
                        <li><a href="/szallitas">Szállítási információk</a></li>
                        <li><a href="/garancia">Garancia</a></li>
                        <li><a href="/aszf">ÁSZF</a></li>
                        <li><a href="/adatkezeles">Adatkezelési tájékoztató</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Vásárlói fiók</h3>
                    <ul>
                        <li><a href="/profil">Profilom</a></li>
                        <li><a href="/rendelesek">Rendeléseim</a></li>
                        <li><a href="/kedvencek">Kedvencek</a></li>
                        <li><a href="/kosar">Kosár</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Kövess minket</h3>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="footer-bottom">
                <p>&copy; 2024 PixelForge. Minden jog fenntartva.</p>
                <div class="payment-icons">
                    <img src="images/payment-icons/mastercard.png" alt="Mastercard">
                    <img src="images/payment-icons/visa.png" alt="Visa">
                    <img src="images/payment-icons/paypal.png" alt="PayPal">
                </div>
            </div>
        </div>
    </footer>

    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
    <script src="script.js"></script>
</body>
</html>