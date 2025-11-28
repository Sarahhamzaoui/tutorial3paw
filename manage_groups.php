<?php
session_start();
require "db_connect.php";

/* ---------------- ADD GROUP ---------------- */
if (isset($_POST["add_group"])) {
    $name = trim($_POST["group_name"]);
    if ($name != "") {
        $conn->prepare("INSERT INTO student_groups (group_name) VALUES (?)")->execute([$name]);
        $_SESSION["msg"] = "💖 Group added successfully!";
        header("Location: manage_groups.php");
        exit;
    }
}

/* ---------------- DELETE GROUP ---------------- */
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    $conn->prepare("DELETE FROM student_groups WHERE id=?")->execute([$id]);
    $_SESSION["msg"] = "🗑 Group deleted";
    header("Location: manage_groups.php");
    exit;
}

/* ---------------- UPDATE GROUP ---------------- */
if (isset($_POST["update_group"])) {
    $id = $_POST["id"];
    $name = trim($_POST["group_name"]);
    if ($name != "") {
        $conn->prepare("UPDATE student_groups SET group_name=? WHERE id=?")->execute([$name, $id]);
        $_SESSION["msg"] = "🔄 Group updated successfully";
        header("Location: manage_groups.php");
        exit;
    }
}

/* ---------------- SEARCH + PAGINATION ---------------- */
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

$totalQuery = $conn->prepare("SELECT COUNT(*) FROM student_groups WHERE group_name LIKE ?");
$totalQuery->execute(["%$search%"]);
$totalGroups = $totalQuery->fetchColumn();
$totalPages = ceil($totalGroups / $limit);

$query = $conn->prepare("SELECT * FROM student_groups WHERE group_name LIKE ? ORDER BY id DESC LIMIT $limit OFFSET $offset");
$query->execute(["%$search%"]);
$groups = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Groups</title>
<style>
body { background:#ffe6f2; font-family:Arial; margin:0 }
.container { width:80%; margin:40px auto; background:white; padding:30px; border-radius:12px; box-shadow:0 0 12px rgba(255,0,120,0.2); }
h2 { color:#d63384; text-align:center; margin-top:0; }

/* Alert */
.msg { background:#d4edda; color:#155724; padding:12px; border-radius:8px; text-align:center; margin-bottom:20px; }

/* Add form */
input { width:100%; padding:12px; margin-top:10px; border-radius:8px; border:2px solid #ffb3d9; }
button { width:100%; padding:12px; background:#d63384; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold; margin-top:10px; }
button:hover { background:#b82d6f; }

/* Search */
.search-box { margin-top:20px; display:flex; gap:10px; }
.search-box input { flex:1; }

/* Table */
table { width:100%; border-collapse:collapse; margin-top:25px; }
th, td { padding:13px; border-bottom:1px solid #ffcce6; text-align:center; }
.action-btn { padding:6px 12px; border-radius:6px; text-decoration:none; color:white; font-size:15px; cursor:pointer; }
.edit { background:#007bff; }
.delete { background:#dc3545; }
.edit:hover { background:#0056b3; }
.delete:hover { background:#a71d2a; }

/* Pagination */
.pagination a {
    margin:5px; padding:8px 14px; border-radius:6px; text-decoration:none;
}
.pagination span {
    margin:5px; padding:8px 14px; border-radius:6px;
}

/* Edit popup */
.modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); justify-content:center; align-items:center; }
.modal-content { background:white; padding:25px; border-radius:12px; width:350px; }
.close { float:right; cursor:pointer; font-weight:bold; font-size:20px; }
</style>

<script>
function openEdit(id, name) {
    document.getElementById("edit-id").value = id;
    document.getElementById("edit-name").value = name;
    document.getElementById("editModal").style.display = "flex";
}
function closeEdit() {
    document.getElementById("editModal").style.display = "none";
}
</script>
</head>
<body>

<div class="container">
<h2>🌸 Manage Groups 🌸</h2>

<?php if (isset($_SESSION["msg"])) { echo "<div class='msg'>".$_SESSION["msg"]."</div>"; unset($_SESSION["msg"]); } ?>

<!-- Add Group -->
<form method="POST">
    <input type="text" name="group_name" placeholder="Add new group name" required>
    <button type="submit" name="add_group">➕ Add Group</button>
</form>



<!-- Table -->
<table>
<tr><th>ID</th><th>Group Name</th><th>Actions</th></tr>
<?php if (count($groups) == 0): ?>
<tr><td colspan="3">🚫 No groups found</td></tr>
<?php endif; ?>

<?php foreach ($groups as $g): ?>
<tr>
    <td><?= $g["id"] ?></td>
    <td><?= $g["group_name"] ?></td>
    <td>
        <a class="action-btn edit" onclick="openEdit('<?= $g['id'] ?>','<?= $g['group_name'] ?>')">✏ Edit</a>
        <a class="action-btn delete" href="?delete=<?= $g['id'] ?>" onclick="return confirm('Delete this group?')">🗑 Delete</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<!-- Pagination -->
<div class="pagination" style="text-align:center; margin-top:20px;">
<?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <?php if ($i == $page): ?>
        <span style="background:#d63384; color:white"><?= $i ?></span>
    <?php else: ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" style="background:#ffcce6; color:#7a0040;"><?= $i ?></a>
    <?php endif; ?>
<?php endfor; ?>
</div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEdit()">✖</span>
        <h3>Edit Group</h3>
        <form method="POST">
            <input type="hidden" name="id" id="edit-id">
            <input type="text" name="group_name" id="edit-name" required>
            <button type="submit" name="update_group">💾 Save Changes</button>
        </form>
    </div>
</div>

<script>
window.onclick = function(event) {
    if (event.target == document.getElementById("editModal")) closeEdit();
}
</script>
</body>
</html>
