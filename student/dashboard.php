<?php

session_start();
include "../config.php";


// ==========================================
// CHECK STUDENT ACCESS
// ==========================================

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "student") {

    header("Location: ../login.php");
    exit();

}


$user_id = $_SESSION["user_id"];


// ==========================================
// AVAILABLE BOOKS
// ==========================================

$result = $conn->query(
    "SELECT COALESCE(SUM(quantity), 0) AS total
     FROM books"
);

$available_books = $result->fetch_assoc()["total"];


// ==========================================
// MY BORROWED BOOKS
// ==========================================

$sql = "SELECT COUNT(*) AS total
        FROM borrowings
        WHERE user_id = ?
        AND status = 'borrowed'";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$my_borrowed_books = $result->fetch_assoc()["total"];

$stmt->close();


// ==========================================
// MY RETURNED BOOKS
// ==========================================

$sql = "SELECT COUNT(*) AS total
        FROM borrowings
        WHERE user_id = ?
        AND status = 'returned'";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$returned_books = $result->fetch_assoc()["total"];

$stmt->close();


// ==========================================
// TOTAL BORROWING HISTORY
// ==========================================

$sql = "SELECT COUNT(*) AS total
        FROM borrowings
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$total_borrowings = $result->fetch_assoc()["total"];

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard - Library System</title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>


<body>


<div class="dashboard">


    <!-- ====================================== -->
    <!-- SIDEBAR -->
    <!-- ====================================== -->

    <aside class="sidebar">


        <div class="sidebar-logo">

            <div class="logo-icon">
                📚
            </div>

            <h2>Library System</h2>

            <p>Student Panel</p>

        </div>



        <ul class="sidebar-menu">


            <li>

                <a href="dashboard.php">

                    🏠 Dashboard

                </a>

            </li>


            <li>

                <a href="books.php">

                    📚 Browse Books

                </a>

            </li>


            <li>

                <a href="borrowed.php">

                    📖 My Borrowed Books

                </a>

            </li>


            <li>

                <a href="../logout.php"
                   class="logout">

                    🚪 Logout

                </a>

            </li>


        </ul>


    </aside>



    <!-- ====================================== -->
    <!-- MAIN CONTENT -->
    <!-- ====================================== -->

    <main class="main-content">


        <!-- TOP BAR -->

        <div class="topbar">


            <div>

                <h1>Student Dashboard</h1>

                <p>
                    Welcome to the Library Management System.
                </p>

            </div>



            <div class="user-info">

                👤

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $_SESSION["username"]
                    );

                    ?>

                </strong>

            </div>


        </div>



        <!-- ====================================== -->
        <!-- DASHBOARD CARDS -->
        <!-- ====================================== -->

        <div class="cards">


            <!-- AVAILABLE BOOKS -->

            <div class="card">

                <div class="card-icon">
                    📚
                </div>

                <div>

                    <h3>Available Books</h3>

                    <p>
                        <?php
                        echo $available_books;
                        ?>
                    </p>

                </div>

            </div>



            <!-- CURRENTLY BORROWED -->

            <div class="card">

                <div class="card-icon">
                    📖
                </div>

                <div>

                    <h3>My Borrowed Books</h3>

                    <p>
                        <?php
                        echo $my_borrowed_books;
                        ?>
                    </p>

                </div>

            </div>



            <!-- RETURNED -->

            <div class="card">

                <div class="card-icon">
                    ✅
                </div>

                <div>

                    <h3>Returned Books</h3>

                    <p>
                        <?php
                        echo $returned_books;
                        ?>
                    </p>

                </div>

            </div>



            <!-- TOTAL HISTORY -->

            <div class="card">

                <div class="card-icon">
                    📋
                </div>

                <div>

                    <h3>Borrowing History</h3>

                    <p>
                        <?php
                        echo $total_borrowings;
                        ?>
                    </p>

                </div>

            </div>


        </div>



        <!-- ====================================== -->
        <!-- QUICK ACTIONS -->
        <!-- ====================================== -->

        <div class="content-box">


            <h2>
                Quick Actions
            </h2>


            <br>


            <a href="books.php"
               class="btn btn-primary">

                📚 Browse Books

            </a>


            <a href="borrowed.php"
               class="btn btn-success">

                📖 My Borrowed Books

            </a>


        </div>



        <!-- ====================================== -->
        <!-- WELCOME MESSAGE -->
        <!-- ====================================== -->

        <div class="content-box">


            <h2>
                Welcome, <?php
                echo htmlspecialchars(
                    $_SESSION["username"]
                );
                ?>! 👋
            </h2>


            <p>

                You can browse available books,
                borrow books, and view your borrowing
                history using the menu on the left.

            </p>


        </div>


    </main>


</div>


</body>

</html>