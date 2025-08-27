<?php
include 'config.php';

$newPassword = password_hash("Masaku254#", PASSWORD_BCRYPT);

$stmt = $conn->prepare("UPDATE users SET password=? WHERE email='admin@masakuauction.com'");
$stmt->bind_param("s", $newPassword);
if($stmt->execute()){
    echo "✅ Admin password updated!";
} else {
    echo "❌ Error: ".$stmt->error;
}
?>
