<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// SMTP settings
$mailHost = 'smtp.rackhost.hu';
$mailUsername = 'digiprint@shador.hu';
$mailPassword = 'Keszenallok01';
$mailPort = 465;

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "Keszenallok01!";
$dbname = "user_auth";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms_accepted = isset($_POST['terms']) ? true : false;

    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match";
    } elseif (!$terms_accepted) {
        $message = "You must accept the terms and conditions";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Username or email already exists";
        } else {
            // Hash the password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $verification_token = bin2hex(random_bytes(16)); // Token for email verification

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, verification_token, is_verified) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("ssss", $username, $email, $password_hash, $verification_token);

            if ($stmt->execute()) {
                // Send verification email
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = $mailHost;
                    $mail->SMTPAuth = true;
                    $mail->Username = $mailUsername;
                    $mail->Password = $mailPassword;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = $mailPort;

                    $mail->setFrom($mailUsername, 'Account Verification');
                    $mail->addAddress($email, $username);
                    $mail->isHTML(true);
                    $mail->Subject = 'Verify Your Account';
                    $verification_link = "https://shador.hu/vizsga/vizsga/verification.php?token=" . $verification_token;
                    $mail->Body = "<p>Dear " . htmlspecialchars($username) . ",</p><hr><p>Please click the following link to verify your account: <a href='" . $verification_link . "'>Verify Account</a></p>";
                    $mail->AltBody = 'Dear ' . $username . ",\n\nPlease visit the following link to verify your account: " . $verification_link;

                    $mail->send();
                    $message = "Registration successful! Please check your email to verify your account.";
                } catch (Exception $e) {
                    $message = "Error sending verification email: {$mail->ErrorInfo}";
                }
            } else {
                $message = "Error: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
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
        .register-container {
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
        input[type="text"], input[type="email"], input[type="password"] {
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
        .terms {
            margin: 10px 0;
        }
        .terms input {
            margin-right: 5px;
        }
    </style>
    <!-- Include Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="register-container">
        <h2>Regisztráció</h2>
        <form method="post" action="">
            <input type="text" name="username" placeholder="Felhasználónév" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Jelszó" required><br>
            <input type="password" name="confirm_password" placeholder="Jelszó megerősítése" required><br>
            <div class="terms">
                <label>
                    <input type="checkbox" name="terms" required>
                    Elfogadom az <a href="aszf.php">ÁSZF</a> (Általános Szerződési Feltételek) 
                </label>
            </div>
            <div class="g-recaptcha" data-sitekey="6LfcE2wqAAAAAFwolQgILt5OParY_5OW12xyuUnE"></div>
            <button type="submit">Regisztrálás</button>
        </form>
        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <div class="register-link">
            <p>Már van fiókja? <a href="login.php">Bejelentkezés itt!</a></p>
        </div>
    </div>
</body>
</html>