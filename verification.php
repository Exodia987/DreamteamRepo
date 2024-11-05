<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to capture all output
ob_start();

echo "Debug: Script started<br>";

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "Keszenallok01!";
$dbname = "user_auth";

echo "Debug: Database connection details set<br>";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

if ($conn->connect_error) {
    die("Debug: Connection failed: " . $conn->connect_error . "<br>");
}

echo "Debug: Database connection successful<br>";

$message = "";

// Check if the token exists in the GET parameters
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    echo "Debug: Token received: " . htmlspecialchars($token) . "<br>";

    // Check the token validity
    $stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE verification_token = ?");
    if ($stmt === false) {
        echo "Debug: Prepare failed: " . $conn->error . "<br>";
    } else {
        echo "Debug: Prepare successful<br>";
        
        $stmt->bind_param("s", $token);
        echo "Debug: Parameters bound<br>";
        
        $stmt->execute();
        echo "Debug: Query executed<br>";
        
        $result = $stmt->get_result();
        echo "Debug: Result fetched<br>";

        if ($result->num_rows === 1) {
            echo "Debug: One row found<br>";
            $user = $result->fetch_assoc();
            echo "Debug: User data: " . print_r($user, true) . "<br>";
            
            if ($user['is_verified'] == 0) {
                echo "Debug: User not yet verified<br>";
                // Token is valid and user is not verified, update the user status
                $stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
                if ($stmt === false) {
                    echo "Debug: Update prepare failed: " . $conn->error . "<br>";
                } else {
                    echo "Debug: Update prepare successful<br>";
                    
                    $stmt->bind_param("i", $user['id']);
                    echo "Debug: Update parameters bound<br>";
                    
                    if ($stmt->execute()) {
                        echo "Debug: Update executed<br>";
                        // Check if the update was successful
                        if ($stmt->affected_rows > 0) {
                            $message = "Your account has been successfully verified. You can now log in.";
                            echo "Debug: Account verified successfully<br>";
                        } else {
                            $message = "An error occurred while verifying your account: No rows were updated.";
                            echo "Debug: No rows updated<br>";
                        }
                    } else {
                        $message = "An error occurred while verifying your account: " . $stmt->error;
                        echo "Debug: Update execution failed: " . $stmt->error . "<br>";
                    }
                }
            } else {
                $message = "This account has already been verified.";
                echo "Debug: Account already verified<br>";
            }
        } else {
            $message = "Invalid token.";
            echo "Debug: Invalid token (no matching row found)<br>";
        }
        $stmt->close();
        echo "Debug: Statement closed<br>";
    }
} else {
    $message = "No token provided. Please check your verification email and click on the provided link.";
    echo "Debug: No token provided in GET parameters<br>";
}

$conn->close();
echo "Debug: Database connection closed<br>";

// Capture the debug output
$debug_output = ob_get_clean();

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .verification-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            color: #333;
        }
        .message {
            margin-top: 20px;
        }
        .login-link {
            margin-top: 20px;
        }
        .login-link a {
            color: #28a745;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .debug-output {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 20px;
            width: 80%;
            max-width: 600px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <h2>Account Verification</h2>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <div class="login-link">
            <a href="login.php">Go to Login Page</a>
        </div>
    </div>
    <div class="debug-output">
        <h3>Debug Information:</h3>
        <pre><?php echo htmlspecialchars($debug_output); ?></pre>
    </div>
</body>
</html>