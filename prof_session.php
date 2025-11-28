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

$students = $conn->prepare("SELECT * FROM users WHERE role='student' AND group_id=? ORDER BY fullname");
$students->execute([$group_id]);
$students = $students->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["submit"])) {
    $date = date("Y-m-d");
    foreach ($students as $s) {
        $status = $_POST["status_" . $s["id"]];
        $stmt = $conn->prepare("INSERT INTO attendance_records(student_id, course_id, session_date, status) VALUES (?,?,?,?)");
        $stmt->execute([$s["id"], $course_id, $date, $status]);
    }
    header("Location: prof_summary.php?course_id=$course_id&group_id=$group_id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Attendance</title>
<style>
body{font-family:Poppins;background:#f6f9ff;margin:0;}
header{background:#0047b3;color:white;padding:18px;font-size:23px;}
table{width:90%;margin:auto;margin-top:35px;border-collapse:collapse;background:white;}
th,td{padding:12px;border-bottom:1px solid #ddd;text-align:center;}
button{padding:13px 20px;background:#0073ff;color:white;border:none;border-radius:7px;cursor:pointer;}
button:hover{background:#005ecc;}
</style>
</head>
<body>
<header>✔ Mark Attendance — <?= $course ?> | <?= $group ?></header>

<form method="post">
<table>
<tr><th>Student</th><th>Status</th></tr>
<?php foreach($students as $s): ?>
<tr>
<td><?= $s["fullname"] ?></td>
<td>
<select name="status_<?= $s["id"] ?>">
    <option value="present">Present</option>
    <option value="absent">Absent</option>
</select>
</td>
</tr>
<?php endforeach; ?>
</table>

<br><center><button type="submit" name="submit">💾 Save Attendance</button></center>
</form>

</body>
</html>
