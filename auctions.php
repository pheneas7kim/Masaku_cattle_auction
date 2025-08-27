<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config.php';




// Get approved cattle only
$sql = "SELECT id, name,phone, breed, age, weight, price, image, created_at 
        FROM cattle 
        WHERE status = 'approved'
        ORDER BY created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Live Auctions</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f8f8f8; margin: 0; padding: 0; }
    .container { width: 90%; margin: auto; padding: 20px; }
    h1 { text-align: center; margin-bottom: 30px; }
    .auction-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
    .card { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .card img { width: 100%; height: 200px; object-fit: cover; }
    .card-body { padding: 15px; }
    .card-body h3 { margin: 0 0 10px; }
    .card-body p { margin: 5px 0; color: #555; }
    .price { font-size: 18px; font-weight: bold; color: green; }
    .btn { display: inline-block; padding: 10px 15px; margin-top: 10px; background: orange; color: white; text-decoration: none; border-radius: 5px; }
    .btn:hover { background: darkorange; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Approved Cattle Auctions</h1>
    <div class="auction-grid">
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <div class="card">
            <?php if ($row['image']): ?>
              <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Cattle Image">
            <?php else: ?>
              <img src="uploads/default.jpg" alt="No Image">
            <?php endif; ?>
            <div class="card-body">
              <h3><?php echo htmlspecialchars($row['name']); ?></h3>
              <p>Breed: <?php echo htmlspecialchars($row['breed']); ?></p>
              <p>Age: <?php echo htmlspecialchars($row['age']); ?> years</p>
              <p>Weight: <?php echo htmlspecialchars($row['weight']); ?> kg</p>
              <p class="price">KES <?php echo number_format($row['price'], 2); ?></p>
           <p>Seller Phone: <?php echo htmlspecialchars($row['phone']); ?></p>

              <a href="bid.php?cattle_id=<?php echo $row['id']; ?>" class="btn">Place Bid</a>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No approved cattle available for auction at the moment.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
