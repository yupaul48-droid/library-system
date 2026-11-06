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

            $message = "Incorrect password.";

        }

    } else {

        $message = "Username not found.";

    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Library Management System</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="login-page">

    <div class="login-container">

        <div class="logo">

            <div class="logo-icon">📚</div>

            <h1>School Library</h1>

            <p>Library Management System</p>

        </div>


        <form method="POST">

            <div class="form-group">

                <label>Username</label>

                <input
                    type="text"
                    name="username"
                    placeholder="Enter your username"
                    required
                >

            </div>


            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>


            <button
                type="submit"
                class="login-button"
            >
                Login
            </button>

        </form>


        <?php if ($message != ""): ?>

            <p class="login-error">
                <?php echo htmlspecialchars($message); ?>
            </p>

        <?php endif; ?>


    </div>

</div>

</body>

</html>