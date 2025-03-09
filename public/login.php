<?php include_once '../includes/header.php'; ?>
<h2>Login</h2>
<form method="POST" action="../controllers/AuthController.php">
    <input type="email" name="email" required placeholder="Email">
    <input type="password" name="password" required placeholder="Password">
    <button type="submit" name="login">Login</button>
</form>