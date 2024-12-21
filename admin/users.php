<?php
require_once '../includes/header.php';
requireAdmin();

// Handle role updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = $_POST['role'] === 'admin' ? 'user' : 'admin';
    
    // Prevent changes to original admin account
    if ($user_id === 1) {
        setFlashMessage('danger', 'Cannot modify the original admin account');
        redirect('../admin/users.php');
        exit;
    }
    
    // Prevent admin from removing their own admin status
    if ($user_id === $_SESSION['user_id'] && $new_role !== 'admin') {
        setFlashMessage('danger', 'You cannot remove your own admin status');
        redirect('../admin/users.php');
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    if ($stmt->execute([$new_role, $user_id])) {
        setFlashMessage('success', 'User role updated successfully');
    } else {
        setFlashMessage('danger', 'Failed to update user role');
    }
    redirect('../admin/users.php');
}

// Fetch all users
$stmt = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>User Management</h2>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-danger' : 'bg-success'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['id'] !== $_SESSION['user_id'] && $user['id'] !== 1): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="role" value="<?php echo $user['role']; ?>">
                                        <button type="submit" name="update_role" class="btn btn-sm <?php echo $user['role'] === 'admin' ? 'btn-warning' : 'btn-primary'; ?>">
                                            <?php echo $user['role'] === 'admin' ? 'Remove Admin' : 'Make Admin'; ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($users)): ?>
            <div class="alert alert-info">
                No users found in the database.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 