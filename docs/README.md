# Shopping Site

A straightforward and secure shopping cart system built with PHP. This project provides essential e-commerce functionality including cart management and a secure checkout process.

## Quick Start Guide

### Environment Setup
- Install XAMPP, WAMP, or a similar PHP development environment.
- Ensure you have PHP version 7.0 or higher.
- Make sure MySQL version 5.7 or higher is installed.

### Database Setup
1. Create a MySQL database:
   ```sql
   CREATE DATABASE techmarket;
   USE techmarket;
   ```
2. Import the database schema from `database.sql`.

### Project Installation
1. Download the project files from the repository:
   - You can either download the ZIP file directly from the repository or use Git to fetch the files.
2. Extract the files to your web server directory.
3. Configure the database connection in `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'techmarket');
   ```

### First Login
- Register a new account at `/register.php`.
- Log in with your credentials.
- Start shopping!

## Features

### Shopping Cart
- Add and remove items
- Update quantities
- View cart summary
- Real-time stock checking

### Checkout Process
- Secure order processing
- Stock availability verification
- Transaction management with database rollback support
- Order confirmation
- Clear cart after successful purchase

## User Roles and Permissions

### Admin
- Manage products (add, edit, delete)
- View all orders and user accounts
- Manage user roles and permissions
- Access to administrative dashboard

### Regular User
- Browse products
- Add products to the shopping cart
- Complete purchases
- Update account information

## Security Features

The PHP Shopping Cart system incorporates several security measures to protect user data and ensure safe transactions:

- **Session Management**: User sessions are securely managed to prevent unauthorized access. Sessions are configured with secure parameters, including `httponly` and `samesite` attributes, to mitigate risks such as session hijacking.

- **Password Hashing**: User passwords are hashed using PHP's `password_hash()` function, ensuring that sensitive information is not stored in plain text. This adds an extra layer of security against data breaches.

- **SQL Injection Prevention**: All database interactions utilize prepared statements with bound parameters, which effectively prevent SQL injection attacks. This ensures that user input is treated as data rather than executable code.

- **Input Validation and Sanitization**: User inputs are validated and sanitized before processing. This helps to prevent malicious data from being processed by the application, reducing the risk of attacks such as cross-site scripting (XSS).

- **Cross-Site Request Forgery (CSRF) Protection**: Anti-CSRF tokens are implemented in forms to ensure that requests are coming from authenticated users, preventing unauthorized actions on behalf of users.

- **Error Handling**: Error messages are handled carefully to avoid exposing sensitive information. Generic error messages are displayed to users, while detailed error logs are maintained for developers to troubleshoot issues.

By implementing these security features, the PHP Shopping Cart system aims to provide a safe and secure environment for users to shop online.

## Technologies Used

### Frontend
- HTML
- CSS
- Bootstrap for styling

### Backend
- PHP
- MySQL
- PDO for database operations

### Security
- Password Hashing
- Prepared Statements
- Input Validation and Sanitization
- CSRF Protection

## License

This project is licensed under the Apache License 2.0.