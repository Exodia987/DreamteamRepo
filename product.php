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

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
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

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - PixelForge</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <header class="bg-gray-800 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">PixelForge</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="index.php" class="hover:text-gray-300">Főoldal</a></li>
                    <li><a href="index.php#products" class="hover:text-gray-300">Termékek</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto mt-8 p-4">
        <div class="bg-white rounded-lg shadow-lg p-6 md:flex">
            <div class="md:w-1/2">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-auto rounded-lg">
            </div>
            <div class="md:w-1/2 md:pl-8 mt-4 md:mt-0">
                <h2 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($product['name']); ?></h2>
                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                <div class="flex items-center mb-4">
                    <span class="text-2xl font-bold text-gray-800"><?php echo number_format($product['price'], 0, ',', ' '); ?> Ft</span>
                    <span class="ml-2 px-2 py-1 bg-<?php echo $product['stock'] > 0 ? 'green' : 'red'; ?>-500 text-white text-sm rounded">
                        <?php echo $product['stock'] > 0 ? 'Készleten' : 'Nincs készleten'; ?>
                    </span>
                </div>
                <button class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                    Kosárba
                </button>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white mt-8 py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 PixelForge. Minden jog fenntartva.</p>
        </div>
    </footer>
</body>
</html>