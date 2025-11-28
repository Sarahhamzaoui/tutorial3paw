<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "professor") {
    header("Location: login.php");
    exit;
}

$prof_id = $_SESSION["user_id"];

// Fetch all attendance sessions of this professor
$stmt = $conn->prepare("
    SELECT 
        a.date,
        c.course_name,
        g.group_name,
        a.course_id,
        a.group_id,
        COUNT(*) AS total,
        SUM(a.status = 'Present') AS present,
        SUM(a.status = 'Absent') AS absent
    FROM attendance a
    JOIN courses c ON a.course_id = c.id
    JOIN student_groups g ON a.group_id = g.id
    WHERE a.professor_id = ?
    GROUP BY a.date, a.course_id, a.group_id
    ORDER BY a.date DESC
");
$stmt->execute([$prof_id]);
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Attendance Summary</title>
<style>
body {
    background: #ffe6f3;
    font-family: 'Poppins', sans-serif;
    margin: 0;
}
.container {
    width: 92%;
    margin: 30px auto;
    background: white;
    padding: 28px;
    border-radius: 20px;
    box-shadow: 0 6px 22px rgba(0,0,0,0.15);
}
h2 {
    text-align: center;
    color: #d81b60;
    font-size: 32px;
    margin-bottom: 25px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
th, td {
    border: 1px solid #ffb5d4;
    padding: 12px;
    text-align: center;
    font-size: 15px;
}
th {
    background: #ff6faf;
    color: white;
}
tr:nth-child(even) {
    background: #ffe5f3;
}
.btn-back {
    background: #777;
    display: inline-block;
    padding: 10px 22px;
    text-decoration: none;
    color: white;
    border-radius: 8px;
    margin-top: 24px;
    font-size: 17px;
}
.btn-back:hover { background: #555; }
.view-btn {
    background: #e91e63;
    padding: 8px 16px;
    border-radius: 8px;
    color: white;
    text-decoration: none;
}
.view-btn:hover { background: #c2185b; }
.percent {
    font-weight: bold;
    color: #b3005a;
}
</style>
</head>
<body>

<div class="container">
<h2>📊 Attendance Summary</h2>

<table>
<tr>
    <th>Date</th>
    <th>Course</th>
    <th>Group</th>
    <th>Present</th>
    <th>Absent</th>
    <th>Total</th>
    <th>%</th>
</tr>

<?php foreach ($sessions as $s): 
    $percent = round(($s["present"] / $s["total"]) * 100);
?>
<tr>
    <td><?= $s["date"] ?></td>
    <td><?= $s["course_name"] ?></td>
    <td><?= $s["group_name"] ?></td>
    <td><?= $s["present"] ?></td>
    <td><?= $s["absent"] ?></td>
    <td><?= $s["total"] ?></td>
    <td class="percent"><?= $percent ?>%</td>
</tr>
<?php endforeach; ?>

</table>

<div style="text-align:center;">
    <a href="prof_home.php" class="btn-back">⬅ Back to Dashboard</a>
</div>
</div>

</body>
</html>
