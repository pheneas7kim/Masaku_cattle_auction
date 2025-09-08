<?php
session_start();
include 'config.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cattle belonging to this user
$stmt = $conn->prepare("SELECT * FROM cattle WHERE seller_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Cattle</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { width: 90%; margin: 30px auto; background: #fff; padding: 20px; border-radius: 8px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: #eee; }
        img { max-width: 120px; border-radius: 6px; }
        .no-data { text-align: center; padding: 20px; font-style: italic; }
        a { text-decoration: none; color: blue; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🐄 My Cattle Submissions</h2>
        <p style="text-align:center"><a href="upload_cattle.php">➕ Upload New Cattle</a></p>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Breed</th>
                <th>Age (months)</th>
                <th>Weight (kg)</th>
                <th>Price (Ksh)</th>
                <th>Phone</th>
                <th>Image</th>
                <th>Uploaded At</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['breed']); ?></td>
                        <td><?php echo $row['age']; ?></td>
                        <td><?php echo $row['weight']; ?></td>
                        <td><?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td>
                            <?php if (!empty($row['image'])): ?>
                                <img src="uploads/<?php echo $row['image']; ?>" alt="Cattle Image">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9" class="no-data">No cattle uploaded yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
