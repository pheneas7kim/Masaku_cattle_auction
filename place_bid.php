<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "You must login first."]);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cattle_id = intval($_POST['cattle_id']);
    $bid_amount = floatval($_POST['bid_amount']);

    // Check cattle
    $stmt = $conn->prepare("SELECT seller_id, price FROM cattle WHERE id = ?");
    $stmt->bind_param("i", $cattle_id);
    $stmt->execute();
    $cattle = $stmt->get_result()->fetch_assoc();

    if (!$cattle) {
        echo json_encode(["success" => false, "error" => "Cattle not found."]);
        exit;
    }

    if ($cattle['seller_id'] == $user_id) {
        echo json_encode(["success" => false, "error" => "You cannot bid on your own cattle."]);
        exit;
    }

    // Get current highest bid
    $stmt = $conn->prepare("SELECT MAX(bid_amount) AS highest_bid FROM bids WHERE cattle_id = ?");
    $stmt->bind_param("i", $cattle_id);
    $stmt->execute();
    $highest = $stmt->get_result()->fetch_assoc();
    $highest_bid = $highest['highest_bid'] ?? $cattle['price'];

    if ($bid_amount <= $highest_bid) {
        echo json_encode(["success" => false, "error" => "Bid must be higher than current highest bid."]);
        exit;
    }

    // Insert new bid
    $stmt = $conn->prepare("INSERT INTO bids (cattle_id, user_id, bid_amount, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iid", $cattle_id, $user_id, $bid_amount);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Database error."]);
    }
}
