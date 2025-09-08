<!-- navbar.php -->
<nav class="navbar">
  <div class="logo">Masaku Auction</div>
  <ul class="nav-links left-links">
    <li><a href="index.php">Home</a></li>

    <li><a href="auctions.php">Auctions</a></li>
    <li><a href="user_dashboard.php">Dashboard</a></li>
    <li><a href="bidding.php">Bidding Process</a></li>
    <li><a href="faq.php">FAQ</a></li>
    <li><a href="">Contact</a></li>
    <li><a href="#about">About Us</a></li>
  </ul>

  <ul class="nav-links right-links">
    <?php if (isset($_SESSION['user_id'])): ?>
      <?php echo htmlspecialchars($_SESSION['user_name']); ?>

      <li><a href="logout.php">Logout</a></li>
    <?php else: ?>
      <li><a href="register.php">Register</a></li>
      <li><a href="login.php">Login</a></li>
    <?php endif; ?>
  </ul>
</nav>
