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
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digiprint - Az Ön Képzelete, A Mi Anyagunk</title>
    <link rel="stylesheet" href="webshop.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script>
        // Lágy gördülési hatás hozzáadása a belső linkekhez
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener("click", function(e) {
                    e.preventDefault(); // Eredeti hivatkozás megakadályozása
                    const target = document.querySelector(this.getAttribute("href"));
                    if (target) {
                        window.scrollTo({
                            top: target.offsetTop,
                            behavior: "smooth" // Lágy gördülési hatás
                        });
                    }
                });
            });
        });
    </script>
</head>
<body>
<header>
    <div class="container">
        <h1>Digiprint</h1>
        <nav>
            <ul>
                <li><a href="#hero">Főoldal</a></li>
                <li><a href="#products">Termékek</a></li>
                <li><a href="#process">Folyamat</a></li>
                <li><a href="#contact">Kapcsolat</a></li>
                <section id="auth-buttons">
                    <div class="auth-container">
                        <?php if ($username): ?>
                            <div class="dropdown">
                                <span style="cursor: pointer;"><?php echo $username; ?></span>
                                <div class="dropdown-content">
                                    <a href="profile.php">Profil módosítása</a>
                                    <a href="logout.php">Kijelentkezés</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <button onclick="window.location.href='bejelentkezes?ref=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>'">Bejelentkezés</button>
                            <button onclick="window.location.href='regisztracio?ref=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>'">Regisztráció</button>
                        <?php endif; ?>
                    </div>
                </section>
            </ul>
        </nav>
    </div>
</header>

<main>
    <section id="hero">
        <div class="container">
            <h2>Az Ön Képzelete,<br>a Mi Anyagunk</h2>
            <p>Alakítsa ötleteit hordható művészetté egyedi textilnyomtatásunkkal</p>
            <a href="#products" class="cta-button">Kezdje el a Tervezést</a>
        </div>
    </section>

    <section id="products">
        <div class="container">
            <h2>Canvas Kollekciónk</h2>
            <div class="product-grid">
                <div class="product-card">
                    <div class="product-image" style="background-image: url('tshirt-placeholder.jpg');"></div>
                    <h3>Pólók</h3>
                    <p>Ár: 19,99$-tól</p>
                    <a href="polo-testreszabas" class="product-button">Testreszabás</a>
                </div>
                <div class="product-card">
                    <div class="product-image" style="background-image: url('hoodie-placeholder.jpg');"></div>
                    <h3>Kapucnis Pulóverek</h3>
                    <p>Ár: 39,99$-tól</p>
                    <a href="#" class="product-button">Testreszabás</a>
                </div>
                <div class="product-card">
                    <div class="product-image" style="background-image: url('bedding-placeholder.jpg');"></div>
                    <h3>Ágynemű</h3>
                    <p>Ár: 49,99$-tól</p>
                    <a href="#" class="product-button">Testreszabás</a>
                </div>
                <div class="product-card">
                    <div class="product-image" style="background-image: url('jersey-placeholder.jpg');"></div>
                    <h3>Sportmez</h3>
                    <p>Ár: 29,99$-tól</p>
                    <a href="#" class="product-button">Testreszabás</a>
                </div>
            </div>
        </div>
    </section>

    <section id="process">
        <div class="container">
            <h2>Ötlettől a Megvalósításig</h2>
            <div class="process-steps">
                <div class="process-step">
                    <div class="step-icon">1</div>
                    <h3>Tervezés</h3>
                    <p>Töltse fel műalkotását vagy használja online tervezőnket</p>
                </div>
                <div class="process-step">
                    <div class="step-icon">2</div>
                    <h3>Előnézet</h3>
                    <p>Nézze meg tervét életre kelni 3D előnézetünkben</p>
                </div>
                <div class="process-step">
                    <div class="step-icon">3</div>
                    <h3>Nyomtatás</h3>
                    <p>Élénk színekért modern nyomtatókat használunk</p>
                </div>
                <div class="process-step">
                    <div class="step-icon">4</div>
                    <h3>Kiszállítás</h3>
                    <p>Az Ön egyedi alkotása közvetlenül az ajtóhoz kerül</p>
                </div>
            </div>
        </div>
    </section>

    <section id="contact">
        <div class="container">
            <h2>Alkossunk Együtt</h2>
            <form action="process_form.php" method="POST">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Az Ön Neve" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Az Ön E-mail címe" required>
                </div>
                <div class="form-group">
                    <textarea name="message" placeholder="Meséljen nekünk projektjéről" required></textarea>
                </div>
                <button type="submit">Üzenet Küldése</button>
            </form>
        </div>
    </section>
</main>

<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Digiprint</h3>
                <p>Az Ön képzeletét kelti életre, egy nyomtatásban.</p>
            </div>
            <div class="footer-section">
                <h3>Gyors Linkek</h3>
                <ul>
                    <li><a href="#hero">Főoldal</a></li>
                    <li><a href="#products">Termékek</a></li>
                    <li><a href="#process">Folyamat</a></li>
                    <li><a href="#contact">Kapcsolat</a></li>
                    <li><a href="aszf">Általános Szerződés</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Kapcsolódjon Velünk</h3>
                <div class="social-icons">
                    <a href="#" class="social-icon">FB</a>
                    <a href="#" class="social-icon">IG</a>
                    <a href="#" class="social-icon">TW</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2023 Digiprint. Minden jog fenntartva.</p>
        </div>
    </div>
</footer>
</body>
</html>
