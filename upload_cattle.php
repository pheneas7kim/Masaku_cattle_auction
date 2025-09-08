<?php
session_start();
date_default_timezone_set('Africa/Nairobi');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please <a href='login.php'>login</a> first.");
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$message   = "";

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM cattle WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $message = $stmt->execute() ? "✅ Cattle deleted successfully." : "❌ Error deleting cattle.";
}

// Handle edit/update
if (isset($_POST['update_id'])) {
    $id = intval($_POST['update_id']);
    $name = $_POST['name'];
    $breed = $_POST['breed'];
    $age = intval($_POST['age']);
    $weight = floatval($_POST['weight']);
    $price = floatval($_POST['price']);
    $close_time = $_POST['close_time'];

    $stmt = $conn->prepare("UPDATE cattle SET name=?, breed=?, age=?, weight=?, price=?, close_time=? WHERE id=? AND seller_id=?");
    $stmt->bind_param("ssiddsii", $name, $breed, $age, $weight, $price, $close_time, $id, $user_id);
    $message = $stmt->execute() ? "✅ Cattle updated successfully." : "❌ Error updating cattle.";
}

// Handle upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['update_id'])) {
    $name = $_POST['name'];
    $breed = $_POST['breed'];
    $age = intval($_POST['age']);
    $weight = floatval($_POST['weight']);
    $price = floatval($_POST['price']);
    $phone = $_POST['phone'];
    $close_time = $_POST['close_time'];
    $imageName = "";

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $imageName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $message = "❌ Error uploading image.";
        }
    }

    $stmt = $conn->prepare("INSERT INTO cattle 
        (seller_id, name, breed, age, weight, price, phone, image, start_time, close_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("issiddsss", $user_id, $name, $breed, $age, $weight, $price, $phone, $imageName, $close_time);
    $message = $stmt->execute() ? "✅ Cattle uploaded successfully. Auction closes at $close_time." : "❌ Error: " . $stmt->error;
}

$result = $conn->query("SELECT * FROM cattle WHERE seller_id = $user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>
<link rel="stylesheet" href="css/style.css">
<style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: #f4f6f9;
        margin: 0;
    }
    h2, p { text-align: center; }
    p.success {
        background: #eaffea; border: 1px solid #c2f0c2;
        padding: 10px; border-radius: 8px;
        width: 80%; margin: 10px auto; color: green;
    }
    .container { display: flex; flex-wrap: wrap; justify-content: center; gap: 25px; padding: 20px; }
    .form-container, .table-container {
        background: #fff; border-radius: 12px;
        padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .form-container { flex: 1 1 300px; max-width: 400px; }
    .table-container { flex: 3 1 700px; }
    form input, form button { width: 100%; padding: 10px; margin-bottom: 12px; border-radius: 6px; border: 1px solid #ccc; }
    form input:focus { border-color: rgba(30, 255, 0, 1); }
    form button { background: #5eff00ff; border: none; color: #fff; font-weight: bold; }
    form button:hover { background: #95fc0eff; cursor: pointer; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: center; }
    th { background: #5eff00ff; color: #fff; }
    tr:nth-child(even) { background: #f9f9f9; }
    td img { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; }
    .edit-btn { background: #ffc107; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
    .edit-btn:hover { background: #e0a800; }
    .delete-btn { background: #dc3545; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; color: white; }
    .delete-btn:hover { background: #b02a37; }
    td.success { color: green; font-weight: bold; }
    td.expired { color: red; font-weight: bold; }

    /* Modal styling */
    .modal {
        display: none; position: fixed; top: 0; left: 0;
        width: 100%; height: 100%; background: rgba(0,0,0,0.6);
        justify-content: center; align-items: center;
    }
    .modal-content {
        background: #fff; padding: 20px; border-radius: 12px;
        max-width: 500px; width: 90%;
        animation: fadeIn 0.3s ease-in-out;
    }
    .modal-header { display: flex; justify-content: space-between; align-items: center; }
    .modal-header h3 { margin: 0; }
    .close-btn {
        background: #dc3545; color: #fff; border: none;
        padding: 5px 10px; border-radius: 6px; cursor: pointer;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>
<body>
<?php include 'navbar.php'; ?>
<h2>User Dashboard</h2>
<p>Welcome, <b><?php echo htmlspecialchars($user_name); ?></b> | <a href="logout.php">Logout</a></p>
<?php if ($message) echo "<p class='success'>$message</p>"; ?>

<div class="container">
    <!-- Upload Form -->
    <div class="form-container">
        <h3>Upload New Cattle</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Full name" required>
            <input type="text" name="breed" placeholder="Breed" required>
            <input type="number" name="age" placeholder="Age (years)" required>
            <input type="number" step="0.1" name="weight" placeholder="Weight (kg)" required>
            <input type="text" name="price" placeholder="Price" required>
            <input type="text" name="phone" placeholder="0700000000" required>
            <input type="datetime-local" name="close_time" required>
            <input type="file" name="image" required>
            <button  type="submit">Upload</button>
        </form>
    </div>

    <!-- My Cattle Table -->
    <div class="table-container">
        <h3>My Cattle</h3>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Breed</th>
                    <th>Age</th>
                    <th>Weight</th>
                    <th>Price</th>
                    <th>Start Time</th>
                    <th>Close Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php $status = (strtotime($row['close_time']) <= time()) ? "closed" : "active"; ?>
                    <tr>
                        <td><img src="uploads/<?php echo $row['image']; ?>" alt="Cattle"></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['breed']; ?></td>
                        <td><?php echo $row['age']; ?> yrs</td>
                        <td><?php echo $row['weight']; ?> kg</td>
                        <td>Ksh <?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo $row['start_time']; ?></td>
                        <td><?php echo $row['close_time']; ?></td>
                        <td class="<?php echo $status === 'closed' ? 'expired' : 'success'; ?>">
                            <?php echo ucfirst($status); ?>
                        </td>
                        <td>
                            <?php if ($status === 'active'): ?>
                                <button class="edit-btn"
                                    onclick="openEditModal(
                                        '<?php echo $row['id']; ?>',
                                        '<?php echo htmlspecialchars($row['name']); ?>',
                                        '<?php echo htmlspecialchars($row['breed']); ?>',
                                        '<?php echo $row['age']; ?>',
                                        '<?php echo $row['weight']; ?>',
                                        '<?php echo $row['price']; ?>',
                                        '<?php echo date('Y-m-d\TH:i', strtotime($row['close_time'])); ?>'
                                    )">Edit</button>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')">
                                <button class="delete-btn">Delete</button>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Cattle</h3>
            <button class="close-btn" onclick="closeEditModal()">X</button>
        </div>
        <form method="POST">
            <input type="hidden" name="update_id" id="edit_id">
            <input type="text" name="name" id="edit_name" required>
            <input type="text" name="breed" id="edit_breed" required>
            <input type="number" name="age" id="edit_age" required>
            <input type="number" step="0.1" name="weight" id="edit_weight" required>
            <input type="text" name="price" id="edit_price" required>
            <input type="datetime-local" name="close_time" id="edit_close_time" required>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, breed, age, weight, price, close_time) {
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_name").value = name;
    document.getElementById("edit_breed").value = breed;
    document.getElementById("edit_age").value = age;
    document.getElementById("edit_weight").value = weight;
    document.getElementById("edit_price").value = price;
    document.getElementById("edit_close_time").value = close_time;
    document.getElementById("editModal").style.display = "flex";
}
function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
}
window.onclick = function(event) {
    if (event.target == document.getElementById("editModal")) {
        closeEditModal();
    }
}
setInterval(() => location.reload(), 60000);
</script>
</body>
</html>
