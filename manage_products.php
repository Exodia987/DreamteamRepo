<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "Keszenallok01!";
$dbname = "user_auth";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getProducts() {
    global $conn;
    $sql = "SELECT * FROM products ORDER BY id DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getProduct($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getProductImages($product_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function addProduct($name, $description, $price, $category, $stock) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, stock) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsi", $name, $description, $price, $category, $stock);
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

function updateProduct($id, $name, $description, $price, $category, $stock) {
    global $conn;
    error_log("Attempting to update product with ID: $id");
    error_log("New values - Name: $name, Description: $description, Price: $price, Category: $category, Stock: $stock");
    
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, stock = ? WHERE id = ?");
    $stmt->bind_param("ssdsii", $name, $description, $price, $category, $stock, $id);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Error updating product: " . $stmt->error);
        error_log("SQL State: " . $stmt->sqlstate);
        error_log("Error No: " . $stmt->errno);
    } else {
        error_log("Product updated successfully. Affected rows: " . $stmt->affected_rows);
    }
    
    return $result;
}

function deleteProduct($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function addProductImage($product_id, $image_url) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
    $stmt->bind_param("is", $product_id, $image_url);
    return $stmt->execute();
}

function deleteProductImage($image_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM product_images WHERE id = ?");
    $stmt->bind_param("i", $image_id);
    return $stmt->execute();
}

$message = '';
$debug = '';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add']) || isset($_POST['update'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $stock = $_POST['stock'];

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

        if (isset($_POST['add'])) {
            $product_id = addProduct($name, $description, $price, $category, $stock);
            if ($product_id) {
                $_SESSION['message'] = "Product added successfully.";
            } else {
                $_SESSION['error'] = "Error adding product.";
            }
        } elseif (isset($_POST['update'])) {
            $id = $_POST['id'];
            error_log("About to call updateProduct with ID: " . $id);
            if (updateProduct($id, $name, $description, $price, $category, $stock)) {
                $_SESSION['message'] = "Product updated successfully.";
                error_log("Product updated successfully.");
            } else {
                $_SESSION['error'] = "Error updating product.";
                error_log("Error updating product.");
            }
        }

        // Handle image uploads
        if (isset($product_id)) {
            $image_field = isset($_POST['add']) ? 'images' : 'new_images';
            if (isset($_FILES[$image_field]) && is_array($_FILES[$image_field]['name'])) {
                foreach ($_FILES[$image_field]['name'] as $key => $name) {
                    if ($_FILES[$image_field]['error'][$key] == UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES[$image_field]['tmp_name'][$key];
                        $name = basename($_FILES[$image_field]['name'][$key]);
                        $target_file = $target_dir . $name;
                        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                        
                        // Check if image file is a actual image or fake image
                        $check = getimagesize($tmp_name);
                        if($check !== false) {
                            // Allow certain file formats
                            if($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg" || $imageFileType == "gif") {
                                if (move_uploaded_file($tmp_name, $target_file)) {
                                    if (addProductImage($product_id, $target_file)) {
                                        $message .= "Image uploaded successfully: $name<br>";
                                    } else {
                                        $message .= "Error uploading image to database: $name<br>";
                                    }
                                } else {
                                    $message .= "Error moving uploaded file: $name<br>";
                                }
                            } else {
                                $message .= "Only JPG, JPEG, PNG & GIF files are allowed: $name<br>";
                            }
                        } else {
                            $message .= "File is not an image: $name<br>";
                        }
                    } elseif ($_FILES[$image_field]['error'][$key] != UPLOAD_ERR_NO_FILE) {
                        $message .= "Error uploading $name<br>";
                    }
                }
            }
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        if (deleteProduct($id)) {
            $_SESSION['message'] = "Product deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting product.";
        }
    } elseif (isset($_POST['delete_image'])) {
        $image_id = $_POST['image_id'];
        if (deleteProductImage($image_id)) {
            $_SESSION['message'] = "Image deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting image.";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$products = getProducts();

// Check if we're in edit mode
$editMode = false;
$editProduct = null;
$editProductImages = [];
if (isset($_GET['edit'])) {
    $editMode = true;
    $editProduct = getProduct($_GET['edit']);
    if (!$editProduct) {
        $_SESSION['error'] = "Product not found.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    $editProductImages = getProductImages($editProduct['id']);
}

$categories = ['Egerek', 'Fejhallgatók', 'Billentyűzetek', 'Monitorok', 'Gamer PC-k', 'Egérpadok', 'Konzolok', 'Kontrollerek'];
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - PixelForge Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Product Management</h1>
        
        <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>{$_SESSION['message']}</div>";
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>{$_SESSION['error']}</div>";
            unset($_SESSION['error']);
        }
        if ($message) {
            echo "<div class='bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4'>$message</div>";
        }
        ?>

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-2xl font-bold mb-4"><?php echo $editMode ? 'Edit' : 'Add New'; ?> Product</h2>
            <?php if ($editMode): ?>
                <input type="hidden" name="id" value="<?php echo $editProduct['id']; ?>">
            <?php endif; ?>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Name</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" type="text" name="name" value="<?php echo $editMode ? htmlspecialchars($editProduct['name']) : ''; ?>" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">Description</label>
                <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="description" name="description" required><?php echo $editMode ? htmlspecialchars($editProduct['description']) : ''; ?></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="price">Price</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="price" type="number" name="price" step="0.01" value="<?php echo $editMode ? $editProduct['price'] : ''; ?>" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="category">Category</label>
                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="category" name="category" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $editMode && $editProduct['category'] == $category ? 'selected' : ''; ?>><?php echo htmlspecialchars($category); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="stock">Stock</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="stock" type="number" name="stock" value="<?php echo $editMode ? $editProduct['stock'] : ''; ?>" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="images">
                    <?php echo $editMode ? 'Add New Images' : 'Product Images'; ?>
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="images" type="file" name="<?php echo $editMode ? 'new_images[]' : 'images[]'; ?>" multiple accept="image/*">
            </div>
            <?php if ($editMode && !empty($editProductImages)): ?>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold mb-2">Current Images</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach ($editProductImages as $image): ?>
                            <div class="relative">
                                <img src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="Product Image" class="w-full h-32 object-cover rounded">
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="absolute top-0 right-0 m-2">
                                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                    <button type="submit" name="delete_image" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded" onclick="return confirm('Are you sure you want to delete this image?')">Delete</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" name="<?php echo $editMode ? 'update' : 'add'; ?>">
                    <?php echo $editMode ? 'Update' : 'Add'; ?> Product
                </button>
                <?php if ($editMode): ?>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Cancel
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <h2 class="text-2xl font-bold mb-4">Product List</h2>
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Description</th>
                        <th class="px-4 py-2">Price</th>
                        <th class="px-4 py-2">Category</th>
                        <th class="px-4 py-2">Images</th>
                        <th class="px-4 py-2">Stock</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td class="border px-4 py-2"><?php echo $product['id']; ?></td>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($product['name']); ?></td>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></td>
                        <td class="border px-4 py-2"><?php echo number_format($product['price'], 2); ?> Ft</td>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($product['category']); ?></td>
                        <td class="border px-4 py-2">
                            <?php
                            $productImages = getProductImages($product['id']);
                            foreach ($productImages as $index => $image):
                                if ($index < 3):
                            ?>
                                <img src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="Product Image" class="w-16 h-16 object-cover inline-block mr-2">
                            <?php
                                endif;
                            endforeach;
                            if (count($productImages) > 3):
                            ?>
                                <span class="text-gray-600">+<?php echo count($productImages) - 3; ?> more</span>
                            <?php endif; ?>
                        </td>
                        <td class="border px-4 py-2"><?php echo $product['stock']; ?></td>
                        <td class="border px-4 py-2">
                            <a href="<?php echo $_SERVER['PHP_SELF'] . '?edit=' . $product['id']; ?>" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded">Edit</a>
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="inline">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="delete" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>