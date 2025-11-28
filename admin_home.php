<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

// STATISTICS
$total_students = $conn->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$total_professors = $conn->query("SELECT COUNT(*) FROM users WHERE role='professor'")->fetchColumn();
$total_courses = $conn->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$total_groups = $conn->query("SELECT COUNT(*) FROM student_groups")->fetchColumn();
$total_sessions = $conn->query("SELECT COUNT(*) FROM attendance_sessions")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #ffe6f2;
    margin: 0;
}

/* HEADER */
.header {
    text-align: center;
    padding: 25px;
}
.header img {
    width: 110px;
    margin-bottom: 10px;
}
.header h1 {
    font-size: 38px;
    color: #b3005c;
    margin: 0;
}

/* STAT CARDS */
.stats {
    display: flex;
    justify-content: center;
    gap: 25px;
    margin: 30px auto;
    flex-wrap: wrap;
    width: 85%;
}
.card {
    width: 200px;
    height: 110px;
    background: white;
    border-radius: 18px;
    text-align: center;
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    padding-top: 20px;
    font-size: 20px;
    font-weight: bold;
    color: #d63384;
}
.card span {
    font-size: 35px;
    color: #000;
}

/* MENU BUTTONS */
.menu {
    width: 75%;
    margin: 40px auto;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
}
.menu a {
    background: #d63384;
    color: white;
    padding: 18px;
    text-align: center;
    border-radius: 12px;
    font-size: 18px;
    text-decoration: none;
    font-weight: 500;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
}
.menu a:hover {
    background: #b82d6f;
}

/* LOGOUT */
.logout {
    display: block;
    width: fit-content;
    margin: 30px auto;
    background: #444;
    padding: 10px 20px;
    color: white;
    text-decoration: none;
    border-radius: 8px;
}
.logout:hover {
    background: #222;
}
</style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <img src="R.png" alt="University Logo">
    <h1>Welcome, Admin 👋</h1>
    <p style="color:#555; font-size:18px; margin-top:5px;">
        University of Algiers — Attendance Management System
    </p>
</div>

<!-- STATS -->
<div class="stats">
    <div class="card">Students<br><span><?= $total_students ?></span></div>
    <div class="card">Professors<br><span><?= $total_professors ?></span></div>
    <div class="card">Courses<br><span><?= $total_courses ?></span></div>
    <div class="card">Groups<br><span><?= $total_groups ?></span></div>
    <div class="card">Sessions<br><span><?= $total_sessions ?></span></div>
</div>

<!-- MENU -->
<!-- MENU -->
<div class="menu">
    <a href="manage_students.php">📚 Manage Students</a>
    <a href="students_import_export.php">📥 Import / Export Students</a>
    <a href="manage_groups.php">👥 Manage Groups</a>
    <a href="manage_courses.php">📘 Manage Courses</a>
</div>


<!-- Logout -->
<a href="logout.php" class="logout">🚪 Logout</a>

</body>
</html>
