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
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url("../uploads/bull1.webp") no-repeat center center fixed;
            background-size: cover;
        }

        /* Form Container */
        .form-container {
            width: 400px;
            max-width: 90%;
            padding: 30px 25px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .form-container h2 {
            font-size: 28px;
            margin-bottom: 25px;
            color: #333;
        }

        .form-container input[type="email"],
        .form-container input[type="password"],
        .form-container button {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .form-container input[type="email"]:focus,
        .form-container input[type="password"]:focus {
            border-color: #28a745;
            outline: none;
        }

        .form-container button {
            background: #28a745;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .form-container button:hover {
            background: #218838;
        }

        .form-container a {
            color: #007BFF;
            text-decoration: none;
            font-size: 14px;
        }

        .form-container a:hover {
            text-decoration: underline;
        }

        .message {
            color: red;
            font-weight: bold;
            margin-bottom: 15px;
        }

        #show_password {
            margin-left: 5px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .form-container {
                padding: 25px 15px;
            }

            .form-container h2 {
                font-size: 24px;
            }

            .form-container input[type="email"],
            .form-container input[type="password"],
            .form-container button {
                padding: 12px;
                font-size: 14px;
            }

            .form-container a {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="form-container">
    <h2>Login</h2>
    <?php if (!empty($message)) { echo "<p class='message'>$message</p>"; } ?>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" id="login_password" placeholder="Password" required>
        <label><input type="checkbox" id="show_password" onclick="togglePassword()"> Show Password</label>
        <button type="submit">Login</button>
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

