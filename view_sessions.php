<?php
session_start();
require "db_connect.php";

$session_id = $_GET["id"];

$session = $conn->prepare("
    SELECT attendance_sessions.session_date,
           courses.course_name AS course, student_groups.group_name AS grp
    FROM attendance_sessions
    JOIN courses ON attendance_sessions.course_id = courses.id
    JOIN student_groups ON attendance_sessions.group_id = student_groups.id
    WHERE attendance_sessions.id = ?
");
$session->execute([$session_id]);
$session_info = $session->fetch();

$records = $conn->prepare("
    SELECT users.fullname, attendance_records.status
    FROM attendance_records
    JOIN users ON attendance_records.student_id = users.id
    WHERE attendance_records.session_id = ?
");
$records->execute([$session_id]);
$data = $records->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
<title>Session Details</title>
<style>
body { background:#ffe6f2; font-family:Arial; margin:0; padding:0; }
.container { width:70%; margin:30px auto; background:white; padding:25px;
             border-radius:12px; box-shadow:0 0 15px rgba(255,0,120,0.2); }
h2 { color:#d63384; text-align:center; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:13px; border-bottom:1px solid #ffcce6; text-align:center; }
</style>
</head>
<body>
<div class="container">

<h2>📌 Attendance | <?= $session_info["course"] ?> — <?= $session_info["grp"] ?> (<?= $session_info["session_date"] ?>)</h2>

<table>
<tr>
 <th>Student</th>
 <th>Status</th>
</tr>

<?php foreach ($data as $r) { ?>
<tr>
 <td><?= $r["fullname"] ?></td>
 <td><?= ucfirst($r["status"]) ?></td>
</tr>
<?php } ?>

</table>
</div>
</body>
</html>
