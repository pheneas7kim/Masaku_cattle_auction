<?php
// Show all errors (debug mode)
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php';

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch user by email
    $sql = "SELECT id, name, password, role, is_verified FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Step 1: Check password
        if (!password_verify($password, $user['password'])) {
            $message = "Invalid password.";
            $messageClass = "error";
        }
        // Step 2: Check verification status
        elseif ((int)$user['is_verified'] === 0) {
            $message = "Your account is not verified. Please check your email for the OTP.";
            $messageClass = "error";
        }
        // Step 3: Role-based login
        else {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['name']    = $user['name']; // store name for dashboards

            // Redirect according to role
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] === 'seller') {
                header("Location: seller_dashboard.php");
            } elseif ($user['role'] === 'buyer') {
                header("Location: buyer_dashboard.php");
            } else {
                header("Location: dashboard.php"); // fallback
            }
            exit();
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
    <title>Login</title>
    <link rel="stylesheet" href="css/form.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            /* Background image for login only */
            background: url("../uploads/bull1.webp") no-repeat center center fixed;
            background-size: cover;
        }

        .form-container {
            width: 350px;
            margin: 100px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9); /* translucent background */
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-container input,
        .form-container button {
            width: 100%;
            margin-bottom: 15px;
        }

        .form-container a {
            text-decoration: none;
            color: #007BFF;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="form-container">
        <h2>Login</h2>

        <?php if (!empty($message)) { ?>
            <p class="<?php echo $messageClass; ?>"><?php echo $message; ?></p>
        <?php } ?>

        <form method="POST" action="">
            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" class="btn">Login</button>
        </form>

        <p><a href="forgot_password.php">Forgot Password?</a></p>
    </div>
</body>
</html>

        