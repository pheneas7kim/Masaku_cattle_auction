<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php';

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch user info
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// ✅ Fetch user bid history with cattle details & highest bid
$sql = "
    SELECT 
        b.id AS bid_id, 
        b.bid_amount, 
        b.created_at, 
        c.name AS cattle_name, 
        c.image,
        (SELECT MAX(b2.bid_amount) 
         FROM bids b2 
         WHERE b2.cattle_id = b.cattle_id) AS highest_bid
    FROM bids b
    JOIN cattle c ON b.cattle_id = c.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bids = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Account</title>
    <style>
        body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
    padding: 20px;
}

.container {
    max-width: 1000px;
    margin: auto;
    padding: 0 15px;
}

h2 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 30px;
}

/* Card style */
.card {
    background: #fff;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

/* User Info */
.info h3 {
    margin-bottom: 12px;
    color: #34495e;
}

.info p {
    margin: 8px 0;
    font-size: 15px;
    color: #555;
}

.info b {
    color: #2c3e50;
}

/* Bid history */
.bid-history h3 {
    margin-bottom: 20px;
    color: #27ae60;
}

.bid-item {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    padding: 16px;
    border: 1px solid #eee;
    border-radius: 12px;
    margin-bottom: 16px;
    background: #fafafa;
    transition: all 0.25s ease-in-out;
    gap: 16px;
}

.bid-item:hover {
    transform: translateY(-3px);
    background: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}

.bid-item img {
    width: 100%;
    max-width: 200px;
    height: auto;
    object-fit: cover;           /* Better fit and crop */
    background: #fff;
    padding: 4px;
    border-radius: 12px;
    border: 1px solid #ddd;
    transition: transform 0.3s, border-color 0.3s;
}

.bid-item img:hover {
    transform: scale(1.05);
    border-color: #27ae60;
}

.bid-details {
    flex: 1;
    min-width: 220px;
}

.bid-details b {
    font-size: 16px;
    color: #2c3e50;
}

.bid-details small {
    color: #888;
}

.outbid {
    color: #e74c3c;
    font-weight: bold;
}

.leading {
    color: #27ae60;
    font-weight: bold;
}

/* Buttons */
.delete-btn {
    background: #dc3545;
    color: #fff;
    border: none;
    padding: 8px 14px;
    cursor: pointer;
    border-radius: 8px;
    font-size: 14px;
    transition: background 0.3s;
}

.delete-btn:hover {
    background: #b02a37;
}

a.back {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 16px;
    background: #27ae60;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    transition: background 0.3s;
}

a.back:hover {
    background: #219150;
}

/* Empty state */
.empty {
    text-align: center;
    padding: 30px;
    color: #666;
}

.empty p {
    margin-bottom: 12px;
    font-size: 16px;
}

/* Responsive tweaks */
@media (max-width: 768px) {
    .bid-item {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .bid-item img {
        margin: 0 auto 10px;
    }

    .bid-details {
        text-align: center;
    }
}

    </style>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
<div class="container">

    <h2>My Account</h2>

    <!-- User Info -->
    <div class="card info">
        <h3>👤 Personal Information</h3>
        <p>📛 <b>Name:</b> <?= htmlspecialchars($user['name'] ?? 'N/A'); ?></p>
        <p>📧 <b>Email:</b> <?= htmlspecialchars($user['email'] ?? 'N/A'); ?></p>
        <p>📱 <b>Phone:</b> <?= htmlspecialchars($user['phone'] ?? 'N/A'); ?></p>
    </div>

    <!-- Bid History -->
    <div class="card bid-history">
        <h3>📜 My Bids</h3>
        <?php if ($bids->num_rows > 0): ?>
            <?php while ($bid = $bids->fetch_assoc()): ?>
                <div class="bid-item">
                    <img src="uploads/<?= htmlspecialchars($bid['image']); ?>" alt="Cattle">
                    <div class="bid-details">
                        <p><b><?= htmlspecialchars($bid['cattle_name']); ?></b></p>
                        <p>Your Bid: <b style="color:#27ae60;">Ksh <?= number_format($bid['bid_amount'], 2); ?></b></p>
                        <p>Highest Bid: <b style="color:#d35400;">Ksh <?= number_format($bid['highest_bid'], 2); ?></b></p>
                        <?php if ($bid['bid_amount'] < $bid['highest_bid']): ?>
                            <p class="outbid">⚠️ You’ve been outbid!</p>
                        <?php else: ?>
                            <p class="leading">✅ You’re the highest bidder</p>
                        <?php endif; ?>
                        <p><small>📅 <?= date("d M Y H:i", strtotime($bid['created_at'])); ?></small></p>
                    </div>
                    <form method="POST" action="delete_bid.php">
                        <input type="hidden" name="bid_id" value="<?= $bid['bid_id']; ?>">
                        <button type="submit" class="delete-btn"
                            onclick="return confirm('Are you sure you want to delete this bid?');">
                            🗑️
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty">
                <p>You haven’t placed any bids yet.</p>
                <a href="auctions.php" class="back">Start Bidding ➡</a>
            </div>
        <?php endif; ?>
    </div>

    <div style="text-align:center;">
        <a href="auctions.php" class="back">⬅ Back to Auctions</a>
    </div>
</div>
</body>
</html>
