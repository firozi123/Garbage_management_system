<?php
// config/db.php

$host = '127.0.0.1';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$dbname = 'garbage_db';

try {
    // Determine if database exists by connecting without dbname first, and creating it if missing.
    $pdo_setup = new PDO("mysql:host=$host", $user, $pass);
    $pdo_setup->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_setup->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    
    // Now connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ensure the tables are created (useful for beginners)
    $stmtUsers = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        is_blocked TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($stmtUsers);

    // ALTER table to add is_blocked if upgrading from previous version
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_blocked TINYINT(1) DEFAULT 0");
    } catch(PDOException $e) {
        // Ignore column exists error
    }

    $stmtReports = "CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        location VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        status ENUM('Pending', 'Collected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($stmtReports);

    // Insert default admin if no admin exists
    $stmt = $pdo->query("SELECT id FROM users WHERE role='admin'");
    if ($stmt->rowCount() == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $insertAdmin = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $insertAdmin->execute(['Admin', 'admin@gmail.com', $hash]);
    }

} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "<br>Please ensure XAMPP MySQL is running.");
}
?>
