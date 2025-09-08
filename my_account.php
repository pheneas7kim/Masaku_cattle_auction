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
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        /* User Info */
        .info p {
            margin: 8px 0;
            font-size: 15px;
            color: #444;
        }
        .info b {
            color: #2c3e50;
        }

        /* Bid history */
        .bid-history h3 {
            margin-bottom: 15px;
            color: #27ae60;
        }
        .bid-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 12px;
            background: #fafafa;
            transition: transform 0.2s;
        }
        .bid-item:hover {
            transform: scale(1.01);
            background: #fff;
        }
        .bid-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .bid-details {
            flex: 1;
        }
        .bid-details b {
            font-size: 16px;
            color: #2c3e50;
        }
        .bid-details small {
            color: #888;
        }

        /* Buttons */
        .delete-btn {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 6px;
            font-size: 14px;
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
            padding: 20px;
            color: #666;
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
                        <p>Bid: <b style="color:#27ae60;">Ksh <?= number_format($bid['bid_amount'], 2); ?></b></p>
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
