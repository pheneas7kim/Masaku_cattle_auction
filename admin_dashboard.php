<?php
session_start();
include 'config.php';

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admins only. <a href='login.php'>Login</a>");
}

$message = "";

// Approve/Reject cattle
if (isset($_GET['action']) && isset($_GET['id'])) {
    $cattle_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == 'approve') {
        $stmt = $conn->prepare("UPDATE cattle SET status='approved' WHERE id=?");
        $stmt->bind_param("i", $cattle_id);
        $stmt->execute();
        $message = "✅ Cattle approved successfully.";
    } elseif ($action == 'reject') {
        $stmt = $conn->prepare("UPDATE cattle SET status='rejected' WHERE id=?");
        $stmt->bind_param("i", $cattle_id);
        $stmt->execute();
        $message = "❌ Cattle rejected.";
    }
}

// Fetch all cattle
$result = $conn->query("SELECT cattle.*, users.name AS seller_name 
                        FROM cattle 
                        JOIN users ON cattle.seller_id = users.id 
                        ORDER BY cattle.created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
   <link rel="stylesheet" href="css/style.css">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        h2 { color: darkred; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background: #f4f4f4; }
        img { width: 80px; height: 80px; object-fit: cover; }
        .success { color: green; }
        .error { color: red; }
        .btn { padding: 5px 10px; text-decoration: none; border-radius: 5px; }
        .approve { background: green; color: white; }
        .reject { background: red; color: white; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <h2>Admin Dashboard</h2>
    <p>Welcome, <b><?php echo $_SESSION['name']; ?></b> | <a href="logout.php">Logout</a></p>

    <?php if ($message) echo "<p class='success'>$message</p>"; ?>

    <h3>All Cattle Uploads</h3>
    <table>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Breed</th>
            <th>Age</th>
            <th>Weight</th>
            <th>Seller</th>
            <th>Status</th>
            <th>Uploaded</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><img src="uploads/<?php echo $row['image']; ?>" alt="Cattle"></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['breed']; ?></td>
                <td><?php echo $row['age']; ?> yrs</td>
                <td><?php echo $row['weight']; ?> kg</td>
                <td><?php echo $row['seller_name']; ?></td>
                <td><?php echo ucfirst($row['status']); ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td>
                    <?php if ($row['status'] == 'pending'): ?>
                        <a class="btn approve" href="?action=approve&id=<?php echo $row['id']; ?>">Approve</a>
                        <a class="btn reject" href="?action=reject&id=<?php echo $row['id']; ?>">Reject</a>
                    <?php else: ?>
                        <i><?php echo ucfirst($row['status']); ?></i>
                    <?php endif; ?>
                    <a href="delete_cattle.php?id=<?php echo $row['id']; ?>" 
                   onclick="return confirm('Are you sure you want to delete this cattle?');">
                   Delete
                </a>
                </td>
                
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
