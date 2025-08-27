<?php
session_start();
include 'config.php';

// Allow only admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Only admin can delete. <a href='login.php'>Login</a>");
}

// Ensure ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch cattle to remove image also
    $result = $conn->query("SELECT image FROM cattle WHERE id=$id");
    if ($result && $row = $result->fetch_assoc()) {
        $imagePath = "uploads/" . $row['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath); // delete image file
        }
    }

    // Delete cattle from DB
    $stmt = $conn->prepare("DELETE FROM cattle WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?msg=Deleted successfully");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    echo "Invalid request.";
}
?>
