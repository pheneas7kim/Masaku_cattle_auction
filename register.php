<?php
// Show all errors (for debugging)
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php';
require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $passwordRaw = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role     = $_POST['role']; // get role from form

    // Validate role
    if (!in_array($role, ['buyer','seller'])) {
        $message = "Invalid role selected.";
        $messageClass = "error";
    }
    // Validate Kenyan phone number
    elseif (!preg_match("/^(07\d{8}|01\d{8}|2547\d{8})$/", $phone)) {
        $message = "Invalid Kenyan phone number format.";
        $messageClass = "error";
    }
    // Check password strength
    elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $passwordRaw)) {
        $message = "Password must be at least 8 chars, include upper & lowercase letters, a number, and a special character.";
        $messageClass = "error";
    }
    // Check password match
    elseif ($passwordRaw !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageClass = "error";
    } else {
        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Email is already registered. Please <a href='login.php'>login</a>.";
            $messageClass = "error";
        } else {
            // Generate OTP
            $otp = rand(100000, 999999);

            // Insert new user with OTP
            $sql = "INSERT INTO users (name, email, phone, password, role, verification_code, is_verified) 
                    VALUES (?, ?, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $name, $email, $phone, $password, $role, $otp);

            if ($stmt->execute()) {
                // Send OTP email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'pheneas7kim@gmail.com';
                    $mail->Password   = 'gocn aglq ogab dgke'; // Gmail App Password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('pheneas7kim@gmail.com', 'Auction System');
                    $mail->addAddress($email, $name);

                    $mail->isHTML(true);
                    $mail->Subject = 'Verify your account';
                    $mail->Body    = "Hello $name,<br><br>Welcome to our system!<br>
                                      Your verification code is <b>$otp</b>.<br><br>
                                      Please enter this code to activate your account.";

                    $mail->send();

                    // Redirect to OTP page
                    header("Location: verify.php?email=" . urlencode($email));
                    exit();
                } catch (Exception $e) {
                    $message = "Account created but email could not be sent. Error: {$mail->ErrorInfo}";
                    $messageClass = "error";
                }
            } else {
                $message = "Registration failed. Please try again.";
                $messageClass = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link rel="stylesheet" href="css/form.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include 'navbar.php'; ?>
 
  <div class="form-container">
    <h2>Register</h2>

    <?php if (!empty($message)) { ?>
      <p class="<?php echo $messageClass; ?>"><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST" action="" onsubmit="return validatePasswords()">
          <label for="role">Role:</label>
          <select name="role" required>
            <option value="seller">Seller</option>
            <option value="buyer">Buyer</option>
          </select>

          <label for="name">Full Name:</label>
          <input type="text" name="name" required>

          <label for="email">Email:</label>
          <input type="email" name="email" required>

          <label for="phone">Phone:</label>
          <input type="text" name="phone" 
                 pattern="^(07\d{8}|01\d{8}|2547\d{8})$" 
                 title="Enter a valid Kenyan phone number" 
                 required>

          <label for="password">Password:</label>
          <input type="password" name="password" id="reg_password" required>

          <label for="confirm_password">Confirm Password:</label>
          <input type="password" name="confirm_password" id="reg_confirm_password" required>

          <button type="submit" class="btn">Register</button>
        </form>
      </div>
    </div>

    <!-- Right Side: Image -->
    <div class="image-section">
      <img src="../uploads/bull1.webp" alt="Auction Image">
    </div>
  </div>

<script>
function validatePasswords() {
  const pass = document.getElementById("reg_password").value.trim();
  const confirm = document.getElementById("reg_confirm_password").value.trim();

  const strongPass = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
  if (!strongPass.test(pass)) {
    alert("Password must be at least 8 chars, include uppercase, lowercase, number, and special char.");
    return false;
  }
  if (pass !== confirm) {
    alert("Passwords do not match!");
    return false;
  }
  return true;
}
</script>
</body>