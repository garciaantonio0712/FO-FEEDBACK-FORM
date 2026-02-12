<?php

// CHANGE THESE TWO VALUES
$new_username = "manager2";          // ← your new username
$plain_password = "MyStrongPass2026"; // ← the password you want to use

// Generate secure hash
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

echo "<h2>New Admin Account Ready to Insert</h2>";
echo "<p><strong>Username:</strong> " . htmlspecialchars($new_username) . "</p>";
echo "<p><strong>Hashed Password:</strong><br>";
echo "<code style='word-break:break-all; font-size:1.1rem;'>" . $hashed_password . "</code></p>";
echo "<p>Copy the hashed password above and use it in phpMyAdmin.</p>";

?>