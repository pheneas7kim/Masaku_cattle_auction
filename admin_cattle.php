<?php
session_start();
include 'config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Only admins can view this page. <a href='login.php'>Login here</a>");
}

// Handle approve/reject actions
if (isset($_GET['action']) && isset($_GET['cattle_id'])) {
    $cattle_id = intval($_GET['cattle_id']);
    $action = $_GET['action'];

    if ($action == "approve" || $action == "reject") {
        $stmt = $conn->prepare("UPDATE cattle SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $action, $cattle_id);
        if ($stmt->execute()) {
            echo " Cattle has been $action successfully.";
        } else {
            echo " Error: " . $stmt->error;
        }
    }
}

// Fetch all pending cattle
$result = $conn->query("SELECT c.id, c.name, c.breed, c.age, c.weight, c.image, u.name AS seller_name 
                        FROM cattle c
                        JOIN users u ON c.seller_id = u.id
                        WHERE c.status = 'pending'");

?>

<h2>Admin Cattle Approval</h2>

<?php if ($result->num_rows > 0): ?>
    <table border="1" cellpadding="10">
        <tr>
            <th>Name</th>
            <th>Breed</th>
            <th>Age</th>
            <th>Weight</th>
            <th>Image</th>
            <th>Seller</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['breed']) ?></td>
            <td><?= $row['age'] ?></td>
            <td><?= $row['weight'] ?> kg</td>
            <td>
                <?php if ($row['image']): ?>
                    <img src="uploads/<?= $row['image'] ?>" width="100">
                <?php else: ?>
                    No Image
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['seller_name']) ?></td>
            <td>
                <a href="admin_cattle.php?action=approve&cattle_id=<?= $row['id'] ?>">Approve</a> | 
                <a href="admin_cattle.php?action=reject&cattle_id=<?= $row['id'] ?>">Reject</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No pending cattle at the moment.</p>
<?php endif; ?>
