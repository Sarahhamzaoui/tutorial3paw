<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "professor") {
    header("Location: login.php");
    exit;
}

$course_id = $_GET["course_id"];
$group_id = $_GET["group_id"];

$course = $conn->prepare("SELECT course_name FROM courses WHERE id=?");
$course->execute([$course_id]);
$course = $course->fetchColumn();

$group = $conn->prepare("SELECT group_name FROM student_groups WHERE id=?");
$group->execute([$group_id]);
$group = $group->fetchColumn();

$records = $conn->prepare("
    SELECT u.fullname,
           SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) AS presents,
           SUM(CASE WHEN status='absent' THEN 1 ELSE 0 END) AS absents
    FROM attendance_records ar
    JOIN users u ON ar.student_id = u.id
    WHERE ar.course_id=? AND u.group_id=?
    GROUP BY u.id
");
$records->execute([$course_id, $group_id]);
$summary = $records->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Summary</title>
<style>
body{font-family:Poppins;background:#f6f9ff;margin:0;}
header{background:#0047b3;color:white;padding:18px;font-size:23px;}
table{width:85%;margin:auto;margin-top:35px;border-collapse:collapse;background:white;}
th,td{padding:12px;border-bottom:1px solid #ddd;text-align:center;}
.back{padding:12px 18px;background:#0073ff;color:white;border-radius:7px;text-decoration:none;}
.back:hover{background:#005ecc;}
</style>
</head>
<body>
<header>📊 Attendance Summary – <?= $course ?> | <?= $group ?></header>
<table>
<tr>
<th>Student</th>
<th>Present</th>
<th>Absent</th>
</tr>
<?php foreach($summary as $s): ?>
<tr>
<td><?= $s["fullname"] ?></td>
<td><?= $s["presents"] ?></td>
<td><?= $s["absents"] ?></td>
</tr>
<?php endforeach; ?>
</table>
<br>
<center><a class="back" href="prof_home.php">⬅ Back</a></center>
</body>
</html>
