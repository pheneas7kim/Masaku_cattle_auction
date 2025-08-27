<?php
include 'config.php';

$cattle_id = $_GET['id']; // cattle ID from URL

$sql = "SELECT c.*, u.name AS seller_name, u.phone AS seller_phone, u.email AS seller_email 
        FROM cattle c
        JOIN users u ON c.seller_id = u.id
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cattle_id);
$stmt->execute();
$result = $stmt->get_result();
$cattle = $result->fetch_assoc();
?>

<h2><?php echo $cattle['name']; ?> (<?php echo $cattle['breed']; ?>)</h2>
<p>Age: <?php echo $cattle['age']; ?> years</p>
<p>Weight: <?php echo $cattle['weight']; ?> kg</p>

<h3>Seller Contact</h3>
<p>Name: <?php echo $cattle['seller_name']; ?></p>
<p>Phone: <?php echo $cattle['seller_phone']; ?></p>
<p>Email: <?php echo $cattle['seller_email']; ?></p>
