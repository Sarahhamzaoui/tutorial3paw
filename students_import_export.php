<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

$message = "";

/* -------------------- IMPORT STUDENTS FROM CSV -------------------- */
if (isset($_POST["import"])) {
    if ($_FILES["csv_file"]["error"] == 0) {
        $file = fopen($_FILES["csv_file"]["tmp_name"], "r");
        $first = true;

        while (($row = fgetcsv($file)) !== false) {
            if ($first) { $first = false; continue; }  // Skip header

            list($fullname, $email, $password, $group) = $row;

            // Skip if email already exists
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->rowCount() > 0) continue;

            // Get group id
            $grp = $conn->prepare("SELECT id FROM student_groups WHERE group_name = ?");
            $grp->execute([$group]);
            $grp_id = $grp->rowCount() > 0 ? $grp->fetchColumn() : null;

            // Insert student
            $insert = $conn->prepare("INSERT INTO users(fullname, email, password, role, group_id) VALUES (?,?,?,?,?)");
            $insert->execute([
                $fullname,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                "student",
                $grp_id
            ]);
        }

        fclose($file);
        $message = "🎉 Students imported successfully!";
    } else {
        $message = "❌ Please select a valid CSV file.";
    }
}

/* -------------------- EXPORT STUDENTS INTO CSV -------------------- */
if (isset($_POST["export"])) {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=students.csv");

    $output = fopen("php://output", "w");
    fputcsv($output, ["fullname", "email", "group"]);

    $data = $conn->query("
        SELECT users.fullname, users.email, student_groups.group_name
        FROM users
        LEFT JOIN student_groups ON users.group_id = student_groups.id
        WHERE users.role = 'student'
    ");

    while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

/* -------------------- FETCH STUDENTS TABLE -------------------- */
$students = $conn->query("
    SELECT users.*, student_groups.group_name
    FROM users
    LEFT JOIN student_groups ON users.group_id = student_groups.id
    WHERE users.role = 'student'
    ORDER BY users.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Import | Export Students</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #ffe6f2;
    margin: 0;
}
h1 {
    text-align: center;
    margin-top: 25px;
    font-size: 32px;
    color: #d63384;
}
.container {
    width: 90%;
    margin: 30px auto;
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}
button {
    background: #d63384;
    border: none;
    padding: 12px 20px;
    color: white;
    border-radius: 8px;
    cursor: pointer;
}
button:hover { background: #b82d6f; }
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 22px;
}
th, td {
    padding: 10px;
    border-bottom: 1px solid #ffd6eb;
    text-align: center;
}
th {
    background: #d63384;
    color: white;
}
.msg {
    background: #ffe1f1;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 18px;
    color: #b6005c;
    text-align: center;
    font-weight: bold;
}
</style>
</head>
<body>

<h1>📥 Import / 📤 Export Students</h1>

<div class="container">

<?php if ($message) echo "<div class='msg'>$message</div>"; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="csv_file" required>
    <button type="submit" name="import">📥 Import CSV</button>
    <button type="submit" name="export">📤 Export CSV</button>
</form>

<hr>

<h2 style="color:#d63384; text-align:center;">Existing Students</h2>

<table>
<tr>
    <th>#</th>
    <th>Full Name</th>
    <th>Email</th>
    <th>Group</th>
</tr>

<?php foreach ($students as $i => $s): ?>
<tr>
    <td><?= $i + 1 ?></td>
    <td><?= htmlspecialchars($s["id"]) ?></td>
    <td><?= htmlspecialchars($s["fullname"]) ?></td>
    <td><?= htmlspecialchars($s["email"]) ?></td>
    <td><?= $s["group_name"] ? $s["group_name"] : "—" ?></td>
</tr>
<?php endforeach; ?>
</table>

</div>

</body>
</html>
