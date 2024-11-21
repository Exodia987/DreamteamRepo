<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "Keszenallok01!";
$dbname = "user_auth";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = '';
$message = '';
$referrer = isset($_GET['ref']) ? urldecode($_GET['ref']) : 'pixelforge';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsername = $_POST['username'] ?? '';
    $inputPassword = $_POST['password'] ?? '';

    if (empty($inputUsername) || empty($inputPassword)) {
        $message = "Kérjük, töltse ki az összes mezőt.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $inputUsername);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($user['is_verified'] == 0) {
                $message = "Kérjük, először hitelesítse a fiókját az emailben kapott linken.";
            } elseif (password_verify($inputPassword, $user['password_hash'])) {
                $token = bin2hex(random_bytes(32));
                
                $stmt = $conn->prepare("UPDATE users SET token = ? WHERE username = ?");
                $stmt->bind_param("ss", $token, $inputUsername);
                
                if ($stmt->execute()) {
                    setcookie("auth_token", $token, [
                        'expires' => time() + (86400 * 30),
                        'path' => '/',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                    
                    setcookie("username", $inputUsername, [
                        'expires' => time() + (86400 * 30),
                        'path' => '/',
                        'secure' => true,
                        'httponly' => false,
                        'samesite' => 'Strict'
                    ]);
                    
                    header("Location: " . $referrer);
                    exit();
                } else {
                    $message = "Hiba a token frissítésekor.";
                }
            } else {
                $message = "Hibás felhasználónév vagy jelszó.";
            }
        } else {
            $message = "Hibás felhasználónév vagy jelszó.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Bejelentkezés</h2>
        <form action="login.php<?php echo isset($_GET['ref']) ? '?ref=' . urlencode($_GET['ref']) : ''; ?>" method="POST">
            <div class="mb-4">
                <input type="text" name="username" placeholder="Felhasználónév" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-6">
                <input type="password" name="password" placeholder="Jelszó" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition duration-300">
                Bejelentkezés
            </button>
        </form>
        <?php if ($message): ?>
            <p class="mt-4 text-red-500 text-center"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <div class="mt-4 text-center">
            <p>Nincs még fiókja? <a href="register.php<?php echo isset($_GET['ref']) ? '?ref=' . urlencode($_GET['ref']) : ''; ?>" class="text-blue-500 hover:underline">Regisztráljon itt!</a></p>
        </div>
        <div class="mt-4 text-center">
            <a href="<?php echo htmlspecialchars($referrer); ?>" class="text-gray-600 hover:text-blue-500 transition duration-300">Vissza az előző oldalra</a>
        </div>
    </div>
</body>
</html>