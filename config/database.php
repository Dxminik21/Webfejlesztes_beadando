<?php
// XAMPP default configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // XAMPP default username
define('DB_PASS', '');         // XAMPP default password is empty
define('DB_NAME', 'techmarket');

try {
    // First connect without database selected
    $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8");

    // Create database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE " . DB_NAME);

    // Create tables if they don't exist
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS products (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        category VARCHAR(50) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT,
        product_id INT,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )");

    // Create default admin user if no users exist
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $default_admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->exec("INSERT INTO users (username, email, password, role) 
                    VALUES ('admin', 'admin@techmarket.com', '$default_admin_password', 'admin')");
    }

    // Create some sample products if no products exist
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("INSERT INTO products (name, description, price, category, stock) VALUES
            ('iPhone 13', 'Latest Apple iPhone with A15 Bionic chip', 999.99, 'Smartphones', 10),
            ('Samsung Galaxy S21', '5G Android smartphone with 120Hz display', 899.99, 'Smartphones', 15),
            ('MacBook Pro', '14-inch MacBook Pro with M1 Pro chip', 1999.99, 'Laptops', 5),
            ('Dell XPS 15', 'Premium Windows laptop with 4K display', 1799.99, 'Laptops', 8),
            ('AirPods Pro', 'Wireless earbuds with active noise cancellation', 249.99, 'Audio', 20)
        ");
    }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}
?> 