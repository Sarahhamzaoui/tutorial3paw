<?php
session_start();
require "db_connect.php"; // must contain $conn PDO connection

// If not professor → redirect
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "professor") {
    header("Location: login.php");
    exit;
}

$prof_id = $_SESSION["user_id"];

// Fetch professor assigned courses & groups
$stmt = $conn->prepare("
    SELECT 
        pc.id AS pc_id,
        pc.course_id,
        pc.group_id,
        c.course_name,
        g.group_name
    FROM professor_courses pc
    JOIN courses c ON pc.course_id = c.id
    JOIN student_groups g ON pc.group_id = g.id
    WHERE pc.professor_id = ?
    ORDER BY c.course_name, g.group_name
");
$stmt->execute([$prof_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Professor Dashboard</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #ffe6f2;
    margin: 0;
}

header {
    background: #ff389c;
    padding: 18px 25px;
    color: white;
    font-size: 26px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
header a {
    color: white;
    text-decoration: none;
    font-size: 22px;
}

.logo {
    display: flex;
    justify-content: center;
    margin: 25px 0;
}
.logo img {
    width: 150px;
}

.container {
    width: 90%;
    margin: 20px auto 45px;
    background: white;
    padding: 28px;
    border-radius: 25px;
    box-shadow: 0px 6px 24px rgba(0,0,0,0.12);
}

h2 {
    text-align: center;
    color: #d10b72;
    font-size: 30px;
    margin-bottom: 25px;
}

.course-title {
    font-size: 22px;
    font-weight: 600;
    margin-top: 25px;
    color: #ff0077;
}

.btn {
    display: inline-block;
    padding: 9px 18px;
    margin: 7px 7px;
    background: #ff389c;
    color: white;
    border-radius: 9px;
    text-decoration: none;
    font-size: 15px;
}
.btn:hover {
    background: #cc006f;
}

.session-box {
    background: #fff0fa;
    display: inline-block;
    padding: 4px 10px;
    border-radius: 8px;
    margin-left: 6px;
    font-size: 14px;
    color: #9b0052;
    border: 1px solid #ffb8df;
}

.message {
    text-align: center;
    font-size: 20px;
    color: #c10048;
    margin-top: 20px;
}
</style>
</head>
<body>

<header>
    🎓 Professor <?= htmlspecialchars($_SESSION["fullname"]) ?>

</div>

    <a href="logout.php">Logout</a>

</div>
<div style="text-align:center; margin-top:15px;">
    <a href="attendance_summary.php" class="btn">📊 View Attendance Summary</a>
</div>


</header>

<div class="logo">
    <img src="R.png" alt="University of Algiers Logo">
</div>

<div class="container">
    <h2>📘 Your Courses & Groups</h2>

    <?php
    if (count($assignments) == 0) {
        echo "<p class='message'>❌ You are not assigned to any course yet.</p>";
    } else {
        $currentCourse = "";
        foreach ($assignments as $a) {
            
            // Count number of sessions taken
            $s = $conn->prepare("
                SELECT COUNT(DISTINCT date)
                FROM attendance
                WHERE professor_id = ? AND course_id = ? AND group_id = ?
            ");
            $s->execute([$prof_id, $a["course_id"], $a["group_id"]]);
            $sessions_count = $s->fetchColumn();

            if ($currentCourse != $a["course_name"]) {
                if ($currentCourse != "") echo "<br>";
                $currentCourse = $a["course_name"];
                echo "<div class='course-title'>📌 " . htmlspecialchars($currentCourse) . "</div>";
            }

            echo "
                <a class='btn' href='take_attendance.php?pc_id={$a["pc_id"]}'>
                    Group {$a["group_name"]}
                </a>
                <span class='session-box'>{$sessions_count} sessions</span>
            ";
        }
    }
    ?>
</div>

</body>
</html>
