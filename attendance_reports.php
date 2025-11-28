<?php
session_start();
require "db_connect.php";

$sessions = $conn->query("
    SELECT attendance_sessions.id, attendance_sessions.session_date,
           courses.course_name AS course, student_groups.group_name AS grp
    FROM attendance_sessions
    JOIN courses ON attendance_sessions.course_id = courses.id
    JOIN student_groups ON attendance_sessions.group_id = student_groups.id
    ORDER BY attendance_sessions.session_date DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
<title>Attendance Reports</title>
<style>
body { background:#ffe6f2; font-family:Arial; margin:0; padding:0; }
.container { width:70%; margin:30px auto; background:white; padding:25px;
             border-radius:12px; box-shadow:0 0 15px rgba(255,0,120,0.2); }
h2 { color:#d63384; text-align:center; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:13px; border-bottom:1px solid #ffcce6; text-align:center; }
a { color:#d63384; text-decoration:none; font-weight:bold; }
a:hover { text-decoration:underline; }
</style>
</head>
<body>
<div class="container">

<h2>📊 Attendance Reports</h2>

<table>
<tr>
  <th>Date</th>
  <th>Course</th>
  <th>Group</th>
  <th>Action</th>
</tr>

<?php foreach ($sessions as $s) { ?>
<tr>
  <td><?= $s["session_date"]; ?></td>
  <td><?= $s["course"]; ?></td>
  <td><?= $s["grp"]; ?></td>
  <td><a href="view_session.php?id=<?= $s['id'] ?>">🔍 View</a></td>
</tr>
<?php } ?>

</table>
</div>
</body>
</html>
