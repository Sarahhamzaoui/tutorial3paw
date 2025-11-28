<?php
require "db_connect.php";
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

$message = "";

/* ADD course */
if (isset($_POST["add"])) {
    $course = $_POST["course"];
    $professor = $_POST["professor"];
    $stmt = $conn->prepare("INSERT INTO courses (course_name, professor_name) VALUES (?, ?)");
    $stmt->execute([$course, $professor]);
    $message = "Course added successfully!";
}

/* UPDATE course */
if (isset($_POST["update"])) {
    $id = $_POST["id"];
    $course = $_POST["course"];
    $professor = $_POST["professor"];
    $stmt = $conn->prepare("UPDATE courses SET course_name=?, professor_name=? WHERE id=?");
    $stmt->execute([$course, $professor, $id]);
    $message = "Course updated successfully!";
}

/* DELETE course */
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    $conn->prepare("DELETE FROM courses WHERE id=?")->execute([$id]);
    header("Location: manage_courses.php");
    exit;
}
$courses = $conn->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Courses</title>
<style>
body { font-family: 'Poppins', sans-serif; background: #ffe6f2; padding: 25px; }
.container { display: flex; gap: 40px; }
.card {
    background: white; padding: 25px; border-radius: 16px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.1);
}
h2 { color: #d63384; text-align: center; }
input { width: 100%; padding: 12px; margin-bottom: 12px; border-radius: 8px; border: 2px solid #ffb3d9; }
button {
    width: 100%; padding: 13px; border: none; border-radius: 8px;
    background: #d63384; color: white; font-size: 17px; cursor: pointer;
}
button:hover { background: #b82d6f; }
table { width: 100%; border-collapse: collapse; text-align: center; }
th { background: #d63384; color: white; padding: 10px; }
td { padding: 10px; border-bottom: 1px solid #ffd6eb; }
.action-btn { margin-right: 10px; cursor: pointer; }
.back { margin-top: 20px; display: block; text-align: center; }
</style>
</head>
<body>

<h1 style="text-align:center;color:#d63384;">📘 Manage Courses</h1>

<?php if ($message): ?>
<div style="background:#ffe1f1;color:#b3005c;padding:10px;border-radius:8px;text-align:center;margin-bottom:12px;">
    <?= $message ?>
</div>
<?php endif; ?>

<div class="container">

<!-- Add / Edit Form -->
<div class="card" style="flex:1;">
<form method="post">
    <input type="hidden" name="id" id="courseId">

    <label>Course Name</label>
    <input type="text" name="course" id="courseName" required>

    <label>Professor Name</label>
    <input type="text" name="professor" id="professorName" required>

    <button type="submit" name="add" id="addBtn">➕ Add Course</button>
    <button type="submit" name="update" id="updateBtn" style="display:none;">💾 Save Changes</button>
</form>
</div>

<!-- Course Table -->
<div class="card" style="flex:2;">
<table>
<tr>
    <th>#</th>
    <th>Course</th>
    <th>Professor</th>
    <th>Action</th>
</tr>
<?php $i=1; foreach($courses as $c): ?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= htmlspecialchars($c["course_name"] ?? "") ?></td>
    <td><?= htmlspecialchars($c["professor_name"] ?? "") ?></td>
    <td>
        <button onclick="editCourse(
            '<?= $c['id'] ?>',
            '<?= htmlspecialchars($c['course_name'] ?? "") ?>',
            '<?= htmlspecialchars($c['professor_name'] ?? "") ?>'
        )" class="action-btn">✏ Edit</button>

        <a href="manage_courses.php?delete=<?= $c['id'] ?>"
           onclick="return confirm('Delete this course?')"
           class="action-btn">🗑 Delete</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

</div>
</div>

<a href="admin_home.php" class="back">⬅ Back to Dashboard</a>

<script>
function editCourse(id, course, prof) {
    document.getElementById("courseId").value = id;
    document.getElementById("courseName").value = course;
    document.getElementById("professorName").value = prof;
    document.getElementById("addBtn").style.display = "none";
    document.getElementById("updateBtn").style.display = "block";
}
</script>

</body>
</html>
