<?php
require_once 'includes/header.php';
requireLogin();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    
    // Check if product exists and has stock
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND stock > 0");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if ($product) {
        if (isset($_SESSION['cart'][$product_id])) {
            if ($_SESSION['cart'][$product_id] < $product['stock']) {
                $_SESSION['cart'][$product_id]++;
                setFlashMessage('success', 'Product quantity updated in cart');
            } else {
                setFlashMessage('warning', 'Cannot add more of this product (stock limit reached)');
            }
        } else {
            $_SESSION['cart'][$product_id] = 1;
            setFlashMessage('success', 'Product added to cart');
        }
    } else {
        setFlashMessage('danger', 'Product not available');
    }
    
    if (!isset($_POST['checkout'])) {
        redirect('products.php');
    }
}

// Handle update quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;
        
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            // Verify stock availability
            $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if ($product && $quantity <= $product['stock']) {
                $_SESSION['cart'][$product_id] = $quantity;
            }
        }
    }
    setFlashMessage('success', 'Cart updated successfully');
    redirect('cart.php');
}

// Get cart items details
$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
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
}
?>

<h2 class="mb-4">Shopping Cart</h2>

<?php if (empty($cart_items)): ?>
    <div class="alert alert-info">
        Your cart is empty. <a href="products.php">Continue shopping</a>
    </div>
<?php else: ?>
    <form method="POST">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
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
                            <td>
                                <input type="number" name="quantity[<?php echo $item['product']['id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" min="0" max="<?php echo $item['product']['stock']; ?>" 
                                       class="form-control" style="width: 80px;">
                            </td>
                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                            <td>
                                <button type="submit" name="quantity[<?php echo $item['product']['id']; ?>]" 
                                        value="0" class="btn btn-danger btn-sm">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="d-flex justify-content-between">
            <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
            <div>
                <button type="submit" name="update_cart" class="btn btn-primary me-2">Update Cart</button>
                <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?> 