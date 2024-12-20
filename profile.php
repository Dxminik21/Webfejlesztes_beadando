<?php
require_once 'includes/header.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_email'])) {
        $email = sanitize($_POST['email']);
        
        $errors = [];
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address";
        }
        
        // Check if email exists for other users
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = "Email already registered to another account";
        }
        
        if (empty($errors)) {
            // Update email
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            $success = $stmt->execute([$email, $user_id]);
            
            if ($success) {
                setFlashMessage('success', 'Email updated successfully');
                redirect('profile.php');
            } else {
                setFlashMessage('danger', 'Failed to update email');
            }
        } else {
            foreach ($errors as $error) {
                setFlashMessage('danger', $error);
            }
        }
    } elseif (isset($_POST['update_username'])) {
        $username = sanitize($_POST['username']);
        
        $errors = [];
        
        // Validate username
        if (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters long";
        }
        
        // Check if username exists for other users
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = "Username already taken";
        }
        
        if (empty($errors)) {
            // Update username
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $success = $stmt->execute([$username, $user_id]);
            
            if ($success) {
                $_SESSION['username'] = $username; // Update session
                setFlashMessage('success', 'Username updated successfully');
                redirect('profile.php');
            } else {
                setFlashMessage('danger', 'Failed to update username');
            }
        } else {
            foreach ($errors as $error) {
                setFlashMessage('danger', $error);
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <!-- Dedicated space for error messages -->
        <div style="min-height: 60px;">
            <?php
            $flash = getFlashMessage();
            if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Profile Settings</h4>
            </div>
            <div class="card-body">
                <form method="POST" class="mb-3">
                    <div class="mb-3">
                        <label class="fw-bold">Username:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="username" required
                                   value="<?php echo htmlspecialchars($user['username']); ?>">
                            <button type="submit" name="update_username" class="btn btn-primary ms-2">Update</button>
                        </div>
                    </div>
                </form>
                
                <form method="POST" class="mb-3">
                    <div class="mb-3">
                        <label class="fw-bold">Email:</label>
                        <div class="input-group">
                            <input type="email" class="form-control" name="email" required
                                   value="<?php echo htmlspecialchars($user['email']); ?>">
                            <button type="submit" name="update_email" class="btn btn-primary ms-2">Update</button>
                        </div>
                    </div>
                </form>
                
                <div class="d-grid">
                    <a href="change_password.php" class="btn btn-secondary">Change Password</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once 'includes/footer.php'; ?> 