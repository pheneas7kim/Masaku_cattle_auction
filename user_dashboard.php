<?php
session_start();

//  Restrict access if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$userName = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url("../uploads/bull1.webp") no-repeat center center fixed;
            background-size: cover;
        }
        .dashboard-container {
            width: 90%;
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: rgba(255,255,255,0.95);
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin: 10px;
            text-align: center;
            width: 250px;
            background: #f9f9f9;
            float: left;
        }
        .card img {
            max-width: 100%;
            height: 180px;
            border-radius: 8px;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: rgba(43, 255, 0, 1);
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn:hover {
            background: #b38300ff;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
      <link rel="stylesheet" href="css/style.css">
</head>
<body>
 <?php include 'navbar.php'; ?>
<div class="dashboard-container">
    <h1>Welcome <?php echo htmlspecialchars($userName); ?> </h1>

    <div class="actions">
        <a href="my_account.php" class="btn">My account</a>
        <a href="upload_cattle.php" class="btn">Upload Cattle</a>
        <a href="auctions.php" class="btn">Buy cattle</a>
        <a href="logout.php" class="btn">Logout</a>
    </div>

    <h2>our  services</h2>
    <div class="clearfix">
        <?php
        include 'config.php';

        $sql = "SELECT id, breed, age, price, image FROM cattle WHERE status='available'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='card'>
                        <img src='uploads/" . htmlspecialchars($row['image']) . "' alt='Cattle'>
                        <h3>" . htmlspecialchars($row['breed']) . "</h3>
                        <p>Age: " . htmlspecialchars($row['age']) . " years</p>
                        <p>Price: Ksh " . htmlspecialchars($row['price']) . "</p>
                        <a href='buy_cattle.php?id=" . $row['id'] . "' class='btn'>Buy</a>
                      </div>";
            }
        } else {
            echo "<p>No cattle available at the moment.</p>";
        }
        ?>
    </div>
</div>
</body>
</html>
