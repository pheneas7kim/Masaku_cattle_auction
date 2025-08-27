<?php
session_start();
include 'config.php';

// Ensure seller is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$cattle_id = $_GET['id'] ?? 0;

// Fetch cattle details
$stmt = $conn->prepare("SELECT * FROM cattle WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $cattle_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$cattle = $result->fetch_assoc();

if (!$cattle) {
    die("Cattle not found or you don’t have permission to edit.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $breed = $_POST['breed'];
    $age   = $_POST['age'];
    $price = $_POST['price'];
    $weight = $_POST['weight'];

    // Check if new image is uploaded
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = "uploads/" . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $updateQuery = "UPDATE cattle SET name=?, breed=?, age=?, price=?, weight=?, image=?, status='pending' WHERE id=? AND seller_id=?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ssiddsii", $name, $breed, $age, $price, $weight, $imageName, $cattle_id, $seller_id);
        }
    } else {
        // Update without changing image
        $updateQuery = "UPDATE cattle SET name=?, breed=?, age=?, price=?, weight=?, status='pending' WHERE id=? AND seller_id=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssiddii", $name, $breed, $age, $price, $weight, $cattle_id, $seller_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Cattle updated successfully! Pending admin approval again.'); window.location='seller_dashboard.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Cattle</title>
</head>
<body>
<h2>Edit Cattle</h2>
<form method="post" enctype="multipart/form-data">
    <label>Name:</label><br>
    <input type="text" name="name" value="<?php echo htmlspecialchars($cattle['name']); ?>" required><br>

    <label>Breed:</label><br>
    <input type="text" name="breed" value="<?php echo htmlspecialchars($cattle['breed']); ?>" required><br>

    <label>Age:</label><br>
    <input type="number" name="age" value="<?php echo $cattle['age']; ?>" required><br>

    <label>Price (Ksh):</label><br>
    <input type="number" name="price" value="<?php echo $cattle['price']; ?>" required><br>

    <label>Weight (kg):</label><br>
    <input type="number" name="weight" value="<?php echo $cattle['weight']; ?>" required><br>


    <label>phone:</label><br>
    <input type="text" name="phone" value="<?php echo $cattle['phone']; ?>" required><br>

    <label>Current Image:</label><br>
    <img src="uploads/<?php echo $cattle['image']; ?>" width="150"><br>
    <input type="file" name="image"><br><br>

    <button type="submit">Update</button>
</form>
</body>
</html>
