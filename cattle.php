<?php
session_start();
include 'config.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    die("❌ Access denied. Only sellers can upload cattle. <a href='login.php'>Login here</a>");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = $_POST['name'];
    $breed       = $_POST['breed'];
    $age         = $_POST['age'];
    $weight      = $_POST['weight'];
    $description = $_POST['description'];
    $seller_id   = $_SESSION['user_id'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check valid image type
        $valid_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $valid_types)) {
            die("❌ Invalid image format. Only JPG, PNG, GIF allowed.");
        }

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            die("❌ Failed to upload image.");
        }
    } else {
        $image_name = NULL; // No image uploaded
    }

    // Insert into cattle table with status 'pending'
    $sql = "INSERT INTO cattle (seller_id, name, breed, age, weight, image, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issidss", $seller_id, $name, $breed, $age, $weight, $image_name);

    if ($stmt->execute()) {
        echo "✅ Cattle uploaded successfully! Awaiting admin approval.";
    } else {
        echo "❌ Error: " . $stmt->error;
    }
}
?>

<h2>Upload Cattle</h2>
<form method="POST" action="cattle.php" enctype="multipart/form-data">
    Name: <input type="text" name="name" required><br>
    Breed: <input type="text" name="breed" required><br>
    Age (years): <input type="number" name="age" required><br>
    Weight (kg): <input type="number" step="0.01" name="weight" required><br>
    Description: <textarea name="description" rows="4" cols="50"></textarea><br>
    Image: <input type="file" name="image"><br>
    <button type="submit">Upload Cattle</button>
</form>
