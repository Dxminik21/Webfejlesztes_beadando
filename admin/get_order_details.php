<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    echo 'Unauthorized access';
    exit;
}

if (!isset($_GET['id'])) {
    echo 'No order ID specified';
    exit;
}

$order_id = (int)$_GET['id'];

// Get order details with items
$stmt = $conn->prepare("
    SELECT oi.*, p.name as product_name, p.image_url
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

if (empty($items)) {
    echo 'Order not found or has no items';
    exit;
}
?>

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
            <?php 
            $total = 0;
            foreach ($items as $item): 
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
            ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="../<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                     class="img-thumbnail me-2" style="max-width: 50px;">
                            <?php endif; ?>
                            <?php echo htmlspecialchars($item['product_name']); ?>
                        </div>
                    </td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($subtotal, 2); ?></td>
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
  </rewritten_file> 