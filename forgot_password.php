<?php
// Show errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Load PHPMailer

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // Check if email exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Generate new OTP
        $otp = rand(100000, 999999);

        // Save OTP in DB
        $updateSql = "UPDATE users SET otp = ?, is_verified = 0 WHERE email = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("is", $otp, $email);

        if ($updateStmt->execute()) {
            // Send OTP via Gmail SMTP
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'pheneas7kim@gmail.com';   // 🔹 your Gmail
                $mail->Password   = 'gocn aglq ogab dgke';     // 🔹 app password (not Gmail login password)
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('yourgmail@gmail.com', 'Your App Name');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "Your Password Reset OTP";
                $mail->Body    = "<p>Your OTP for password reset is: <b>$otp</b></p>";

                $mail->send();
                
                header("Location: reset_password.php?email=" . urlencode($email));
                exit();

              
            } catch (Exception $e) {
                $message = "⚠️ Failed to send email. Mailer Error: " . $mail->ErrorInfo;
                $messageClass = "error";
            }
        } else {
            $message = "Failed to update OTP. Please try again.";
            $messageClass = "error";
        }
    } else {
        $message = "No account found with that email.";
        $messageClass = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="css/form.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
  <div class="form-container">
    <h2>Forgot Password</h2>

    <?php if (!empty($message)) { ?>
      <p class="<?php echo $messageClass; ?>"><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST" action="">
      <label for="email">Enter your email:</label>
      <input type="email" name="email" required>

      <button type="submit" class="btn">Resend OTP</button>
    </form>

    <p><a href="login.php">Back to Login</a></p>
  </div>
</body>
</html>
