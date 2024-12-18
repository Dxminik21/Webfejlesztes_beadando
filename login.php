<?php
require_once 'includes/header.php';

if (isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        setFlashMessage('success', 'Welcome back, ' . $user['username'] . '!');
        redirect('index.php');
    } else {
        setFlashMessage('danger', 'Invalid username or password');
    }
}
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all flash messages
    const flashMessages = document.querySelectorAll('.alert');
    
    // Auto hide each flash message after 3 seconds
    flashMessages.forEach(function(message) {
        setTimeout(function() {
            // Add fade out effect
            message.style.transition = 'opacity 0.5s ease-in-out';
            message.style.opacity = '0';
            
            // Remove the element after fade out
            setTimeout(function() {
                message.remove();
            }, 500);
        }, 3000);
    });
});
</script>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Login</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                <p class="mt-3">
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php 
require_once 'includes/footer.php'; 