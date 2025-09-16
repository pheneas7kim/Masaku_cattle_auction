<?php
session_start();
include 'config.php';

// ✅ Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Approve or Reject user
if (isset($_GET['action']) && isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === "approve") {
        $sql = "UPDATE users SET status = 'approved' WHERE id = ?";
    } elseif ($action === "reject") {
        $sql = "UPDATE users SET status = 'rejected' WHERE id = ?";
    }

    if (isset($sql)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    }

    header("Location: admin_users.php"); // Refresh page
    exit();
}

// Fetch all users
$result = $conn->query("SELECT id, name, email, role, is_verified, status FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Users</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ccc;
            padding: 10px;
        }
        th {
            background: #007BFF;
            color: white;
        }
        td {
            text-align: center;
        }
        a.btn {
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
        }
        .approve { background: green; }
        .reject { background: red; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Admin - Manage Users</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>OTP Verified?</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= $row['name']; ?></td>
            <td><?= $row['email']; ?></td>
            <td><?= ucfirst($row['role']); ?></td>
            <td><?= $row['is_verified'] ? "Yes" : " No"; ?></td>
            <td><?= ucfirst($row['status']); ?></td>
            <td>
                <?php if ($row['status'] === "pending") { ?>
                    <a class="btn approve" href="admin_users.php?action=approve&id=<?= $row['id']; ?>">Approve</a>
                    <a class="btn reject" href="admin_users.php?action=reject&id=<?= $row['id']; ?>">Reject</a>
                <?php } else { ?>
                    <em>No action</em>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
