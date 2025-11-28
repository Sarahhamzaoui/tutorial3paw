<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #ffe6f2;
    margin: 0;
    padding: 0;
}
.container {
    width: 40%;
    margin: 80px auto;
    background: white;
    padding: 35px;
    border-radius: 15px;
    box-shadow: 0 0 18px rgba(255, 0, 120, 0.25);
}
h2 {
    text-align: center;
    color: #d63384;
    font-size: 28px;
    margin-bottom: 25px;
}
input[type=email],
input[type=password] {
    width: 100%;
    padding: 13px;
    margin-bottom: 18px;
    border-radius: 10px;
    border: 2px solid #ffb3d9;
    font-size: 16px;
}
button {
    width: 100%;
    background: #d63384;
    border: none;
    padding: 13px;
    border-radius: 10px;
    font-size: 18px;
    color: white;
    cursor: pointer;
    transition: 0.2s;
}
button:hover {
    background: #b82d6f;
}

/* Error message */
.error {
    background: #ffd6e8;
    color: #b3003d;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 18px;
    font-weight: 500;
    font-size: 16px;
}
</style>
</head>
<body>

<div class="container">
<h2>Attendance System Login</h2>

<?php 
if (isset($_GET["error"])) {
    echo "<div class='error'>❌ Incorrect email or password</div>";
}
?>

<form action="auth.php" method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login 🔐</button>
</form>
</div>

</body>
</html>
