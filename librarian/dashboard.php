<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "librarian") {
    header("Location: ../login.php");
    exit();
}

require_once "../config.php";

$message = "";
$message_type = "";

/* =========================================
   CLEAR RETURNED BORROWING HISTORY
========================================= */

if (isset($_POST["clear_history"])) {

    $sql = "DELETE FROM borrowings WHERE status = 'returned'";

    if ($conn->query($sql)) {

        $message = "Returned borrowing history has been cleared successfully.";
        $message_type = "success";

    } else {

        $message = "Failed to clear borrowing history.";
        $message_type = "error";

    }
}


/* =========================================
   CLEAR ALL BOOKS
========================================= */

if (isset($_POST["clear_books"])) {

    // Check if there are any borrowing records
    $check_sql = "SELECT COUNT(*) AS total FROM borrowings";

    $check_result = $conn->query($check_sql);

    $borrowing_count = $check_result->fetch_assoc()["total"];

    if ($borrowing_count > 0) {

        $message = "Books cannot be cleared because borrowing records still exist. Clear the borrowing history first.";
        $message_type = "error";

    } else {

        $delete_sql = "DELETE FROM books";

        if ($conn->query($delete_sql)) {

            $message = "All books have been cleared successfully.";
            $message_type = "success";

        } else {

            $message = "Failed to clear books.";
            $message_type = "error";

        }
    }
}


/* =========================================
   DASHBOARD STATISTICS
========================================= */

// Total books
$result = $conn->query(
    "SELECT COUNT(*) AS total FROM books"
);

$total_books = $result->fetch_assoc()["total"];


// Borrowed books
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM borrowings
     WHERE status = 'borrowed'"
);

$borrowed_books = $result->fetch_assoc()["total"];


// Students
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'student'"
);

$students = $result->fetch_assoc()["total"];


// Returned books
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM borrowings
     WHERE status = 'returned'"
);

$returned_books = $result->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Librarian Dashboard</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="dashboard">


    <!-- =====================================
         SIDEBAR
    ====================================== -->

    <aside class="sidebar">

        <div class="sidebar-logo">

            📚 Library System

        </div>


        <ul class="sidebar-menu">

            <li>

                <a href="dashboard.php">

                    🏠 Dashboard

                </a>

            </li>


            <li>

                <a href="books.php">

                    📚 Manage Books

                </a>

            </li>


            <li>

                <a href="borrowings.php">

                    📋 Borrowed Books

                </a>

            </li>


            <li>

                <a href="students.php">

                    👨‍🎓 Students

                </a>

            </li>


            <li>

                <a href="../logout.php" class="logout">

                    🚪 Logout

                </a>

            </li>

        </ul>

    </aside>



    <!-- =====================================
         MAIN CONTENT
    ====================================== -->

    <main class="main-content">


        <!-- TOPBAR -->

        <div class="topbar">

            <div>

                <h1>Librarian Dashboard</h1>

                <p>
                    Welcome,
                    <?php echo htmlspecialchars($_SESSION["username"]); ?>!
                </p>

            </div>


            <div class="user-info">

                👤 Librarian

            </div>

        </div>



        <!-- =====================================
             MESSAGE
        ====================================== -->

        <?php if ($message != ""): ?>

            <?php if ($message_type == "success"): ?>

                <div class="success-message">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php else: ?>

                <div class="dashboard-error">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>

        <?php endif; ?>



        <!-- =====================================
             STATISTICS
        ====================================== -->

        <div class="cards">


            <!-- TOTAL BOOKS -->

            <div class="card">

                <div class="card-icon">

                    📚

                </div>


                <h3>Total Books</h3>


                <p>

                    <?php echo $total_books; ?>

                </p>


                <form
                    method="POST"
                    onsubmit="return confirm(
                        'WARNING: This will permanently delete all books. Make sure there are no borrowing records. Continue?'
                    );"
                    style="margin-top: 15px;"
                >

                    <button
                        type="submit"
                        name="clear_books"
                        class="btn btn-danger"
                    >

                        🗑️ Clear Books

                    </button>

                </form>

            </div>



            <!-- BORROWED BOOKS -->

            <div class="card">

                <div class="card-icon">

                    📖

                </div>


                <h3>Borrowed Books</h3>


                <p>

                    <?php echo $borrowed_books; ?>

                </p>

            </div>



            <!-- STUDENTS -->

            <div class="card">

                <div class="card-icon">

                    👨‍🎓

                </div>


                <h3>Students</h3>


                <p>

                    <?php echo $students; ?>

                </p>

            </div>



            <!-- RETURNED BOOKS -->

            <div class="card">

                <div class="card-icon">

                    ✅

                </div>


                <h3>Returned Books</h3>


                <p>

                    <?php echo $returned_books; ?>

                </p>

            </div>

        </div>



        <!-- =====================================
             QUICK ACTIONS
        ====================================== -->

        <div class="content-box">

            <h2>Quick Actions</h2>


            <div class="quick-actions">


                <a
                    href="books.php"
                    class="btn btn-primary"
                >

                    📚 Manage Books

                </a>


                <a
                    href="borrowings.php"
                    class="btn btn-success"
                >

                    📋 View Borrowed Books

                </a>


                <a
                    href="students.php"
                    class="btn btn-primary"
                >

                    👨‍🎓 View Students

                </a>

            </div>

        </div>



        <!-- =====================================
             CLEAR BORROWING HISTORY
        ====================================== -->

        <div class="content-box">

            <h2>Borrowing History</h2>


            <p style="margin-bottom: 15px;">

                Clear old returned borrowing records.
                Currently borrowed books will not be affected.

            </p>


            <form
                method="POST"
                onsubmit="return confirm(
                    'Are you sure you want to clear all returned borrowing history? This action cannot be undone.'
                );"
            >

                <button
                    type="submit"
                    name="clear_history"
                    class="btn btn-danger"
                >

                    🗑️ Clear Returned History

                </button>

            </form>

        </div>


    </main>

</div>

</body>

</html>