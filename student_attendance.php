<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION["user_id"];
$course_id = $_GET["course_id"]; // from link
$success = "";
$error = "";

// --------------------- File upload (justification) ---------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {

    $fileName = time() . "_" . basename($_FILES["file"]["name"]);
    $targetPath = "justifications/" . $fileName;

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetPath)) {

        // Insert into justification_requests table (without course_id because your table doesn't have it)
        $stmt = $conn->prepare("
            INSERT INTO justification_requests (student_id, file, date)
            VALUES (?, ?, CURDATE())
        ");
        $stmt->execute([$student_id, $fileName]);

        $success = "📌 Justification submitted successfully!";
    } else {
        $error = "⚠️ Failed to upload justification.";
    }
}

// --------------------- Fetch attendance records ---------------------
$stmt = $conn->prepare("
    SELECT date, status 
    FROM attendance 
    WHERE student_id = ? AND course_id = ?
    ORDER BY date DESC
");
$stmt->execute([$student_id, $course_id]);
$attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
<title>Attendance | Student</title>
<style>
body { background:#ffe6f2; font-family: Arial; }
.container {
    width:70%; margin:40px auto; background:white;
    padding:25px; border-radius:12px;
    box-shadow:0 0 15px rgba(0,0,0,0.1);
}
h2 { text-align:center; color:#d63384; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:12px; text-align:center; border-bottom:1px solid #ddd; }
.status-present { color:green; font-weight:bold; }
.status-absent { color:red; font-weight:bold; }
.success { background:#d4edda; padding:12px; margin-top:10px; border-radius:8px; color:#155724; }
.error { background:#f8d7da; padding:12px; margin-top:10px; border-radius:8px; color:#721c24; }
input[type=file] { margin-top:20px; }
button {
    padding:10px 18px; border:none; background:#d63384;
    color:white; border-radius:6px; cursor:pointer;
}
button:hover { background:#b12d6c; }
</style>
</head>
<body>

<div class="container">
<h2>📌 Attendance Details</h2>

<?php if ($success) echo "<div class='success'>$success</div>"; ?>
<?php if ($error) echo "<div class='error'>$error</div>"; ?>

<table>
<tr><th>Date</th><th>Status</th></tr>

<?php foreach ($attendance as $row): ?>
<tr>
    <td><?= $row["date"] ?></td>
    <td class="<?= ($row["status"] == "Present") ? "status-present" : "status-absent"; ?>">
        <?= $row["status"] ?>
    </td>
</tr>
<?php endforeach; ?>
</table>

<hr><h3>📎 Upload Justification (if absent)</h3>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit">Submit Justification</button>
</form>

</div>
</body>
</html>
