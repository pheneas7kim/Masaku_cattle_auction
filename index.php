<?php
// index.php
session_start();
include 'config.php'; // database connection

// Fetch only APPROVED cattle/auctions
$query = "SELECT * FROM cattle WHERE status='approved' ORDER BY created_at DESC LIMIT 6";
$result = $conn->query($query);

// If user is logged in, fetch their name
$userName = "";
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $userQuery = "SELECT name FROM users WHERE id = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($fetchedName);
    if ($stmt->fetch()) {
        $userName = $fetchedName;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masaku Cattle Auction</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <!-- Navbar -->
  <?php include 'navbar.php'; ?>

  <!-- Banner -->
  <section class="banner">
    <div class="banner-content">
      <?php if ($userName): ?>
        <h1>Welcome <?php echo htmlspecialchars($userName); ?> to Masaku Cattle Auction</h1>
      <?php else: ?>
        <h1>Welcome to Masaku Cattle Auction</h1>
      <?php endif; ?>
      <p>Buy, Sell & Bid on the best cattle in real-time.</p>
    </div>
  </section>

  <!-- Featured Auctions -->
  <hr><br>
  <section class="featured-auctions">
    <h2>Featured Auctions</h2>
    <div class="auction-list">

      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="auction-item">
            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" 
                 alt="Cattle Image" width="250" height="180">
            <h3><?php echo htmlspecialchars($row['breed']); ?></h3>
            <p>Starting Price: Ksh <?php echo number_format($row['price']); ?></p>
            <p>Age (years): <?php echo number_format($row['age']); ?></p>
           

            <p>Phone: <?php echo $row['phone']; ?></p>

            <!-- If not logged in, redirect to login page -->
            <?php if (!isset($_SESSION['user_id'])): ?>
              <a href="login.php" class="btn">Buy Now</a>
            <?php else: ?>
              <a href="auction_details.php?id=<?php echo $row['id']; ?>" class="btn">Buy Now</a>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No approved cattle available at the moment.</p>
      <?php endif; ?>

    </div>
  </section>

  <hr>

  <section id="about" style="padding:50px; background:#f9f9f9;">
    <h2>About Us</h2>
    <p>
        Welcome to Masaku Cattle Auction, your trusted online platform 
        for buying and selling cattle. Our mission is to bring farmers, buyers, 
        and livestock traders together in a transparent and efficient marketplace.
    </p>
    <h3>Our Mission</h3>
    <p>
        To empower livestock farmers by giving them direct access to buyers 
        and ensuring fair prices through open auctions.
    </p>
    <h3>Why Choose Us?</h3>
    <ul>
        <li>Transparent auction process</li>
        <li> Safe and secure transactions</li>
        <li> Easy registration for farmers & buyers</li>
        <li> Admin approval system to ensure quality listings</li>
    </ul>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <p>&copy; 2025 Masaku Cattle Auction. All Rights Reserved.</p>
      <p>Contact: +254 700 000000 | Email: info@masakuauction.com</p>
      <p>
        <a href="#">Terms & Conditions</a> | 
        <a href="#">Privacy Policy</a>
      </p>
    </div>
  </footer>

</body>
</html>
