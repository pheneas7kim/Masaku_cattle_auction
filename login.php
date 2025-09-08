<?php
session_start();
include 'config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch user by email
    $sql = "SELECT id, name, password, role, is_verified, status FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Successful login
            session_regenerate_id(true); // prevent session fixation

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];  // ✅ store username
            $_SESSION['role']      = $user['role'];

            unset($_SESSION['otp_required']);

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                if ((int)$user['is_verified'] === 1 && $user['status'] === 'approved') {
                    header("Location: user_dashboard.php");
                    exit();
                } elseif ((int)$user['is_verified'] === 0) {
                    $message = "Your account is not verified. Please check your email for the OTP.";
                } elseif ($user['status'] !== 'approved') {
                    $message = "Your account is verified but awaiting admin approval.";
                }
            }
        } else {
            $message = "Invalid password.";
        }
    } else {
        $message = "No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/form.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url("../uploads/bull1.webp") no-repeat center center fixed;
            background-size: cover;
        }
        .form-container {
            width: 350px;
            margin: 100px auto;
            padding: 20px;
            background: rgba(255,255,255,0.9);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container input,
        .form-container button {
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
        }
        .form-container a {
            text-decoration: none;
            color: #007BFF;
            font-size: 14px;
        }
        .message {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }
        #show_password {
            width: auto;
            margin-left: 5px;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="form-container">
    <h2>Login</h2>
    <?php if (!empty($message)) { echo "<p class='message'>$message</p>"; } ?>
    <form method="POST" action="">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" id="login_password" required>
        <label><input type="checkbox" id="show_password" onclick="togglePassword()"> Show Password</label>

        <button type="submit" class="btn">Login</button>
    </form>
    <p><a href="forgot_password.php">Forgot Password?</a></p>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById("login_password");
    passwordField.type = passwordField.type === "password" ? "text" : "password";
}
</script>
</body>
</html>
