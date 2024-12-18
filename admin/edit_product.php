<?php
require_once '../includes/header.php';
requireAdmin();

if (!isset($_GET['id'])) {
    setFlashMessage('danger', 'No product ID specified');
    redirect('admin/dashboard.php');
}

$product_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    setFlashMessage('danger', 'Product not found');
    redirect('admin/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $category = sanitize($_POST['category']);
    $stock = (int)$_POST['stock'];
    
    $errors = [];
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = "Product name is required";
    }
    
    if ($price <= 0) {
        $errors[] = "Price must be greater than 0";
    }
    
    if ($stock < 0) {
        $errors[] = "Stock cannot be negative";
    }
    
    // Handle image upload
    $image_url = $product['image_url']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG and GIF are allowed.";
        } else {
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Delete old image if exists
                if (!empty($product['image_url']) && file_exists('../' . $product['image_url'])) {
                    unlink('../' . $product['image_url']);
                }
                $image_url = 'uploads/' . $file_name;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, 
                               category = ?, stock = ?, image_url = ? WHERE id = ?");
        
        if ($stmt->execute([$name, $description, $price, $category, $stock, $image_url, $product_id])) {
            setFlashMessage('success', 'Product updated successfully');
            redirect('admin/dashboard.php');
        } else {
            $errors[] = "Failed to update product";
        }
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            setFlashMessage('danger', $error);
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Edit Product</h4>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php 
                            echo htmlspecialchars($product['description']); 
                        ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required
                                   value="<?php echo htmlspecialchars($product['price']); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" required
                               value="<?php echo htmlspecialchars($product['category']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock" required
                               value="<?php echo htmlspecialchars($product['stock']); ?>">
                    </div>
                    
                    <?php if (!empty($product['image_url'])): ?>
                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <div>
                                <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="Current product image" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Update Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="form-text text-muted">Leave empty to keep the current image</small>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 