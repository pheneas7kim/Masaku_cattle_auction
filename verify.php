<?php
session_start();
include 'config.php';

$message = "";

// If user comes from register.php, store email in session
if (isset($_GET['email'])) {
    $_SESSION['pending_email'] = $_GET['email'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp   = trim($_POST['otp']);
    $email = $_SESSION['pending_email'] ?? '';

    if ($email && $otp) {
        // Check OTP
        $sql = "SELECT id, verification_code, is_verified FROM users 
                WHERE email = ? AND is_verified = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($otp == $user['verification_code']) {
                //  Mark as verified
                $update = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
                $update->bind_param("i", $user['id']);
                $update->execute();

                // Clear pending email
                unset($_SESSION['pending_email']);

                header("Location: login.php?verified=1");
                exit();
            } else {
                $message = "Invalid OTP.";
            }
        } else {
            $message = " Invalid OTP or already verified.";
        }
    } else {
        $message = " Missing OTP or email session.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Account</title>
</head>
<body>
    <h2>Verify Your Account</h2>
    <?php if (!empty($message)) echo "<p style='color:red;'>$message</p>"; ?>
    <form method="POST">
        <label>Enter OTP sent to your email:</label>
        <input type="text" name="otp" required>
        <button type="submit">Verify</button>
    </form>
</body>
</html>
