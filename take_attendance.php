<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'professor') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['pc_id'])) die("Invalid request");
$pc_id = $_GET['pc_id'];

// Load professor-course/group info
$stmt = $conn->prepare("SELECT pc.*, c.course_name, g.group_name
                        FROM professor_courses pc
                        JOIN courses c ON pc.course_id = c.id
                        JOIN student_groups g ON pc.group_id = g.id
                        WHERE pc.id = ?");
$stmt->execute([$pc_id]);
$pc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$pc) die("Record not found");

// Load students of this group
$stmt = $conn->prepare("SELECT id, fullname FROM users WHERE role = 'student' AND group_id = ?");
$stmt->execute([$pc['group_id']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Save attendance
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    foreach ($students as $s) {
        $sid = $s['id'];
        $status = $_POST["status_$sid"] ?? "Absent";

        $insert = $conn->prepare("INSERT INTO attendance (student_id, professor_id, course_id, group_id, status, date)
                                  VALUES (?, ?, ?, ?, ?, CURDATE())");
        $insert->execute([$sid, $pc['professor_id'], $pc['course_id'], $pc['group_id'], $status]);
    }
    header("Location: prof_home.php?saved=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Take Attendance</title>
<style>
body {
    background: #ffe6f3;
    font-family: 'Poppins', sans-serif;
    margin: 0;
}
.container {
    width: 90%;
    margin: 30px auto;
    background: white;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
}
h2 {
    text-align: center;
    font-size: 28px;
    color: #e91e63;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
}
table th, table td {
    border: 1px solid #ffa6c9;
    padding: 10px;
    text-align: center;
}
table th {
    background: #ff80b3;
    color: white;
    font-size: 17px;
}
tr:nth-child(even) {
    background: #ffe5f2;
}

.btn-save {
    background: #e91e63;
    color: white;
    padding: 12px 26px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 18px;
    margin-top: 25px;
}
.btn-save:hover {
    background: #c2185b;
}
.btn-back {
    background: #777;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    margin-left: 12px;
}
.btn-back:hover {
    background: #555;
}
.top-info {
    text-align: center;
    font-size: 20px;
    margin-top: 10px;
    color: #d81b60;
}
</style>
</head>
<body>

<div class="container">
    <h2>📌 Attendance – <?= htmlspecialchars($pc['course_name']) ?> – Group <?= htmlspecialchars($pc['group_name']) ?></h2>
    <div class="top-info">Select Present / Absent for each student</div>

<form method="post">
<table>
<tr>
    <th>#</th>
    <th>Student Name</th>
    <th>Present</th>
    <th>Absent</th>
</tr>

<?php $i = 1; foreach ($students as $s): ?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= htmlspecialchars($s['fullname']) ?></td>
    <td><input type="radio" name="status_<?= $s['id'] ?>" value="Present" required></td>
    <td><input type="radio" name="status_<?= $s['id'] ?>" value="Absent"></td>
</tr>
<?php endforeach; ?>

</table>

<div style="text-align: center;">
    <button class="btn-save" type="submit">💾 Save Attendance</button>
    <a class="btn-back" href="prof_home.php">⬅ Back</a>
</div>

</form>
</div>

</body>
</html>
