<?php
$password = "Admin@2026";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "<b>" . $hashed_password . "</b>";
?>