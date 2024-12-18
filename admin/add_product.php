<?php
require_once '../includes/header.php';
requireAdmin();

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
    $image_url = '';
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
                $image_url = 'uploads/' . $file_name;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, stock, image_url) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $description, $price, $category, $stock, $image_url])) {
            setFlashMessage('success', 'Product added successfully');
            redirect('admin/dashboard.php');
        } else {
            $errors[] = "Failed to add product";
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
                <h4 class="mb-0">Add New Product</h4>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php 
                            echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; 
                        ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required
                                   value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" required
                               value="<?php echo isset($_POST['category']) ? htmlspecialchars($_POST['category']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock" required
                               value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : '0'; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 