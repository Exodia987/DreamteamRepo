<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "Keszenallok01!";
$dbname = "user_auth";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

$message = '';
$debug = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock = isset($_POST['stock']) ? $_POST['stock'] : 0; // Add stock handling

    $debug .= "POST data received: " . print_r($_POST, true) . "\n";

    // Create a sanitized directory name based on the product name
    $dir_name = preg_replace('/[^A-Za-z0-9_-]/', '_', $name);
    $target_dir = "products/" . $dir_name . "/";

    // Create the directory if it doesn't exist
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            $debug .= "Failed to create directory: $target_dir\n";
        } else {
            $debug .= "Directory created: $target_dir\n";
        }
    }

    $image_urls = array();

    // Handle multiple file uploads
    $file_count = count($_FILES['images']['name']);
    $debug .= "Number of files: $file_count\n";

    for($i = 0; $i < $file_count; $i++) {
        $target_file = $target_dir . basename($_FILES["images"]["name"][$i]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        $debug .= "Processing file: " . $_FILES["images"]["name"][$i] . "\n";

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["images"]["tmp_name"][$i]);
        if($check !== false) {
            $uploadOk = 1;
            $debug .= "File is an image - " . $check["mime"] . ".\n";
        } else {
            $message .= "A fájl nem kép: " . $_FILES["images"]["name"][$i] . "<br>";
            $uploadOk = 0;
            $debug .= "File is not an image.\n";
        }

        // Check file size (5MB limit)
        if ($_FILES["images"]["size"][$i] > 5000000) {
            $message .= "A fájl túl nagy: " . $_FILES["images"]["name"][$i] . "<br>";
            $uploadOk = 0;
            $debug .= "File is too large.\n";
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $message .= "Csak JPG, JPEG, PNG & GIF fájlok engedélyezettek: " . $_FILES["images"]["name"][$i] . "<br>";
            $uploadOk = 0;
            $debug .= "Invalid file type.\n";
        }

        // If everything is ok, try to upload file
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["images"]["tmp_name"][$i], $target_file)) {
                $image_urls[] = $target_file;
                $message .= "A fájl sikeresen feltöltve: " . $_FILES["images"]["name"][$i] . "<br>";
                $debug .= "File uploaded successfully.\n";
            } else {
                $error = error_get_last();
                $message .= "Hiba történt a fájl feltöltése során: " . $_FILES["images"]["name"][$i] . "<br>";
                $message .= "Hiba üzenet: " . ($error ? $error['message'] : "Ismeretlen hiba") . "<br>";
                $message .= "Célkönyvtár: " . $target_dir . "<br>";
                $message .= "Célkönyvtár írható: " . (is_writable($target_dir) ? 'Igen' : 'Nem') . "<br>";
                $debug .= "Failed to upload file. Error: " . ($error ? $error['message'] : "Unknown error") . "\n";
            }
        }
    }

    // If we have at least one image, insert the product
    if (count($image_urls) > 0) {
        // We'll use only the first image URL since the table has a single image_url column
        $image_url = $image_urls[0];
        
        $debug .= "Preparing to insert into database.\n";
        $debug .= "Image URL: $image_url\n";

        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, image_url, stock) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $debug .= "Prepare failed: " . $conn->error . "\n";
            die("Prepare failed: " . $conn->error);
        }
        
        $bind_result = $stmt->bind_param("ssdssi", $name, $description, $price, $category, $image_url, $stock);
        if ($bind_result === false) {
            $debug .= "Binding parameters failed: " . $stmt->error . "\n";
            die("Binding parameters failed: " . $stmt->error);
        }
        
        if ($stmt->execute()) {
            $message .= "A termék sikeresen feltöltve.<br>";
            $debug .= "Product inserted successfully.\n";
        } else {
            $message .= "Hiba történt a termék feltöltése során: " . $stmt->error . "<br>";
            $debug .= "Error inserting product: " . $stmt->error . "\n";
        }
        $stmt->close();
    } else {
        $message .= "Legalább egy képet fel kell tölteni a termékhez.<br>";
        $debug .= "No images were uploaded successfully.\n";
    }
}

$categories = ['Egerek', 'Fejhallgatók', 'Billentyűzetek', 'Monitorok', 'Gamer PC-k', 'Egérpadok'];

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Új termék feltöltése - PixelForge</title>
    <link rel="stylesheet" href="webshop.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .upload-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            height: 100px;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>PixelForge</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Főoldal</a></li>
                    <li><a href="index.php#products">Termékek</a></li>
                    <li><a href="index.php#about">Rólunk</a></li>
                    <li><a href="index.php#contact">Kapcsolat</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="upload-section">
            <div class="container">
                <h2>Új termék feltöltése</h2>
                <form class="upload-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Termék neve:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Leírás:</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Ár (Ft):</label>
                        <input type="number" id="price" name="price" min="0" step="1" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Kategória:</label>
                        <select id="category" name="category" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="images">Termék képei:</label>
                        <input type="file" id="images" name="images[]" accept="image/*" multiple required>
                    </div>
                    <button type="submit" class="submit-btn">Termék feltöltése</button>
                </form>
                <?php if ($message): ?>
                    <div class="message">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <!-- Footer content here -->
    </footer>
</body>
</html>
<pre>
<?php echo $debug; ?>
</pre>