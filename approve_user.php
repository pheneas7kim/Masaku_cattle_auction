<?php
session_start();
include 'config.php';


// Only admin can approve
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "UPDATE users SET is_verified = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?msg=User approved successfully");
    } else {
        echo "Error approving user.";
    }
}
