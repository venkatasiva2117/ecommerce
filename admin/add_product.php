<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

// Handle Product Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    
    // File Upload Handling
    $image = $_FILES['image'];
    $targetDir = "../images/";
    $imageName = basename($image['name']);
    $targetFile = $targetDir . $imageName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validate image file
    $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $validExtensions)) {
        $errorMessage = "Invalid image file type. Only JPG, JPEG, PNG, and GIF are allowed.";
    } elseif (move_uploaded_file($image['tmp_name'], $targetFile)) {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO products (name, price, description, image) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $price, $description, $imageName])) {
            $successMessage = "Product added successfully!";
            header("Location: ../index.php"); // Redirect to home page
            exit();
        } else {
            $errorMessage = "Failed to add product. Try again.";
        }
    } else {
        $errorMessage = "Failed to upload image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <style>
        /* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

/* Container */
.container {
    width: 50%;
    margin: 50px auto;
    background-color: #fff;
    padding: 30px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

/* Heading */
h2 {
    text-align: center;
    color: #333;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
}

label {
    margin-bottom: 5px;
    font-weight: bold;
    color: #555;
}

input[type="text"],
input[type="number"],
textarea,
input[type="file"] {
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    width: 100%;
}

textarea {
    resize: vertical;
    height: 100px;
}

/* Button */
button {
    background-color: #4CAF50;
    color: white;
    padding: 15px;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #45a049;
}

/* Error Message */
.error {
    color: red;
    text-align: center;
    font-size: 16px;
    margin-bottom: 15px;
}

/* Success Message */
.success {
    color: green;
    text-align: center;
    font-size: 16px;
    margin-bottom: 15px;
}

/* Back Link */
.back-link {
    text-align: center;
    margin-top: 20px;
}

.back-link a {
    text-decoration: none;
    color: #4CAF50;
    font-size: 14px;
}

.back-link a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
    <div class="container">
        <h2>Add Product</h2>
        <?php if (isset($errorMessage)) : ?>
            <div class="error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <label for="name">Product Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="price">Price:</label>
            <input type="number" step="0.01" name="price" id="price" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" required></textarea>

            <label for="image">Image:</label>
            <input type="file" name="image" id="image" required>

            <button type="submit" name="add_product">Add Product</button>
        </form>

        <div class="back-link">
            <a href="../index.php">Back to Home</a>
        </div>
    </div>
</body>
</html>
