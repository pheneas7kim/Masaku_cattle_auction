<?php
session_start();
include 'config.php';

// Ensure only sellers are logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    die("Access denied. Only sellers can access this page. <a href='login.php'>Login</a>");
}

$seller_id = $_SESSION['user_id'];
$message = "";

// Handle cattle upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $breed = $_POST['breed'];
    $age = intval($_POST['age']);
    $weight = floatval($_POST['weight']);
    $imageName = "";

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $imageName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $message = "Error uploading image.";
        }
    }

    $stmt = $conn->prepare("INSERT INTO cattle (seller_id, name, breed, age, weight, image, status) 
                            VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("issids", $seller_id, $name, $breed, $age, $weight, $imageName);

    if ($stmt->execute()) {
        $message = "✅ Cattle uploaded successfully. Waiting for admin approval.";
    } else {
        $message = "Error: " . $stmt->error;
    }
}

// Fetch seller’s cattle
$result = $conn->query("SELECT * FROM cattle WHERE seller_id = $seller_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seller Dashboard</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        h2 { color: darkgreen; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background: #f4f4f4; }
        img { width: 80px; height: 80px; object-fit: cover; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h2>Seller Dashboard</h2>
    <p>Welcome, <b><?php echo $_SESSION['name']; ?></b> | <a href="logout.php">Logout</a></p>

    <?php if ($message) echo "<p class='success'>$message</p>"; ?>

    <!-- Upload Form -->
    <h3>Upload New Cattle</h3>
    <form method="POST" enctype="multipart/form-data">
        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Breed:</label><br>
        <input type="text" name="breed" required><br><br>

        <label>Age (years):</label><br>
        <input type="number" name="age" required><br><br>

        <label>Weight (kg):</label><br>
        <input type="number" step="0.1" name="weight" required><br><br>

        <label>Image:</label><br>
        <input type="file" name="image" required><br><br>

        <button type="submit">Upload</button>
    </form>

    <!-- My Cattle List -->
    <h3>My Cattle</h3>
    <table>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Breed</th>
            <th>Age</th>
            <th>Weight</th>
            <th>Status</th>
            <th>Uploaded</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><img src="uploads/<?php echo $row['image']; ?>" alt="Cattle"></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['breed']; ?></td>
                <td><?php echo $row['age']; ?> yrs</td>
                <td><?php echo $row['weight']; ?> kg</td>
                <td><?php echo ucfirst($row['status']); ?></td>
                <td><?php echo $row['created_at']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
