<?php
require "db_connect.php"; // correct DB connection file

if (!isset($_GET['id'])) die("Invalid request");
$id = $_GET['id'];

// Load student
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) die("Student not found");

// Load groups
$groups = $conn->query("SELECT * FROM student_groups ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Handle update form
if (isset($_POST['save'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $group_id = $_POST['group_id'] ?: null;

    $update = $conn->prepare("UPDATE users SET fullname=?, email=?, group_id=? WHERE id=?");
    $update->execute([$fullname, $email, $group_id, $id]);

    header("Location: manage_students.php?updated=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <style>
        body { font-family: Poppins, sans-serif; background:#ffe6f2; text-align:center; }
        .form-card {
            width: 35%; margin:50px auto; background:white; padding:30px;
            border-radius:15px; box-shadow:0 4px 12px rgba(0,0,0,0.15);
        }
        input, select {
            width:100%; padding:12px; margin-bottom:14px;
            border-radius:8px; border:2px solid #ffb3d9;
        }
        button, a {
            display:inline-block; padding:12px 20px; border-radius:8px;
            color:white; text-decoration:none; margin-top:5px; font-size:16px;
        }
        .btn-save { background:#d63384; }
        .btn-save:hover { background:#b82d6f; }
        .btn-back { background:gray; }
        .btn-back:hover { background:#555; }
    </style>
</head>
<body>

<h1 style="color:#d63384;">✏ Edit Student</h1>

<form method="post" class="form-card">
    <input type="text" name="fullname" value="<?= htmlspecialchars($student['fullname']) ?>" required>
    <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>

    <select name="group_id">
        <option value="">Select Group</option>
        <?php foreach ($groups as $g): ?>
            <option value="<?= $g['id'] ?>" <?= $student['group_id'] == $g['id'] ? "selected" : "" ?>>
                <?= htmlspecialchars($g['group_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="save" class="btn-save">💾 Save</button>
    <a href="manage_students.php" class="btn-back">⬅ Back</a>
</form>

</body>
</html>
