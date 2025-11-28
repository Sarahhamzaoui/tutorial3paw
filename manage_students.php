<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

$message = "";

// Add student
if (isset($_POST["add_student"])) {
    $fullname = $_POST["fullname"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $group_id = $_POST["group_id"] ?: null;
    $role = "student";

    $insert = $conn->prepare("INSERT INTO users(fullname, email, password, role, group_id) VALUES (?,?,?,?,?)");
    $insert->execute([$fullname, $email, $password, $role, $group_id]);

    $message = "🎉 Student added successfully!";
}

// Delete student
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    $del = $conn->prepare("DELETE FROM users WHERE id=? AND role='student'");
    $del->execute([$id]);
    header("Location: manage_students.php");
    exit;
}

// Fetch student groups for dropdown
$groups = $conn->query("SELECT * FROM student_groups ORDER BY group_name")->fetchAll();

// Fetch students list
$students = $conn->query("
    SELECT users.id, fullname, email, student_groups.group_name
    FROM users
    LEFT JOIN student_groups ON users.group_id = student_groups.id
    WHERE role='student'
    ORDER BY users.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Students</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #ffe6f2;
    margin: 0;
    padding: 0;
}
h1 {
    text-align: center;
    color: #d63384;
    margin-top: 35px;
}
.container {
    width: 90%;
    margin: auto;
    margin-top: 30px;
    display: flex;
    gap: 40px;
}
.add-box {
    flex: 1;
    background: white;
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.1);
}
.add-box h2 {
    color: #d63384;
    text-align: center;
}
input, select {
    width: 100%;
    padding: 12px;
    margin-bottom: 14px;
    border-radius: 8px;
    border: 2px solid #ffb3d9;
}
button {
    width: 100%;
    padding: 13px;
    background: #d63384;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 17px;
    cursor: pointer;
    margin-top: 5px;
}
button:hover { background: #b82d6f; }
.msg {
    background: #ffe1f1;
    color: #b3005c;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 12px;
    font-weight: 500;
    text-align: center;
}
.table-box {
    flex: 2;
    background: white;
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.1);
}
table {
    width: 100%;
    border-collapse: collapse;
    text-align: center;
    font-size: 16px;
}
th {
    padding: 12px;
    background: #d63384;
    color: white;
}
td {
    padding: 10px;
    border-bottom: 1px solid #ffd6eb;
}
.action-btn {
    padding: 7px 14px;
    border-radius: 7px;
    text-decoration: none;
    color: white;
}
.edit-btn { background: #1e90ff; }
.delete-btn { background: #ff4d6d; }
.edit-btn:hover { background: #0c70d4; }
.delete-btn:hover { background: #cc314f; }
.back {
    display: block;
    width: fit-content;
    margin: 20px auto;
    text-decoration: none;
    background: #999;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
}
.back:hover { background: #555; }
</style>
</head>
<body>

<h1>👨‍🎓 Manage Students</h1>

<div class="container">

<!-- ADD STUDENT -->
<div class="add-box">
    <h2>Add Student</h2>
    <?php if ($message) echo "<div class='msg'>$message</div>"; ?>
    <form method="POST">
        <input type="text" name="fullname" placeholder="Full name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <select name="group_id">
            <option value="">Select Group (optional)</option>
            <?php foreach ($groups as $g): ?>
                <option value="<?= $g['id'] ?>"><?= $g['group_name'] ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="add_student">➕ Add Student</button>
    </form>
</div>

<!-- TABLE -->
<div class="table-box">
    <h2 style="color:#d63384; text-align:center;">Student List</h2>
    <table>
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Group</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
        <?php foreach ($students as $s): ?>
        <tr>
            <td><?= $s["id"]; ?></td>
            <td><?= $s["fullname"]; ?></td>
            <td><?= $s["email"]; ?></td>
            <td><?= $s["group_name"] ?: "—" ?></td>
            <td><a class="action-btn edit-btn" href="student_edit.php?id=<?= $s['id']; ?>">✏ Edit</a></td>
            <td><a class="action-btn delete-btn" href="manage_students.php?delete=<?= $s['id']; ?>" onclick="return confirm('Delete student?');">🗑 Delete</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</div>

<a class="back" href="admin_home.php">⬅ Back to Dashboard</a>

</body>
</html>
