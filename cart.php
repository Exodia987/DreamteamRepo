<?php
session_start();

// Database connection
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "Keszenallok01!";
$dbname = "user_auth";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $product['quantity'] = $quantity;
            $cart_items[] = $product;
            $total += $product['price'] * $quantity;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kosár - PixelForge</title>
    <link rel="stylesheet" href="webshop.css">
</head>
<body>
    <header>
        <!-- Add your header content here -->
    </header>

    <main>
        <div class="container">
            <h1>Kosár</h1>
            <?php if (empty($cart_items)): ?>
                <p>A kosár üres.</p>
            <?php else: ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Termék</th>
                            <th>Ár</th>
                            <th>Mennyiség</th>
                            <th>Összesen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo number_format($item['price'], 0, ',', ' '); ?> Ft</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', ' '); ?> Ft</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">Összesen:</td>
                            <td><?php echo number_format($total, 0, ',', ' '); ?> Ft</td>
                        </tr>
                    </tfoot>
                </table>
                <a href="checkout.php" class="btn">Tovább a pénztárhoz</a>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h5>Kapcsolat</h5>
                    <ul class="list-unstyled">
                        <li>Email: pixelforge@shador.hu</li>
                        <li>Tel: +36 00 0000 000</li>
                        <li>Cím: 1234 Budapest, Gamer utca 42.</li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Információk</h5>
                    <ul class="list-unstyled">
                        <li><a href="/szallitas">Szállítási információk</a></li>
                        <li><a href="/garancia">Garancia</a></li>
                        <li><a href="/aszf">ÁSZF</a></li>
                        <li><a href="/adatkezeles">Adatkezelési tájékoztató</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Vásárlói fiók</h5>
                    <ul class="list-unstyled">
                        <li><a href="/profil">Profilom</a></li>
                        <li><a href="/rendelesek">Rendeléseim</a></li>
                        <li><a href="/kedvencek">Kedvencek</a></li>
                        <li><a href="/kosar">Kosár</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Kövess minket</h5>
                    <div class="social-icons">
                        <a href="#" class="me-2"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 PixelForge. Minden jog fenntartva.</p>
                </div>
                <div class="col-md-6 text-end">
                    <img src="images/payment-icons/mastercard.png" alt="Mastercard" class="payment-icon">
                    <img src="images/payment-icons/visa.png" alt="Visa" class="payment-icon">
                    <img src="images/payment-icons/paypal.png" alt="PayPal" class="payment-icon">
                </div>
            </div>
        </div>
    </footer>
</body>
</html>