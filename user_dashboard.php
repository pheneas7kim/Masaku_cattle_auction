<?php
session_start();

// Restrict access if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Make sure the session has the user's name
$userName = $_SESSION['user_name'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/style.css">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }
        header {
            background: #333;
            color: #fff;
            padding: 15px 0;
            text-align: center;
        }
        nav ul {
            list-style: none;
            padding: 0;
            margin: 10px 0 0 0;
        }
        nav ul li {
            display: inline;
            margin: 0 15px;
        }
        nav ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
        }
        nav ul li a i {
            margin-right: 6px;
        }
        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin: 30px auto;
            max-width: 1200px;
        }
        .dashboard-card {
            background: #fff;
            border-radius: 12px;
            margin: 15px;
            padding: 25px;
            width: 280px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .dashboard-card i {
            font-size: 60px;
            margin: 20px 0;
        }
        .dashboard-card h3 {
            margin: 15px 0;
            font-size: 22px;
            color: #333;
        }
        .dashboard-card p {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            color: #fff;
            background: #007bff;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
        footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 12px 0;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome <?php echo htmlspecialchars($userName); ?></h1>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i>Home</a></li>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="dashboard-container">

        <div class="dashboard-card">
            <i class="fas fa-user" style="color:#28a745;"></i>
            <h3>My Account</h3>
            <p>Check and manage your personal account.</p>
            <a href="my_account.php" class="btn">Go to Account</a>
        </div>

        <div class="dashboard-card">
            <i class="fas fa-cloud-upload-alt" style="color:#007bff;"></i>
            <h3>Upload Cattle</h3>
            <p>Upload your livestock for auction.</p>
            <a href="upload_cattle.php" class="btn">Sell Livestock</a>
        </div>

        <div class="dashboard-card">
            <i class="fas fa-sign-out-alt" style="color:#dc3545;"></i>
            <h3>Logout</h3>
            <p>End your session securely.</p>
            <a href="logout.php" class="btn">Logout</a>
        </div>

    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Livestock Auction System. All rights reserved.</p>
    </footer>
</body>
</html>
