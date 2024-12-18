<?php
require_once 'includes/header.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Profile Settings</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="fw-bold">Username:</label>
                    <div><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                
                <form method="POST" action="" class="mb-3">
                    <div class="mb-3">
                        <label class="fw-bold">Email:</label>
                        <div class="input-group">
                            <input type="email" class="form-control" name="email" required
                                   value="<?php echo htmlspecialchars($user['email']); ?>">
                            <button type="submit" class="btn btn-primary">Update Email</button>
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

<?php require_once 'includes/footer.php'; ?> 