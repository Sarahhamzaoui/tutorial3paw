<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT c.course_name, g.group_name, pc.course_id
    FROM users u
    JOIN student_groups g ON u.group_id = g.id
    JOIN professor_courses pc ON g.id = pc.group_id
    JOIN courses c ON pc.course_id = c.id
    WHERE u.id = ?
");
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Student Dashboard</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #ffd7ec, #ffeef8);
    margin: 0;
    padding: 0;
}

header {
    background: #ff4da6;
    color: white;
    padding: 20px 35px;
    font-size: 26px;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header a {
    color: white;
    text-decoration: none;
    font-size: 20px;
    background: rgba(255, 255, 255, 0.25);
    padding: 8px 18px;
    border-radius: 8px;
}

#logo-container {
    text-align: center;
    margin-top: 18px;
}

#logo-container img {
    width: 130px;
}

.container {
    width: 85%;
    background: white;
    margin: 35px auto;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(255, 0, 140, 0.15);
}

h2 {
    text-align: center;
    font-size: 28px;
    color: #e60073;
    margin-bottom: 30px;
}

.course-box {
    background: #fff1fb;
    border: 2px solid #ffc1e4;
    padding: 18px;
    border-radius: 18px;
    margin: 15px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: .3s;
}

.course-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(255, 0, 120, 0.15);
}

.btn {
    background: #ff2d8b;
    color: white;
    padding: 10px 18px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 15px;
    font-weight: 500;
    transition: .3s;
}

.btn:hover {
    background: #cc0066;
}
</style>
</head>
<body>

<header>
    🎓 Welcome <?= htmlspecialchars($_SESSION["fullname"]) ?>
    <a href="logout.php">Logout</a>
</header>

<div id="logo-container">
    <img src="R.png" alt="University of Algiers Logo">
</div>

<div class="container">
    <h2>📘 Your Courses</h2>

    <?php if (!$courses): ?>
        <p style="text-align:center; color:#ff0066; font-size:18px;">
            ❌ You are not enrolled in any course yet.
        </p>
    <?php endif; ?>

    <?php foreach ($courses as $c): ?>
        <div class="course-box">
            <strong><?= htmlspecialchars($c["course_name"]) ?> — Group <?= htmlspecialchars($c["group_name"]) ?></strong>
            <a class="btn" href="student_attendance.php?course_id=<?= $c["course_id"] ?>">
                View Attendance 🔍
            </a>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
