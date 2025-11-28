<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: login.php");
    exit;
}

require "db_connect.php";

$record_id = $_GET["record_id"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Justification Upload</title>
</head>
<body>
<h2>Send Justification</h2>

<form action="upload_justification.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="record_id" value="<?php echo $record_id; ?>">

    <label>Message to professor (optional):</label><br>
    <textarea name="message" rows="4" cols="40"></textarea><br><br>

    <label>Upload a file (PDF / Image):</label>
    <input type="file" name="file" required><br><br>

    <button type="submit">Submit</button>
</form>
</body>
</html>
