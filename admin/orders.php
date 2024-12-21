<?php
require_once '../includes/header.php';
requireAdmin();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = sanitize($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        $_SESSION['status_updated'] = true;
        $_SESSION['updated_status'] = ucfirst($new_status);
        $_SESSION['order_id'] = $order_id;
    } else {
        setFlashMessage('danger', 'Failed to update order status');
    }
    redirect('../admin/orders.php');
}

// Fetch all orders with user information
$stmt = $conn->query("
    SELECT o.*, u.username, u.email,
           (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) as total_items
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();
?>

<!-- Status Update Success Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Status Updated</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Order #<span id="updatedOrderId"></span> status has been updated to <strong id="updatedStatus"></strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="dashboard.php" class="btn btn-link p-0 text-dark" style="text-decoration: none; outline: none !important; box-shadow: none !important;">
            <i class="bi bi-arrow-left fs-4"></i>
        </a>
        <h2 class="mb-0">Order Management</h2>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($order['username']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                            </td>
                            <td><?php echo $order['total_items']; ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($order['status']) {
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                        View Details
                                    </button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <select name="status" class="form-select form-select-sm" style="width: auto;" 
                                                onchange="this.form.submit()">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>
                                                Pending
                                            </option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>
                                                Processing
                                            </option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>
                                                Completed
                                            </option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>
                                                Cancelled
                                            </option>
                                        </select>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                No orders found.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                Loading...
            </div>
        </div>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    const contentDiv = document.getElementById('orderDetailsContent');
    
    // Show modal with loading state
    modal.show();
    
    // Fetch order details
    fetch(`get_order_details.php?id=${orderId}`)
        .then(response => response.text())
        .then(html => {
            contentDiv.innerHTML = html;
        })
        .catch(error => {
            contentDiv.innerHTML = 'Error loading order details.';
        });
}

<?php if (isset($_SESSION['status_updated']) && $_SESSION['status_updated']): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
        document.getElementById('updatedOrderId').textContent = '<?php echo $_SESSION['order_id']; ?>';
        document.getElementById('updatedStatus').textContent = '<?php echo $_SESSION['updated_status']; ?>';
        modal.show();
    });
    <?php 
    unset($_SESSION['status_updated']);
    unset($_SESSION['updated_status']);
    unset($_SESSION['order_id']);
    ?>
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?> 