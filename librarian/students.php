<?php

session_start();
include "../config.php";


// ==========================================
// CHECK LIBRARIAN ACCESS
// ==========================================

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "librarian") {

    header("Location: ../login.php");
    exit();

}


// ==========================================
// GET ALL STUDENTS
// ==========================================

$sql = "SELECT

            users.id,
            users.username,

            COUNT(
                CASE
                    WHEN borrowings.status = 'borrowed'
                    THEN borrowings.id
                END
            ) AS borrowed_books,

            COUNT(
                CASE
                    WHEN borrowings.status = 'returned'
                    THEN borrowings.id
                END
            ) AS returned_books

        FROM users

        LEFT JOIN borrowings
        ON users.id = borrowings.user_id

        WHERE users.role = 'student'

        GROUP BY users.id, users.username

        ORDER BY users.id DESC";


$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Students - Library System</title>

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

            <p>Librarian Panel</p>

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

                    📖 Borrowed Books

                </a>

            </li>


            <li>

                <a href="students.php">

                    👨‍🎓 Students

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

                <h1>Students</h1>

                <p>
                    View registered students and their borrowing records.
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
        <!-- STUDENT TABLE -->
        <!-- ====================================== -->

        <div class="content-box">


            <h2>
                👨‍🎓 Registered Students
            </h2>


            <br>


            <?php if ($result->num_rows > 0): ?>


                <div style="overflow-x:auto;">


                    <table>


                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Username
                                </th>

                                <th>
                                    Currently Borrowed
                                </th>

                                <th>
                                    Returned Books
                                </th>

                            </tr>

                        </thead>



                        <tbody>


                        <?php while ($student = $result->fetch_assoc()): ?>


                            <tr>


                                <!-- STUDENT ID -->

                                <td>

                                    <?php

                                    echo $student["id"];

                                    ?>

                                </td>



                                <!-- USERNAME -->

                                <td>

                                    👤

                                    <?php

                                    echo htmlspecialchars(
                                        $student["username"]
                                    );

                                    ?>

                                </td>



                                <!-- BORROWED BOOKS -->

                                <td>

                                    📖

                                    <?php

                                    echo $student["borrowed_books"];

                                    ?>

                                </td>



                                <!-- RETURNED BOOKS -->

                                <td>

                                    ✅

                                    <?php

                                    echo $student["returned_books"];

                                    ?>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <p>

                    👨‍🎓 No students have registered yet.

                </p>


            <?php endif; ?>


        </div>


    </main>

</div>


</body>

</html>