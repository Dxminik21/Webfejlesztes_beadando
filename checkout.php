<?php
require_once 'includes/header.php';
requireLogin();

if (empty($_SESSION['cart'])) {
    setFlashMessage('warning', 'Your cart is empty');
    redirect('cart.php');
}

// Get cart items and calculate total
$cart_items = [];
$total = 0;

$product_ids = array_keys($_SESSION['cart']);
$placeholders = str_repeat('?,', count($product_ids) - 1) . '?';

$stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($product_ids);
$products = $stmt->fetchAll();

foreach ($products as $product) {
    $quantity = $_SESSION['cart'][$product['id']];
    $subtotal = $product['price'] * $quantity;
    $total += $subtotal;
    
    $cart_items[] = [
        'product' => $product,
        'quantity' => $quantity,
        'subtotal' => $subtotal
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate stock availability again
    foreach ($cart_items as $item) {
        if ($item['quantity'] > $item['product']['stock']) {
            $errors[] = "Sorry, {$item['product']['name']} only has {$item['product']['stock']} items in stock";
        }
    }
    
    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            // Create order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $total]);
            $order_id = $conn->lastInsertId();
            
            // Create order items and update stock
            $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            
            foreach ($cart_items as $item) {
                // Add order item
                $stmt_items->execute([
                    $order_id,
                    $item['product']['id'],
                    $item['quantity'],
                    $item['product']['price']
                ]);
                
                // Update stock
                $stmt_stock->execute([
                    $item['quantity'],
                    $item['product']['id']
                ]);
            }
            
            $conn->commit();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Set order completion flag for modal
            $_SESSION['order_completed'] = true;
            redirect('index.php');
            
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = "An error occurred while processing your order. Please try again.";
        }
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            setFlashMessage('danger', $error);
        }
    }
}
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Order Summary</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($item['product']['image_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['product']['image_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['product']['name']); ?>"
                                                     class="img-thumbnail me-2" style="max-width: 50px;">
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($item['product']['name']); ?>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format($item['product']['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Complete Order</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <p class="mb-3">
                        By clicking "Place Order", you agree to purchase the items in your cart 
                        for the total amount shown.
                    </p>
                    
                    <div class="d-grid gap-2">
                        <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                        <button type="submit" class="btn btn-success">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 