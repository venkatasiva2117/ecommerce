<?php
session_start();
include '../includes/db.php'; // Include database connection

// Check if a product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$product_id = $_GET['id'];

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<p>Product not found.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Product Details</h1>
            <nav>
                <a href="../index.php">Home</a>
                <a href="cart.php" class="cart-link">
                    <img src="../images/cart-icon.png" alt="Cart" class="cart-icon">
                    Cart
                </a>
            </nav>
        </div>
    </header>
    
    <div class="main-container">
        <main>
            <div class="product-details">
                <h2><?= htmlspecialchars($product['name']); ?></h2>
                <?php if (!empty($product['image'])) : ?>
                    <img src="../images/<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="product-image-large">
                <?php endif; ?>
                <p><strong>Price:</strong> $<?= number_format($product['price'], 2); ?></p>
                <p><strong>Description:</strong> <?= htmlspecialchars($product['description']); ?></p>

                <!-- Add to Cart Button -->
                <form method="POST" action="cart.php">
                    <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                    <button type="submit" name="add_to_cart" class="add-to-cart-button">Add to Cart</button>
                </form>
            </div>
        </main>
    </div>
    
    <footer>
        <p>&copy; <?= date('Y'); ?> Online Store. All rights reserved.</p>
    </footer>
</body>
</html>
