<?php

session_start();
include "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if ($password == $user["password"]) {

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            if ($user["role"] == "librarian") {
                header("Location: librarian/dashboard.php");
            } else {
                header("Location: student/dashboard.php");
            }

            exit();

        } else {
            $message = "Wrong password.";
        }

    } else {
        $message = "Username not found.";
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Library System</title>
</head>

<body>

<h1>Library Management System</h1>

<form method="POST">

    <label>Username</label>
    <br>
    <input type="text" name="username" required>

    <br><br>

    <label>Password</label>
    <br>
    <input type="password" name="password" required>

    <br><br>

    <button type="submit">Login</button>

</form>

<p><?php echo $message; ?></p>

</body>

</html>