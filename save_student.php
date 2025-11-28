<?php
session_start();
require "db_connect.php";

$fullname = $_POST["fullname"];
$email = $_POST["email"];
$password = $_POST["password"];
$group_id = $_POST["group_id"];

// hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

$sql = $conn->prepare("INSERT INTO users (fullname, email, password, role, group_id) VALUES (?, ?, ?, 'student', ?)");
$sql->execute([$fullname, $email, $hashed, $group_id]);

echo "<script>alert('Student added ✔'); window.location='manage_students.php';</script>";
