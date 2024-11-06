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
                            <button onclick="window.location.href='login.php?ref=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>'">Bejelentkezés</button>
                            <button onclick="window.location.href='register.php?ref=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>'">Regisztráció</button>
                        <?php endif; ?>
                    </div>
                </section>
            </ul>
        </nav>
    </div>
</header>

<main>
    <section id="aszf">
        <div class="container">
            <h2>Általános Szerződési Feltételek</h2>
            <p>
                <strong>1. BEVEZETÉS</strong><br>
                A jelen Általános Szerződési Feltételek (ÁSZF) határozza meg a DigiPrint és ügyfelei közötti szerződési feltételeket. Kérjük, hogy figyelmesen olvassa el ezt a dokumentumot. <br><br>
                <strong>2. CÉLUNK</strong><br>
                A DigiPrint fő célja, hogy egyedi, Ön által megálmodott mintákat vigyen pólókra, pulóverekre és egyéb ruhadarabokra. Öntől kérjük a kreatív dizájnt, mi pedig biztosítjuk, hogy az ötlete életre keljen, akár pamut, akár poliészter anyagon. <br><br>
                <strong>3. ÁRAK ÉS DÍJAK</strong><br>
                Minden árat az elvégzett munka alapján határozunk meg, a választott anyagtól és a minta bonyolultságától függően. Az árak tartalmazzák a festékköltséget, a gépek fáradozását, és a szorgos dolgozóink kávéellátását. <br><br>
                <strong>4. SZÁLLÍTÁSI FELTÉTELEK</strong><br>
                A szállítási idő nagymértékben függ attól, hogy Ön mennyire messze él nyomdánktól és milyen gyors a posta. Ha Ön türelmes, mi is azok leszünk, de ha siet, az extra gyors szállítás feláras. <br><br>
                <strong>5. VISSZATÉRÍTÉSI POLITIKA</strong><br>
                Ha Ön úgy találja, hogy a nyomtatott ruhadarab nem olyan, mint várta, ne csüggedjen! Küldjön nekünk egy kedves üzenetet, és mindent megteszünk annak érdekében, hogy mosolyt varázsoljunk az arcára - ha másképp nem megy, legalább egy vicces kávésbögrével!<br><br>
                <strong>6. SZELLEMI TULAJDONJOG</strong><br>
                Az Ön által megálmodott dizájn továbbra is az Ön szellemi tulajdona marad, de kérjük, ne feledje, hogy a mi logónk és marketingünk felett szigorú uralmat tartunk.<br><br>
                <strong>7. A SZERZŐDÉS MÓDOSÍTÁSA</strong><br>
                Fenntartjuk a jogot, hogy az ÁSZF-et időről időre frissítsük, különös tekintettel a szabályzataink egyoldalú javítására. Ha észrevétele lenne, szívesen halljuk, feltéve, ha az pozitív!<br><br>
                <strong>8. FELELŐSSÉG KORLÁTOZÁSA</strong><br>
                A DigiPrint nem vállal felelősséget semmilyen közvetett vagy véletlen kárért. Ha Ön nyomást gyakorol ránk, akkor is maradunk lazák, mint egy pamutpóló.<br><br>
                <strong>9. ADATVÉDELEM</strong><br>
                Az Ön által megadott adatokat biztonságosan tároljuk, és soha nem adjuk át illetékteleneknek. Csak egyéni kreativitása iránt érdeklődünk, nem az online vásárlási szokásai iránt.<br><br>
                <strong>10. ZÁRÓ RENDELKEZÉSEK</strong><br>
                Ez az ÁSZF szigorúan humoros és könnyed megközelítéssel készült. Minden igazi és jogi szándék nélkül jött létre, de reméljük, hogy örömét leli az olvasásában!
            </p>
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
