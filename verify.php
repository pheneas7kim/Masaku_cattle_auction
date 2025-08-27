<?php
// Show all errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php';
session_start();

$message = "";
$messageClass = "";

// Get email from query string
if (!isset($_GET['email'])) {
    header("Location: login.php");
    exit();
}

$email = trim($_GET['email']);

// Handle OTP submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enteredOtp = trim($_POST['otp']);

    // Fetch user by email
    $sql = "SELECT id, name, role, verification_code, is_verified FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ((int)$user['is_verified'] === 1) {
            $message = "Your account is already verified. Please <a href='login.php'>login</a>.";
            $messageClass = "success";
        } elseif ($enteredOtp == $user['verification_code']) {
            // OTP matches → verify account
            $update = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $update->bind_param("i", $user['id']);
            $update->execute();

            // Auto-login after verification
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email']   = $email;
            $_SESSION['role']    = $user['role'];
            $_SESSION['name']    = $user['name'];

            // Redirect to role-based dashboard
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] === 'seller') {
                header("Location: seller_dashboard.php");
            } elseif ($user['role'] === 'buyer') {
                header("Location: buyer_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $message = "Invalid OTP. Please try again.";
            $messageClass = "error";
        }
    } else {
        $message = "No account found with this email.";
        $messageClass = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Account</title>
    <link rel="stylesheet" href="css/form.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
      <?php include 'navbar.php'; ?>
<div class="form-container">
    <h2>Verify Your Account</h2>

    <?php if (!empty($message)) { ?>
        <p class="<?php echo $messageClass; ?>"><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST" action="">
        <label for="otp">Enter OTP:</label>
        <input type="text" name="otp" required pattern="\d{6}" title="Enter the 6-digit OTP">
        <button type="submit" class="btn">Verify</button>
    </form>
</div>
</body>
</html>
