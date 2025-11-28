<?php
session_start();
require "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit;
}

$email = $_POST["email"];
$password = $_POST["password"];

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user["password"])) {
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["fullname"] = $user["fullname"];
    $_SESSION["role"] = $user["role"];

    if ($user["role"] == "admin") header("Location: admin_home.php");
    elseif ($user["role"] == "professor") header("Location: prof_home.php");
    else header("Location: student_home.php");
    exit;
}

$_SESSION["error"] = "❌ Incorrect email or password";
header("Location: login.php");
exit;
?>
