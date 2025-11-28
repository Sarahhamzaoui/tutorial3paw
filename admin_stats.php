<?php
// admin_stats.php
session_start();
require "db_connect.php";

// security
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php"); exit;
}

// 1) Attendance % per course (computed as present / total_records for that course)
$courseStmt = $conn->query("
    SELECT c.id, c.course_name,
      SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) AS present_count,
      COUNT(ar.id) AS total_count
    FROM courses c
    LEFT JOIN attendance_sessions s ON s.course_id = c.id
    LEFT JOIN attendance_records ar ON ar.session_id = s.id
    GROUP BY c.id, c.course_name
");
$courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

// prepare arrays for chart
$course_labels = [];
$course_percent = [];
foreach ($courses as $c) {
    $course_labels[] = $c['course_name'];
    $total = (int)$c['total_count'];
    $present = (int)$c['present_count'];
    $percent = $total > 0 ? round(($present / $total) * 100, 1) : 0;
    $course_percent[] = $percent;
}

// 2) Top 8 students with most absences (by count)
$absStmt = $conn->query("
    SELECT u.id, u.fullname, u.email, SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) AS absent_count
    FROM users u
    LEFT JOIN attendance_records ar ON ar.student_id = u.id
    WHERE u.role = 'student'
    GROUP BY u.id, u.fullname, u.email
    HAVING absent_count > 0
    ORDER BY absent_count DESC
    LIMIT 8
");
$absent_students = $absStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Admin — Attendance Statistics</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --pink:#d63384; --pink-dark:#b82d6f; --bg:#ffe6f2;
    --card:#fff; --muted:#7a0040;
  }
  body{font-family:'Poppins',sans-serif;background:linear-gradient(180deg,var(--bg),#fff);margin:0;padding:40px;}
  .wrap{max-width:1200px;margin:0 auto;}
  .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;}
  .title{font-size:26px;color:var(--pink);font-weight:700;}
  .sub{color:var(--muted);font-weight:500;}
  .grid{display:grid;grid-template-columns:1fr 420px;gap:22px;}
  .card{background:var(--card);border-radius:14px;padding:18px;box-shadow:0 8px 30px rgba(0,0,0,0.06);}
  .chart-wrap{height:360px;}
  .small-list{display:flex;flex-direction:column;gap:10px;margin-top:10px;}
  .student-row{display:flex;justify-content:space-between;align-items:center;padding:10px;border-radius:10px;background:#fff4fb;border:1px solid #ffd6ea;}
  .student-name{font-weight:600;color:#6b003f;}
  .abs-count{font-weight:700;color:#c02a2a;}
  .legend{display:flex;gap:10px;align-items:center;margin-top:10px;}
  .legend .dot{width:12px;height:12px;border-radius:3px;display:inline-block;}
  .footer-actions{margin-top:14px;display:flex;gap:10px;justify-content:flex-end;}
  .btn{padding:10px 14px;background:var(--pink);color:white;border-radius:10px;border:none;cursor:pointer;font-weight:600;}
  .btn.secondary{background:#ffcce6;color:#7a0040;}
  @media(max-width:900px){ .grid{grid-template-columns:1fr; } }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div>
      <div class="title">📊 Attendance Statistics</div>
      <div class="sub">Overview — attendance % per course & top absent students</div>
    </div>
    <div>
      <a href="admin_home.php" class="btn secondary" style="text-decoration:none;">← Back</a>
    </div>
  </div>

  <div class="grid">
    <!-- Left: Charts -->
    <div class="card">
      <h3 style="margin:0;color:var(--pink);">Attendance Rate per Course</h3>
      <p style="color:var(--muted);margin-top:6px;">Percentage of present records across all sessions</p>
      <div class="chart-wrap">
        <canvas id="courseChart" width="400" height="260"></canvas>
      </div>

      <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:12px;">
        <div style="min-width:220px;">
          <strong style="color:var(--pink-dark)">Average attendance</strong>
          <?php
            $avg = count($course_percent) ? round(array_sum($course_percent)/count($course_percent),1) : 0;
            echo "<div style='font-size:22px;margin-top:6px;font-weight:700;color:#7a0040'>{$avg}%</div>";
          ?>
        </div>
        <div style="min-width:220px;">
          <strong style="color:var(--pink-dark)">Courses tracked</strong>
          <div style="font-size:22px;margin-top:6px;font-weight:700;color:#7a0040"><?= count($course_labels) ?></div>
        </div>
      </div>
    </div>

    <!-- Right: Top absent students -->
    <div class="card">
      <h3 style="margin:0;color:var(--pink);">Top Absent Students</h3>
      <p style="color:var(--muted);margin-top:6px;">Students with most 'absent' records</p>

      <div class="small-list">
        <?php if (count($absent_students)==0): ?>
          <div style="padding:14px;background:#fff4fb;border-radius:10px;color:#7a0040;">No absences recorded yet.</div>
        <?php endif; ?>

        <?php foreach($absent_students as $st): ?>
        <div class="student-row">
          <div>
            <div class="student-name"><?= htmlspecialchars($st['fullname']) ?></div>
            <div style="font-size:12px;color:#9a2a61;"><?= htmlspecialchars($st['email']) ?></div>
          </div>
          <div class="abs-count"><?= (int)$st['absent_count'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="footer-actions">
        <a href="students_import_export.php" style="text-decoration:none;"><button class="btn secondary">Manage Students</button></a>
        <button class="btn" onclick="downloadPNG()">Download Chart</button>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?= json_encode(array_values($course_labels), JSON_UNESCAPED_UNICODE) ?>;
const dataVals = <?= json_encode(array_values($course_percent)) ?>;

const ctx = document.getElementById('courseChart').getContext('2d');
const courseChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [{
      label: 'Attendance %',
      data: dataVals,
      backgroundColor: labels.map((_,i)=> 'rgba(214,51,132,'+ (0.65 - (i*0.02)) +')'),
      borderColor: labels.map((_,i)=> 'rgba(214,51,132,1)'),
      borderWidth: 1
    }]
  },
  options: {
    indexAxis: 'x',
    scales: {
      y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }
    },
    plugins:{ legend:{display:false}, tooltip:{callbacks:{label:function(c){return c.raw + '%';}}} }
  }
);

// download chart as PNG
function downloadPNG(){
  const url = courseChart.toBase64Image();
  const a = document.createElement('a');
  a.href = url;
  a.download = 'attendance_by_course.png';
  a.click();
}
</script>
</body>
</html>
