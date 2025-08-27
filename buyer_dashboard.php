<?php
session_start();
include 'config.php';

// Ensure only buyer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    die("Access denied. Only buyers can access this page. <a href='login.php'>Login</a>");
}

// Fetch only approved cattle
$result = $conn->query("SELECT c.*, u.name AS seller_name 
                        FROM cattle c 
                        JOIN users u ON c.seller_id = u.id 
                        WHERE c.status = 'approved'
                        ORDER BY c.created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">">
    <title>Buyer Dashboard</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        h2 { color: darkblue; }
        .cattle-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin: 10px;
            width: 250px;
            float: left;
            text-align: center;
            background: #f9f9f9;
        }
        img { width: 100%; height: 180px; object-fit: cover; border-radius: 8px; }
        .seller { font-size: 12px; color: gray; }
        .btn-buy {
            display: inline-block;
            padding: 8px 12px;
            margin-top: 10px;
            background: green;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
        .btn-buy:hover { background: darkgreen; }
    </style>
</head>
<body>
      <?php include 'navbar.php'; ?>
    <h2>Buyer Dashboard</h2>
    <p>Welcome, <b><?php echo $_SESSION['name']; ?></b> | <a href="logout.php">Logout</a></p>

    <h3>Available Cattle (Approved Only)</h3>
    
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="cattle-card">
                <img src="uploads/<?php echo $row['image']; ?>" alt="Cattle">
                <h4><?php echo $row['name']; ?></h4>
                <p><b>Breed:</b> <?php echo $row['breed']; ?></p>
                <p><b>Age:</b> <?php echo $row['age']; ?> yrs</p>
                <p><b>Weight:</b> <?php echo $row['weight']; ?> kg</p>
                <p class="seller">Seller: <?php echo $row['seller_name']; ?></p>
                <a class="btn-buy" href="buy_cattle.php?id=<?php echo $row['id']; ?>">Buy Now</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No approved cattle available yet.</p>
    <?php endif; ?>
</body>
</html>
