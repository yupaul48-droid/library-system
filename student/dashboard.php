<?php

session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "student") {
    header("Location: ../login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Dashboard</title>
</head>

<body>

<h1>Student Dashboard</h1>

<p>
Welcome, <?php echo $_SESSION["username"]; ?>!
</p>

<hr>

<h3>Student Menu</h3>

<a href="#">View Books</a>
<br><br>

<a href="#">My Borrowed Books</a>
<br><br>

<a href="../logout.php">Logout</a>

</body>

</html>