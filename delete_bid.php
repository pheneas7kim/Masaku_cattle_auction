<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please <a href='login.php'>login</a> first.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bid_id'])) {
    $bid_id = intval($_POST['bid_id']);

    // Verify that the bid belongs to the logged-in user
    $stmt = $conn->prepare("SELECT id FROM bids WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $bid_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Delete the bid
        $stmt = $conn->prepare("DELETE FROM bids WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $bid_id, $user_id);
        if ($stmt->execute()) {
            header("Location: my_account.php?success=1");
            exit;
        } else {
            header("Location: my_account.php?error=1");
            exit;
        }
    } else {
        header("Location: my_account.php?error=unauthorized");
        exit;
    }
} else {
    header("Location: my_account.php?error=invalid");
    exit;
}
