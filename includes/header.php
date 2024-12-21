<?php
$root_path = dirname(__DIR__) . '/';
require_once $root_path . 'config/database.php';
require_once $root_path . 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechMarket - Your Electronics Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        html {
            position: relative;
            min-height: 100%;
        }
        body {
            min-height: 100vh;
            padding-top: 0 !important; /* Remove default padding */
        }
        .content-wrapper {
            min-height: calc(100vh + 50px); /* Reduced extra height to 50px */
            padding-bottom: 140px; /* Footer height */
        }
        footer {
            background-color: #212529;
            min-height: 140px;
            width: 100%;
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Flash message handling
        const flashMessages = document.querySelectorAll('.alert');
        flashMessages.forEach(function(message) {
            setTimeout(function() {
                message.style.transition = 'opacity 0.5s ease-in-out';
                message.style.opacity = '0';
                setTimeout(function() {
                    message.remove();
                }, 500);
            }, 3000);
        });
    });
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo dirname($_SERVER['PHP_SELF']) === '/techmarket/admin' ? '../index.php' : 'index.php'; ?>">TechMarket</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo dirname($_SERVER['PHP_SELF']) === '/techmarket/admin' ? '../products.php' : 'products.php'; ?>">Products</a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo dirname($_SERVER['PHP_SELF']) === '/techmarket/admin' ? 'dashboard.php' : 'admin/dashboard.php'; ?>">Admin Dashboard</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <?php if (!isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo dirname($_SERVER['PHP_SELF']) === '/techmarket/admin' ? '../cart.php' : 'cart.php'; ?>">
                                <i class="bi bi-cart"></i> Cart
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo dirname($_SERVER['PHP_SELF']) === '/techmarket/admin' ? '../profile.php' : 'profile.php'; ?>">Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo dirname($_SERVER['PHP_SELF']) === '/techmarket/admin' ? '../logout.php' : 'logout.php'; ?>">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="content-wrapper">
        <div class="container mt-4">
            <?php
            $flash = getFlashMessage();
            if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
