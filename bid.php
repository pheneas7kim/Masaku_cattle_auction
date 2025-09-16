<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config.php';

// Ensure logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please <a href='login.php'>login</a> first.");
}

$user_id = $_SESSION['user_id'];

// Validate cattle id
if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$cattle_id = intval($_GET['id']);

// Fetch cattle details
$stmt = $conn->prepare("SELECT c.*, u.name as seller_name 
                        FROM cattle c 
                        JOIN users u ON c.seller_id = u.id 
                        WHERE c.id = ?");
$stmt->bind_param("i", $cattle_id);
$stmt->execute();
$cattle = $stmt->get_result()->fetch_assoc();

if (!$cattle) {
    die("Cattle not found.");
}

$is_owner = ($user_id == $cattle['seller_id']);

// Fetch highest bid + bidder
$stmt = $conn->prepare("
    SELECT b.bid_amount, u.name AS bidder_name 
    FROM bids b
    JOIN users u ON b.user_id = u.id
    WHERE b.cattle_id = ?
    ORDER BY b.bid_amount DESC 
    LIMIT 1
");
$stmt->bind_param("i", $cattle_id);
$stmt->execute();
$highest = $stmt->get_result()->fetch_assoc();

if ($highest) {
    $highest_bid = $highest['bid_amount'];
    $highest_bidder = $highest['bidder_name'];
} else {
    $highest_bid = $cattle['price'];
    $highest_bidder = "N/A (no bids yet)";
}

// Fetch previous bids
$stmt = $conn->prepare("SELECT b.bid_amount, b.created_at, u.name 
                        FROM bids b 
                        JOIN users u ON b.user_id = u.id 
                        WHERE b.cattle_id = ? 
                        ORDER BY b.created_at DESC");
$stmt->bind_param("i", $cattle_id);
$stmt->execute();
$bids = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Place Bid</title>
    <link rel="stylesheet" href="css/bid.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { width: 70%; margin: auto; background: #fff; padding: 20px; border-radius: 8px; }
        h2 { text-align: center; color: #333; }
        .price { color: green; font-size: 18px; font-weight: bold; }
        .message { text-align: center; margin: 10px 0; display: none; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .bid-history { margin-top: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; }
        .bid-item { margin-bottom: 8px; }
        input[type="number"] { padding: 8px; width: 60%; margin-right: 10px; }
        button { padding: 10px 15px; background: #007bff; color: #fff; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
<div class="container">
    <h2>Place a Bid for <?= htmlspecialchars($cattle['name']); ?></h2>
    <p><b>Breed:</b> <?= htmlspecialchars($cattle['breed']); ?></p>
    <p><b>Age:</b> <?= $cattle['age']; ?> years</p>
    <p><b>Weight:</b> <?= $cattle['weight']; ?> kg</p>
    <p><b>Location:</b> <?= htmlspecialchars($cattle['location']); ?></p>
    <p class="price">Starting Price: Ksh <?= number_format($cattle['price'], 2); ?></p>
    <p><b>Current Highest Bid:</b> 
        <?= is_numeric($highest_bid) 
            ? "Ksh " . number_format($highest_bid, 2) . " by " . htmlspecialchars($highest_bidder) 
            : "No bids yet"; ?>
    </p>

    <!-- ✅ AJAX Status Message -->
    <p class="message" id="statusMessage"></p>

    <!-- Bid Form -->
    <?php if ($is_owner): ?>
        <p style="color:red; font-weight:bold;">⚠ You cannot bid on your own cattle.</p>
    <?php else: ?>
        <form id="bidForm">
            <input type="hidden" name="cattle_id" value="<?= $cattle['id']; ?>">
            <input type="number" 
                   name="bid_amount" 
                   placeholder="Enter your bid (min: <?= $highest_bid + 500; ?>)" 
                   required 
                   min="<?= $highest_bid + 500; ?>">
            <button type="submit">Submit Bid</button>
        </form>
    <?php endif; ?>

    <!-- Previous Bids -->
    <div class="bid-history" id="bidHistory">
        <h3>Previous Bids</h3>
        <?php if ($bids->num_rows > 0): ?>
            <?php while ($bid = $bids->fetch_assoc()): ?>
                <div class="bid-item">
                    <?= htmlspecialchars($bid['name']); ?> bid 
                    <b>Ksh <?= number_format($bid['bid_amount'], 2); ?></b>
                    on <?= date("d M Y H:i", strtotime($bid['created_at'])); ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No bids yet.</p>
        <?php endif; ?>
    </div>

    <p style="text-align:center; margin-top:20px;">
        <a href="auctions.php">⬅ Back to Auctions</a> | 
        <a href="my_account.php">My Account</a>
    </p>
</div>

<!-- ✅ AJAX Script -->
<script>
document.getElementById("bidForm")?.addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const statusMessage = document.getElementById("statusMessage");
    const bidHistory = document.getElementById("bidHistory");

    fetch("place_bid.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        statusMessage.style.display = "block";
        if (data.success) {
            statusMessage.className = "message success";
            statusMessage.textContent = "✅ Bid submitted successfully!";

            // 🔄 Refresh the page after 1.5s so new bid shows
            setTimeout(() => location.reload(), 1500);
        } else {
            statusMessage.className = "message error";
            statusMessage.textContent = "❌ " + data.error;
        }

        // Auto-hide after 5s
        setTimeout(() => { statusMessage.style.display = "none"; }, 5000);
    })
    .catch(err => {
        statusMessage.style.display = "block";
        statusMessage.className = "message error";
        statusMessage.textContent = "⚠ An error occurred. Try again.";
    });
});
</script>

</body>
</html>
