<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Handle adding a product to the cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    // Check if the product is already in the cart
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing_cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_cart_item) {
        // Update the quantity if the product is already in the cart
        $new_quantity = $existing_cart_item['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$new_quantity, $user_id, $product_id]);
    } else {
        // Insert new product into the cart
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }

    header("Location: cart.php");
    exit();
}

// Handle updating quantity
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$quantity, $user_id, $product_id]);
}

// Handle removing a product from the cart
if (isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
}

// Fetch cart items
$stmt = $conn->prepare("SELECT cart.product_id, cart.id AS cart_id, products.name, products.price, cart.quantity 
                        FROM cart 
                        JOIN products ON cart.product_id = products.id 
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_cost = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <style>
        .cart-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #f2f2f2;
        }
        button {
            background: #ff4d4d;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background: #cc0000;
        }
    </style>
</head>
<body>

<div class="cart-container">
    <h2>Your Shopping Cart</h2>
    <table>
        <tr>
            <th>Product Name</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
            <th>Action</th>
        </tr>
        <?php foreach ($cart_items as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['name']); ?></td>
            <td>$<?= number_format($item['price'], 2); ?></td>
            <td>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                    <input type="number" name="quantity" value="<?= $item['quantity']; ?>" min="1">
                    <button type="submit" name="update_quantity">Update</button>
                </form>
            </td>
            <td>$<?= number_format($item['price'] * $item['quantity'], 2); ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                    <button type="submit" name="remove_from_cart">Remove</button>
                </form>
            </td>
        </tr>
        <?php 
        $total_cost += $item['price'] * $item['quantity'];
        endforeach; 
        ?>
        <tr>
            <td colspan="3"><strong>Total Cost:</strong></td>
            <td><strong>$<?= number_format($total_cost, 2); ?></strong></td>
            <td></td>
        </tr>
    </table>
</div>

</body>
</html>
