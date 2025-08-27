<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php';

// Admin info
$name = "Admin User";
$email = "admin@masakuauction.com";
$phone = "0700000000";
$password = password_hash("admin123", PASSWORD_BCRYPT);
$role = "admin";

// Check if admin already exists
$check = $conn->prepare("SELECT id FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "Admin already exists.";
} else {
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $phone, $password, $role);

    if ($stmt->execute()) {
        echo "Admin user created successfully!";
    } else {
        echo " Error: " . $stmt->error;
    }
}
?>
