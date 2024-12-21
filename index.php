<?php
require_once 'includes/header.php';

// Get featured products (popular products based on market trends)
$stmt = $conn->query("SELECT * FROM products WHERE name IN (
    'iPhone 13',
    'PlayStation 5',
    'AirPods Pro',
    'MacBook Air M2',
    'Samsung Galaxy S21',
    'Nintendo Switch OLED'
) ORDER BY name ASC");
$featured_products = $stmt->fetchAll();
?>

<?php if (isset($_SESSION['order_completed'])): ?>
<!-- Thank You Modal -->
<div class="modal fade" id="thankYouModal" tabindex="-1" aria-labelledby="thankYouModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="margin-top: 0; margin-bottom: auto;">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <h3 class="mb-2">Thank You for Your Purchase!</h3>
                <p class="mb-3">Your order has been successfully placed.</p>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('thankYouModal'));
        modal.show();
        <?php unset($_SESSION['order_completed']); ?>
    });
</script>
<?php endif; ?>

<div class="jumbotron bg-light p-5 mb-4 rounded">
    <h1 class="display-4">Welcome to TechMarket</h1>
    <p class="lead">Your one-stop shop for the latest electronics and gadgets.</p>
    <hr class="my-4">
    <p>Browse our wide selection of smartphones, laptops, accessories, and more!</p>
    <a class="btn btn-primary btn-lg" href="products.php" role="button">Shop Now</a>
</div>

<div class="container mt-4">
    <h2 class="mb-4">Featured Products</h2>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($featured_products as $product): ?>
            <div class="col">
                <div class="card h-100">
                    <?php if (!empty($product['image_url'])): ?>
                        <img src="/techmarket/<?php echo htmlspecialchars($product['image_url']); ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="card-text d-flex justify-content-between align-items-center">
                            <strong>Price: $<?php echo number_format($product['price'], 2); ?></strong>
                            <span class="text-muted">Stock: <?php echo $product['stock']; ?></span>
                        </p>
                        <?php if ($product['stock'] > 0): ?>
                            <form action="cart.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-primary">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($featured_products)): ?>
        <div class="alert alert-info">
            No products available at the moment.
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 