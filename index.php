<?php
session_start();
date_default_timezone_set('Africa/Nairobi'); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

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

// Fetch only the latest 6 cattle
$sql = "SELECT c.*, u.name AS seller_name 
        FROM cattle c 
        JOIN users u ON c.seller_id = u.id 
        ORDER BY c.start_time DESC 
        LIMIT 6";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masaku Cattle Auction</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
  <style>
    .countdown { font-weight: bold; color: #e63946; }
    .card img { width: 100%; height: 200px; object-fit: cover; border-radius: 10px; }
    .btn-primary { background: #16a34a; color: white; padding: 10px 20px; border-radius: 8px; font-weight: bold; }
    .btn-primary:hover { background: #15803d; }
    .btn-secondary { background: #f97316; color: white; padding: 10px 20px; border-radius: 8px; font-weight: bold; }
    .btn-secondary:hover { background: #ea580c; }
  </style>
</head>
<body class="bg-gray-50">

  <!-- Navbar -->
  <?php include 'navbar.php'; ?>

<!-- 🌟 HERO / BANNER SECTION -->
<section class="relative h-screen pt-20 flex items-center justify-center text-center">
  <!-- Background Image -->
  <div class="absolute inset-0">
    <img src="uploads/1756195892_bull1.webp" alt="Healthy bulls ready for auction in Kenya" class="w-full h-full object-cover">
    <!-- Gradient Overlay (lighter) -->
    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/40 to-black/10"></div>
  </div>

  <!-- Content -->
  <div class="relative z-10 px-6 sm:px-12 text-grey-100">
   <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold mb-4 animate-fadeIn text-yellow-300">
  Welcome to Masaku Cattle Auction
</h1>

    <p class="text-lg sm:text-xl md:text-2xl max-w-2xl mx-auto mb-6 animate-fadeIn delay-200 text-yellow-400">
  Buy and sell cattle with trust, transparency, and ease.
</p>

    <!-- CTA Buttons -->
    <div class="flex flex-col sm:flex-row justify-center gap-4 mb-8 animate-fadeIn delay-300">
      <a href="auctions.php" 
         class="px-6 py-3 bg-orange-500 hover:bg-orange-600 rounded-full shadow-lg transition transform hover:scale-105 text-white font-semibold">
        View Auctions
      </a>
      <a href="sell.php" 
         class="px-6 py-3 bg-green-600 hover:bg-green-700 rounded-full shadow-lg transition transform hover:scale-105 text-white font-semibold">
        Sell Your Cattle
      </a>
    </div>

    <!-- 🔍 Search Form -->
    <form method="GET" action="auctions.php" class="flex justify-center w-full max-w-lg mx-auto animate-fadeIn delay-500">
      <input type="text" name="search" placeholder="Search cattle by breed" 
             class="flex-grow px-4 py-3 rounded-l-full border-none shadow-lg text-gray-800 focus:ring-2 focus:ring-orange-400"
             aria-label="Search cattle">
      <button type="submit" 
              class="px-6 py-3 bg-green-600 text-white rounded-r-full shadow-lg hover:bg-green-700 transition font-semibold">
        Search
      </button>
    </form>
  </div>
</section>

<!-- ✨ Animations -->
<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
  animation: fadeIn 0.8s ease-out forwards;
}
.animate-fadeIn.delay-200 { animation-delay: 0.2s; }
.animate-fadeIn.delay-300 { animation-delay: 0.3s; }
.animate-fadeIn.delay-500 { animation-delay: 0.5s; }
</style>

   

  <!-- FEATURED AUCTIONS -->
  <section class="py-16">
    <h2 class="text-center text-3xl font-bold mb-12">Auction - Latest Cattle</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 max-w-7xl mx-auto px-6">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): 
          $expired = (strtotime($row['close_time']) <= time());
        ?>
          <div class="bg-white rounded-2xl shadow-md p-5 hover:shadow-xl transition">
            <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="Cattle">
            
            <p><b>Breed:</b> <?= htmlspecialchars($row['breed']) ?></p>
            <p><b>Age:</b> <?= $row['age'] ?> years</p>
            <p><b>Weight:</b> <?= $row['weight'] ?> kg</p>
            <p class="text-lg font-bold text-red-600">Ksh <?= number_format($row['price'], 2) ?></p>
            <p>📞 <?= htmlspecialchars($row['phone']) ?></p>
            

            <p class="mt-2 status <?= $expired ? 'text-red-600' : 'text-green-600' ?>">
              <?= $expired ? "Closed" : "Active (closes " . date("d M Y H:i", strtotime($row['close_time'])) . ")" ?>
            </p>

            <?php if (!$expired): ?>
              <a href="bid.php?id=<?= $row['id']; ?>">
                <button class="mt-3 w-full btn-primary">Place Bid</button>
              </a>
            <?php else: ?>
              <button class="mt-3 w-full bg-gray-300 text-gray-600 py-2 rounded-lg cursor-not-allowed">Closed</button>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="col-span-3 text-center text-red-500">No cattle available for auction.</p>
      <?php endif; ?>
    </div>

    <div class="text-center mt-10">
      <a href="auctions.php" class="btn-secondary">View All Auctions</a>
    </div>
  </section>

  <!-- STATS SECTION -->
  <section class="bg-gradient-to-br from-green-50 to-green-100 py-24">
    <div class="max-w-7xl mx-auto px-6 text-center">
      <h2 class="text-4xl font-extrabold text-gray-800 mb-12">Why Choose Us?</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-10">
        <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition">
          <i class="fas fa-chart-line text-4xl text-orange-500 mb-4"></i>
          <h3 class="text-3xl font-bold">$50M+</h3>
          <p class="text-gray-600">Annual Sales Volume</p>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition">
          <i class="fas fa-users text-4xl text-orange-500 mb-4"></i>
          <h3 class="text-3xl font-bold">5,000+</h3>
          <p class="text-gray-600">Active Users</p>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition">
          <i class="fas fa-clock text-4xl text-orange-500 mb-4"></i>
          <h3 class="text-3xl font-bold">24/7</h3>
          <p class="text-gray-600">Platform Availability</p>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition">
          <i class="fas fa-check-circle text-4xl text-orange-500 mb-4"></i>
          <h3 class="text-3xl font-bold">98%</h3>
          <p class="text-gray-600">Successful Transactions</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ABOUT SECTION -->
  <section id="about" class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
      <div>
        <h2 class="text-3xl font-bold mb-6">About Us</h2>
        <p class="mb-4 text-gray-700">Welcome to Masaku Cattle Auction, your trusted online platform 
          for buying and selling cattle. Our mission is to bring farmers, buyers, 
          and livestock traders together in a transparent and efficient marketplace.</p>
        <h3 class="text-2xl font-semibold mb-3">Our Mission</h3>
        <p class="mb-4 text-gray-700">To empower livestock farmers by giving them direct access to buyers 
          and ensuring fair prices through open auctions.</p>
        <h3 class="text-2xl font-semibold mb-3">Why Choose Us?</h3>
        <ul class="list-disc pl-6 text-gray-700">
          <li>Transparent auction process</li>
          <li>Safe and secure transactions</li>
          <li>Easy registration for farmers & buyers</li>
          <li>Free posting of cattle listings</li>
        </ul>
      </div>
      <div>
        <img src="uploads/1756195892_bull1.webp" alt="About Masaku Cattle Auction" class="rounded-2xl shadow-lg">
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="bg-gray-900 text-white py-12">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-8">
      <div>
        <h3 class="text-lg font-bold mb-3">Masaku Cattle Auction</h3>
        <p>Trusted platform for buying and selling cattle in Kenya.</p>
      </div>
      <div>
        <h3 class="text-lg font-bold mb-3">Quick Links</h3>
        <ul>
          <li><a href="auctions.php" class="hover:text-orange-400">Auctions</a></li>
          <li><a href="sell.php" class="hover:text-orange-400">Sell Cattle</a></li>
          <li><a href="#about" class="hover:text-orange-400">About Us</a></li>
          <li><a href="#" class="hover:text-orange-400">Contact</a></li>
        </ul>
      </div>
      <div>
        <h3 class="text-lg font-bold mb-3">Stay Connected</h3>
        <p class="mb-3">Contact: +254 758022918<br>Email: info@masakuauction.com</p>
        <div class="flex space-x-4">
          <a href="#" class="hover:text-orange-400"><i class="fab fa-facebook"></i></a>
          <a href="#" class="hover:text-orange-400"><i class="fab fa-twitter"></i></a>
          <a href="#" class="hover:text-orange-400"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>
    <div class="text-center text-gray-400 mt-8">
      &copy; 2025 Masaku Cattle Auction. All Rights Reserved.
    </div>
  </footer>

</body>
</html>
