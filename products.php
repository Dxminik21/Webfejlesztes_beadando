<?php
require_once 'includes/header.php';

// Get filters from URL parameters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'name_asc';

// Build the base query
$query = "SELECT * FROM products WHERE 1=1";
$params = [];

// Add search filter
if (!empty($search)) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Add category filter
if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

// Add sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY price DESC";
        break;
    case 'name_desc':
        $query .= " ORDER BY name DESC";
        break;
    default:
        $query .= " ORDER BY name ASC";
}

// Get categories for filter
$stmt = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Execute the main query
$stmt = $conn->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-8">
        <form class="d-flex" method="GET">
            <input type="text" name="search" class="form-control me-2" placeholder="Search products..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
    <div class="col-md-4">
        <div class="d-flex justify-content-end">
            <select class="form-select w-auto" name="sort" onchange="this.form.submit()">
                <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Categories</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="products.php" class="list-group-item list-group-item-action <?php echo empty($category) ? 'active' : ''; ?>">
                        All Categories
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="?category=<?php echo urlencode($cat); ?>" 
                           class="list-group-item list-group-item-action <?php echo $category === $cat ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($products as $product): ?>
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
        
        <?php if (empty($products)): ?>
            <div class="alert alert-info">
                No products found matching your criteria.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 