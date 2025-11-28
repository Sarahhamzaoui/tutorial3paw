<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "professor") {
    header("Location: login.php");
    exit;
}

$prof_id = $_SESSION["user_id"];
$message = "";

// Fetch courses taught by this professor
$courses = $conn->prepare("SELECT * FROM courses WHERE professor_id = ?");
$courses->execute([$prof_id]);
$courses = $courses->fetchAll(PDO::FETCH_ASSOC);

// Fetch all groups
$groups = $conn->query("SELECT * FROM student_groups ORDER BY group_name")->fetchAll(PDO::FETCH_ASSOC);

// Create session
if (isset($_POST["create"])) {
    $course_id = $_POST["course_id"];
    $group_id = $_POST["group_id"];
    $date = $_POST["session_date"];

    // Prevent duplicate sessions for same course + group + day
    $check = $conn->prepare("
        SELECT id FROM attendance_sessions 
        WHERE course_id=? AND group_id=? AND session_date=?
    ");
    $check->execute([$course_id, $group_id, $date]);

    if ($check->rowCount() > 0) {
        $message = "⚠ Session already exists for this course, group and date.";
    } else {
        $insert = $conn->prepare("
            INSERT INTO attendance_sessions(course_id, group_id, session_date) 
            VALUES (?, ?, ?)
        ");
        $insert->execute([$course_id, $group_id, $date]);

        $message = "🎉 Session created successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Create Session</title>
<style>
body { font-family: 'Poppins', sans-serif; background: #edf3ff; }
.container {
    width: 50%; margin: 45px auto; background: white; padding: 30px;
    border-radius: 18px; box-shadow: 0 6px 18px rgba(0,0,0,0.12);
}
h2 { text-align: center; color: #0052cc; font-size: 28px; }
label { display: block; margin-top: 15px; font-weight: 600; }
select, input {
    width: 100%; padding: 12px; margin-top: 7px;
    border-radius: 8px; border: 1px solid #9eb8e6;
}
button {
    width: 100%; padding: 13px; margin-top: 28px;
    background: #0052cc; border: none; border-radius: 8px;
    color: white; font-size: 18px; cursor: pointer;
}
button:hover { background: #003d99; }
.msg {
    padding: 12px; border-radius: 8px; text-align: center;
    margin-bottom: 15px; font-weight: 600;
}
.success { background: #e7f8e7; color: #066306; }
.warning { background: #ffe6e6; color: #b30000; }
.back-btn {
    display: block; text-decoration: none; margin: 20px auto;
    width: fit-content; background: #777; padding: 10px 18px;
    color: white; border-radius: 7px;
}
.back-btn:hover { background: #555; }
</style>
</head>
<body>

<div class="container">
<h2>📅 Create Attendance Session</h2>

<?php if ($message): ?>
<div class="msg <?= str_contains($message, '⚠') ? 'warning' : 'success' ?>">
    <?= $message ?>
</div>
<?php endif; ?>

<form method="POST">
    <label>Course</label>
    <select name="course_id" required>
        <option value="">— Select course —</option>
        <?php foreach ($courses as $c): ?>
        <option value="<?= $c['id'] ?>"><?= $c['course_name'] ?></option>
        <?php endforeach; ?>
    </select>

    <label>Group</label>
    <select name="group_id" required>
        <option value="">— Select group —</option>
        <?php foreach ($groups as $g): ?>
        <option value="<?= $g['id'] ?>"><?= $g['group_name'] ?></option>
        <?php endforeach; ?>
    </select>

    <label>Date</label>
    <input type="date" name="session_date" required>

    <button type="submit" name="create">➕ Create Session</button>
</form>
</div>

<a href="prof_home.php" class="back-btn">⬅ Back to Dashboard</a>
</body>
</html>
