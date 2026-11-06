<?php

session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "librarian") {
    header("Location: ../login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Librarian Dashboard</title>
</head>

<body>

<h1>Librarian Dashboard</h1>

<p>
Welcome, <?php echo $_SESSION["username"]; ?>!
</p>

<hr>

<h3>Librarian Menu</h3>

<a href="#">Manage Books</a>
<br><br>

<a href="#">Borrowed Books</a>
<br><br>

<a href="../logout.php">Logout</a>

</body>

</html>