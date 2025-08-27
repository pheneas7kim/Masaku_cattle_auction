<?php
// Show errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php';

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "Passwords do not match!";
        $messageClass = "error";
    } else {
        // Check OTP
        $sql = "SELECT id FROM users WHERE email = ? AND otp = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Hash new password
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password and clear OTP
            $updateSql = "UPDATE users SET password = ?, otp = NULL, is_verified = 1 WHERE email = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ss", $hashedPassword, $email);

            if ($updateStmt->execute()) {
                $message = "Password reset successful! You can now log in.";
                $messageClass = "success";
            } else {
                $message = "Failed to reset password. Please try again.";
                $messageClass = "error";
            }
        } else {
            $message = "Invalid OTP or email.";
            $messageClass = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <link rel="stylesheet" href="css/form.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
  <div class="form-container">
    <h2>Reset Password</h2>

    <?php if (!empty($message)) { ?>
      <p class="<?php echo $messageClass; ?>"><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST" action="">
      <label for="email">Email:</label>
      <input type="email" name="email" required>

      <label for="otp">Enter OTP:</label>
      <input type="text" name="otp" required>

      <label for="new_password">New Password:</label>
      <input type="password" name="new_password" required>

      <label for="confirm_password">Confirm Password:</label>
      <input type="password" name="confirm_password" required>

      <button type="submit" class="btn">Reset Password</button>
    </form>

    <p><a href="login.php">Back to Login</a></p>
  </div>
</body>
</html>
