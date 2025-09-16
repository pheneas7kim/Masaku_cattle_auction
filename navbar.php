<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar">
  <div class="logo">Livestock Auction</div>

  <div class="menu-toggle" id="menu-toggle">☰</div>

  <ul class="nav-links" id="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="auctions.php">Auctions</a></li>

    <?php if (isset($_SESSION['user_id'])): ?>
      <li><a href="user_dashboard.php">Dashboard</a></li>
    <?php endif; ?>

    <li><a href="bidding.php">Bidding Process</a></li>
    
    
    <li><a href="#about">About Us</a></li>

    <?php if (isset($_SESSION['user_id'])): ?>
      <li><span class="welcome">Welcome <?php echo htmlspecialchars($_SESSION['user_name']); ?></span></li>
      <li><a href="logout.php">Logout</a></li>
    <?php else: ?>
      <li><a href="register.php">Register</a></li>
      <li><a href="login.php">Login</a></li>
    <?php endif; ?>
  </ul>
</nav>

<script>
const toggle = document.getElementById('menu-toggle');
const navLinks = document.getElementById('nav-links');

toggle.addEventListener('click', function() {
    navLinks.classList.toggle('active');
    document.body.classList.toggle('menu-open');
});
</script>

