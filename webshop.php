<?php
// Database connection
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "Keszenallok01!";
$dbname = "user_auth";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
function getProducts($category = null) {
    global $conn;
    $sql = "SELECT * FROM products";
    if ($category) {
        $sql .= " WHERE category = ?";
    }
    $stmt = $conn->prepare($sql);
    if ($category) {
        $stmt->bind_param("s", $category);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$categories = ['Mice', 'Headsets', 'Keyboards', 'Monitors', 'Gaming PCs'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelForge - Premium Gaming Hardware</title>
    <link rel="stylesheet" href="webshop.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <h1>PixelForge</h1>
            <nav>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#products">Products</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>
            <div class="auth-container">
                <?php if ($username): ?>
                    <div class="dropdown">
                        <span><?php echo $username; ?></span>
                        <div class="dropdown-content">
                            <a href="profile.php">Edit Profile</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn">Login</a>
                    <a href="register.php" class="btn">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <section id="home" class="hero">
            <div class="container">
                <h2>Welcome to PixelForge</h2>
                <p>Discover premium gaming hardware for the ultimate gaming experience.</p>
                <a href="#products" class="btn">Shop Now</a>
            </div>
        </section>

        <section id="products" class="products">
            <div class="container">
                <h2>Our Products</h2>
                <div class="category-nav">
                    <?php foreach ($categories as $category): ?>
                        <a href="?category=<?php echo urlencode($category); ?>" class="btn"><?php echo $category; ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="product-grid">
                    <?php
                    $selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;
                    $products = getProducts($selectedCategory);
                    foreach ($products as $product):
                    ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                            <a href="add_to_cart.php?id=<?php echo $product['id']; ?>" class="btn">Add to Cart</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="about" class="about">
            <div class="container">
                <h2>About PixelForge</h2>
                <p>PixelForge is your one-stop shop for premium gaming hardware. We offer a curated selection of high-quality mice, headsets, keyboards, monitors, and gaming PCs to enhance your gaming experience.</p>
            </div>
        </section>

        <section id="contact" class="contact">
            <div class="container">
                <h2>Contact Us</h2>
                <form action="send_message.php" method="POST">
                    <input type="text" name="name" placeholder="Your Name" required>
                    <input type="email" name="email" placeholder="Your Email" required>
                    <textarea name="message" placeholder="Your Message" required></textarea>
                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 PixelForge. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>