<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FAQ - Auction System</title>
  <style>
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      background: #f4f6f9;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 900px;
      margin: auto;
      padding: 40px 20px;
    }
    h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 30px;
    }

    /* FAQ Styles */
    .faq-item {
      background: #fff;
      border-radius: 12px;
      margin-bottom: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      overflow: hidden;
      transition: all 0.3s ease;
    }
    .faq-question {
      background: #27ae60;
      color: #fff;
      padding: 15px 20px;
      cursor: pointer;
      font-weight: bold;
      position: relative;
    }
    .faq-question::after {
      content: "+";
      position: absolute;
      right: 20px;
      font-size: 20px;
      transition: transform 0.3s;
    }
    .faq-item.active .faq-question::after {
      content: "−";
    }
    .faq-answer {
      display: none;
      padding: 15px 20px;
      color: #444;
      background: #fafafa;
      border-top: 1px solid #eee;
    }
    .faq-item.active .faq-answer {
      display: block;
    }

    /* Back button */
    .back {
      display: inline-block;
      margin-top: 30px;
      padding: 10px 18px;
      background: #27ae60;
      color: #fff;
      border-radius: 8px;
      text-decoration: none;
      transition: background 0.3s;
    }
    .back:hover {
      background: #219150;
    }
  </style>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include 'navbar.php'; ?>
  <div class="container">
    <h2>❓ Frequently Asked Questions</h2>

    <div class="faq-item">
      <div class="faq-question">How do I register an account?</div>
      <div class="faq-answer">
        Click on the <b>Register</b> button on the homepage, fill in your details (name, email, phone, password), and confirm. 
        You can then log in to start bidding.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">How do I place a bid?</div>
      <div class="faq-answer">
        Go to the <b>Auctions</b> page, select a cattle you’re interested in, enter your bid amount, and click submit. 
        Make sure your bid is higher than the current highest bid.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">How will I know if I’ve been outbid?</div>
      <div class="faq-answer">
        On your <b>My Account</b> page, you’ll see all your bids. If another user places a higher bid, 
        the system will mark your bid as <span style="color:red;font-weight:bold;">Outbid</span>.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">Can I delete my bid?</div>
      <div class="faq-answer">
        Yes, on your <b>My Account</b> page under "My Bids", you can remove your bid by clicking the 🗑️ button.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">What happens if I win an auction?</div>
      <div class="faq-answer">
        If you’re the highest bidder when the auction ends, the system will notify you. 
        The seller will then contact you for payment and delivery arrangements.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">Is my personal information safe?</div>
      <div class="faq-answer">
        Yes. All user information is securely stored, and we never share it with third parties.
      </div>
    </div>

    <div style="text-align:center;">
      <a href="index.php" class="back">⬅ Back to Home</a>
    </div>
  </div>

  <script>
    document.querySelectorAll(".faq-question").forEach(item => {
      item.addEventListener("click", () => {
        let parent = item.parentElement;
        parent.classList.toggle("active");
      });
    });
  </script>
</body>
</html>
