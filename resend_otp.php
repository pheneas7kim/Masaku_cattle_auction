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
    $email = trim($_POST['email']);

    // Check if user exists and not verified
    $sql  = "SELECT id, name, is_verified FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['is_verified'] == 1) {
            $message = "Your account is already verified. Please <a href='login.php'>Login</a>.";
            $messageClass = "success";
        } else {
            // Generate new OTP
            $otp = rand(100000, 999999);

            // Update OTP in DB
            $update = $conn->prepare("UPDATE users SET verification_code = ? WHERE id = ?");
            $update->bind_param("si", $otp, $user['id']);
            $update->execute();

            // Send OTP email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'yourgmail@gmail.com';
                $mail->Password   = 'your-app-password'; // use Gmail App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('yourgmail@gmail.com', 'Auction System');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Resend Verification Code';
                $mail->Body    = "Hello {$user['name']},<br><br>Your new verification code is <b>$otp</b>.";

                $mail->send();

                $message = "A new OTP has been sent to your email. Please check and verify.";
                $messageClass = "success";
            } catch (Exception $e) {
                $message = "Could not send email. Error: {$mail->ErrorInfo}";
                $messageClass = "error";
            }
        }
    } else {
        $message = "Email not found. Please register first.";
        $messageClass = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Resend Verification</title>
  <link rel="stylesheet" href="css/form.css">
</head>
<body>
  <div class="form-container">
    <h2>Resend Verification Code</h2>

    <?php if (!empty($message)) { ?>
      <p class="<?php echo $messageClass; ?>"><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST" action="resend_otp.php">
      <label for="email">Enter your Email:</label>
      <input type="email" name="email" required>

      <button type="submit" class="btn">Resend Code</button>
    </form>
  </div>
</body>
</html>
