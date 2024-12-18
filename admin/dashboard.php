<?php
require_once '../includes/header.php';
requireAdmin();

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['delete_product'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$product_id])) {
        setFlashMessage('success', 'Product deleted successfully');
    } else {
        setFlashMessage('danger', 'Failed to delete product');
    }
    redirect('admin/dashboard.php');
}

// Fetch all products
$stmt = $conn->query("SELECT * FROM products ORDER BY name");
$products = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Product Management</h2>
    <a href="add_product.php" class="btn btn-primary">Add New Product</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-warning">Edit</a>
                                    <form method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="delete_product" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="alert alert-info">
                No products found in the database.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 