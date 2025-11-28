<?php
session_start();
require "db_connect.php";

$id = $_GET["id"];
$sql = $conn->prepare("DELETE FROM users WHERE id=?");
$sql->execute([$id]);

header("Location: manage_students.php");
exit;
