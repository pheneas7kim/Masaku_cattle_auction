<?php
session_start();
include 'config.php';

// Restrict access to sellers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $breed = trim($_POST['breed']);
    $age = intval($_POST['age']);
    $weight = floatval($_POST['weight']);
    $phone = trim($_POST['phone']);
    $auction_start = $_POST['auction_start'];
    $auction_end = $_POST['auction_end'];
    $seller_id = $_SESSION['user_id'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imgName = time() . "_" . basename($_FILES['image']['name']);
        $target = "images/" . $imgName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            // Insert into database
            $sql = "INSERT INTO products 
                (seller_id, name, description, price, breed, age, weight, phone, image, auction_start, auction_end) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issdsidssss", $seller_id, $name, $desc, $price, $breed, $age, $weight, $phone, $imgName, $auction_start, $auction_end);

            if ($stmt->execute()) {
                $msg = "Cattle uploaded successfully!";
            } else {
                $msg = "Failed to add product to database.";
            }
        } else {
            $msg = "Failed to upload image.";
        }
    } else {
        $msg = "Please select an image.";
    }
}

// Fetch seller phone from users table (optional: pre-fill)
$userSql = "SELECT phone FROM users WHERE id = ?";
$stmtUser = $conn->prepare($userSql);
$stmtUser->bind_param("i", $_SESSION['user_id']);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Cattle Auction</title>
<link rel="stylesheet" href="css/form.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
     <?php include 'navbar.php'; ?>
<div class="form-container">
    <h2>Add Your Cattle for Auction</h2>
    <?php if (!empty($msg)) { echo "<p>$msg</p>"; } ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <label>Full Name:</label>
        <input type="text" name="name" required>

        <label>Description:</label>
        <textarea name="description" required></textarea>

        <label>Price (KES):</label>
        <input type="number" step="0.01" name="price" required>

        <label>Breed:</label>
        <input type="text" name="breed" required>

        <label>Age (years):</label>
        <input type="number" name="age" required>

        <label>Weight (kg):</label>
        <input type="number" step="0.1" name="weight" required>

        <label>Phone Number:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required
               pattern="^(07\d{8}|01\d{8}|2547\d{8})$"
               title="Enter a valid Kenyan phone number">

        <label>Auction Start:</label>
        <input type="datetime-local" name="auction_start" required>

        <label>Auction End:</label>
        <input type="datetime-local" name="auction_end" required>

        <label>Image:</label>
        <input type="file" name="image" accept="image/*" required>

        <button type="submit">Upload Cattle</button>
    </form>
    <p><a href="seller_dashboard.php">Back to Dashboard</a></p>
</div>
</body>
</html>
