<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "Keszenallok01!";
$dbname = "user_auth";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$username = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsername = $_POST['username'] ?? '';
    $inputPassword = $_POST['password'] ?? '';

    // Validate input
    if (empty($inputUsername) || empty($inputPassword)) {
        $message = "Kérjük, töltse ki az összes mezőt.";
    } else {
        // Validate credentials
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $inputUsername);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if the user is verified
            if ($user['is_verified'] == 0) {
                $message = "Kérjük, először hitelesítse a fiókját az emailben kapott linken.";
            } elseif (password_verify($inputPassword, $user['password_hash'])) { // Use 'password_hash' for verification
                // Generate a new token and set cookies
                $token = bin2hex(random_bytes(16));
                setcookie("auth_token", $token, time() + (86400 * 30), "/");

                // Update the token in the database
                $stmt = $conn->prepare("UPDATE users SET token = ? WHERE username = ?");
                $stmt->bind_param("ss", $token, $inputUsername);
                if ($stmt->execute()) {
                    // Redirect to the original URL or homepage
                    $redirectUrl = isset($_GET['ref']) ? $_GET['ref'] : 'webshop.php';
                    header("Location: $redirectUrl");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 16px;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            color: red;
            text-align: center;
        }
        .register-link {
            text-align: center;
            margin-top: 10px;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Bejelentkezés</h2>
        <form action="login.php<?php echo isset($_GET['ref']) ? '?ref=' . urlencode($_GET['ref']) : ''; ?>" method="POST">
            <input type="text" name="username" placeholder="Felhasználónév" required>
            <input type="password" name="password" placeholder="Jelszó" required>
            <button type="submit">Bejelentkezés</button>
        </form>
        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <div class="register-link">
            <p>Nincs még fiókja? <a href="register.php">Regisztráljon itt!</a></p>
        </div>
    </div>
</body>
</html>
