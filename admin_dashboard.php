<?php
session_start();
date_default_timezone_set('Africa/Nairobi'); // Ensure correct timezone

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'config.php';

// ================= HANDLE USER ACTIONS =================

// Delete a user and their cattle
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Delete cattle first
    $stmt = $conn->prepare("DELETE FROM cattle WHERE seller_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Delete user (only non-admins)
    $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role!='admin'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Deactivate user
if (isset($_GET['deactivate'])) {
    $id = intval($_GET['deactivate']);
    $stmt = $conn->prepare("UPDATE users SET status='deactivated' WHERE id=? AND role!='admin'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Activate user
if (isset($_GET['activate'])) {
    $id = intval($_GET['activate']);
    $stmt = $conn->prepare("UPDATE users SET status='approved' WHERE id=? AND role!='admin'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// ================= HANDLE CATTLE ACTIONS =================

// Delete a single cattle listing
if (isset($_GET['delete_cattle'])) {
    $cattle_id = intval($_GET['delete_cattle']);
    $stmt = $conn->prepare("DELETE FROM cattle WHERE id=?");
    $stmt->bind_param("i", $cattle_id);
    $stmt->execute();
}

// ================= HANDLE COUNTRY ACTIONS =================

// Add country
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['country_name'])) {
    $country = trim($_POST['country_name']);
    if (!empty($country)) {
        $stmt = $conn->prepare("INSERT INTO countries (name) VALUES (?)");
        $stmt->bind_param("s", $country);
        $stmt->execute();
    }
}

// Delete country
if (isset($_GET['delete_country'])) {
    $id = intval($_GET['delete_country']);
    $stmt = $conn->prepare("DELETE FROM countries WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// ================= FETCH DATA =================

// Fetch all users (except admin)
$usersResult = $conn->query("SELECT id, name, email, role, status FROM users WHERE role!='admin'");

// Fetch all cattle with seller details (✅ now includes close_time)
$cattleResult = $conn->query("
    SELECT c.id, c.name AS cattle_name, c.breed, c.age, c.weight, c.price, c.close_time, 
           u.name AS seller_name 
    FROM cattle c
    JOIN users u ON c.seller_id = u.id
    ORDER BY c.start_time DESC
");

// Fetch all countries
$countriesResult = $conn->query("SELECT id, name FROM countries ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Masaku Cattle Auction</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 20px;
        }
        h2 { color: darkgreen; margin-top: 40px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        table th {
            background: #007BFF;
            color: #fff;
        }
        .btn {
            display: inline-block;
            padding: 6px 12px;
            background: #007BFF;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn:hover { background: #0056b3; }
        .danger { background: #dc3545; }
        .danger:hover { background: #a71d2a; }
        .success { background: #28a745; }
        .success:hover { background: #1e7e34; }
        .warning { background: #ffc107; color: #000; }
        .warning:hover { background: #e0a800; }
        form { margin: 15px 0; }
        input[type="text"] {
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <h1>Admin Dashboard - Masaku Cattle Auction</h1>

    <!-- MANAGE USERS -->
    <h2>Manage Users</h2>
    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th>
        </tr>
        <?php while ($row = $usersResult->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <?php if ($row['status'] === 'approved'): ?>
                        <a href="?deactivate=<?= $row['id'] ?>" class="btn warning">Deactivate</a>
                    <?php else: ?>
                        <a href="?activate=<?= $row['id'] ?>" class="btn success">Activate</a>
                    <?php endif; ?>
                    <a href="?delete=<?= $row['id'] ?>" class="btn danger" 
                       onclick="return confirm('Delete this user and all their cattle?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- MANAGE CATTLE -->
    <h2>Manage Cattle Listings</h2>
    <table>
        <tr>
            <th>ID</th><th>Cattle Name</th><th>Breed</th><th>Age</th>
            <th>Weight</th><th>Price</th><th>Seller</th><th>Status</th><th>Action</th>
        </tr>
        <?php while ($c = $cattleResult->fetch_assoc()): ?>
            <?php 
                $expired = false;
                if (!empty($c['close_time'])) {
                    $expired = (strtotime($c['close_time']) <= time());
                }
            ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['cattle_name']) ?></td>
                <td><?= htmlspecialchars($c['breed']) ?></td>
                <td><?= $c['age'] ?> yrs</td>
                <td><?= $c['weight'] ?> kg</td>
                <td>Ksh <?= number_format($c['price'], 2) ?></td>
                <td><?= htmlspecialchars($c['seller_name']) ?></td>
                <td style="font-weight:bold; color:<?= $expired ? 'red' : 'green' ?>;">
                    <?= empty($c['close_time']) 
                        ? "No close time set" 
                        : ($expired ? "Closed" : "Active (closes " . date("d M Y H:i", strtotime($c['close_time'])) . ")") ?>
                </td>
                <td>
                    <a href="?delete_cattle=<?= $c['id'] ?>" class="btn danger"
                       onclick="return confirm('Delete this cattle listing?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- MANAGE COUNTRIES -->
    <h2>Manage Countries</h2>
    <form method="post">
        <input type="text" name="country_name" placeholder="Enter country name" required>
        <button type="submit" class="btn success">Add Country</button>
    </form>

    <table>
        <tr>
            <th>ID</th><th>Country</th><th>Action</th>
        </tr>
        <?php while ($row = $countriesResult->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>
                    <a href="?delete_country=<?= $row['id'] ?>" class="btn danger"
                       onclick="return confirm('Delete this country?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="logout.php" class="btn">Logout</a>
    <a href="auctions.php" class="btn">View Auctions</a>
    <a href="upload_cattle.php" class="btn">Upload Cattle</a>
    <a href="my_account.php" class="btn">My account</a>

</body>
</html>
