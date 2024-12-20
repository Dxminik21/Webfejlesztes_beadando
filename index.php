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

<div class="jumbotron bg-light p-5 mb-4 rounded">
    <h1 class="display-4">Welcome to TechMarket</h1>
    <p class="lead">Your one-stop shop for the latest electronics and gadgets.</p>
    <hr class="my-4">
    <p>Browse our wide selection of smartphones, laptops, accessories, and more!</p>
    <a class="btn btn-primary btn-lg" href="products.php" role="button">Shop Now</a>
</div>

<h2 class="mb-4">Featured Products</h2>

<div class="row row-cols-1 row-cols-md-3 g-4">
    <?php foreach ($featured_products as $product): ?>
        <div class="col">
            <div class="card h-100">
                <?php if (!empty($product['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                    <p class="card-text">
                        <strong>Price: $<?php echo number_format($product['price'], 2); ?></strong>
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

<?php require_once 'includes/footer.php'; ?> 