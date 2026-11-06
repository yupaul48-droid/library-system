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
// GET MY BORROWING RECORDS
// ==========================================

$sql = "SELECT

            borrowings.id,

            books.title,
            books.author,
            books.category,

            borrowings.borrow_date,
            borrowings.return_date,
            borrowings.status

        FROM borrowings

        INNER JOIN books

        ON borrowings.book_id = books.id

        WHERE borrowings.user_id = ?

        ORDER BY borrowings.id DESC";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Borrowed Books - Library System</title>

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

                <h1>My Borrowed Books</h1>

                <p>
                    View your current and previous borrowing records.
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
        <!-- BORROWING TABLE -->
        <!-- ====================================== -->

        <div class="content-box">


            <h2>
                📖 My Borrowing History
            </h2>


            <br>


            <?php if ($result->num_rows > 0): ?>


                <div style="overflow-x:auto;">


                    <table>


                        <thead>

                            <tr>

                                <th>
                                    Book
                                </th>

                                <th>
                                    Author
                                </th>

                                <th>
                                    Category
                                </th>

                                <th>
                                    Borrow Date
                                </th>

                                <th>
                                    Return Date
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>



                        <tbody>


                        <?php while ($borrow = $result->fetch_assoc()): ?>


                            <tr>


                                <!-- BOOK -->

                                <td>

                                    📚

                                    <?php

                                    echo htmlspecialchars(
                                        $borrow["title"]
                                    );

                                    ?>

                                </td>



                                <!-- AUTHOR -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $borrow["author"]
                                    );

                                    ?>

                                </td>



                                <!-- CATEGORY -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $borrow["category"]
                                    );

                                    ?>

                                </td>



                                <!-- BORROW DATE -->

                                <td>

                                    <?php

                                    echo $borrow["borrow_date"];

                                    ?>

                                </td>



                                <!-- RETURN DATE -->

                                <td>

                                    <?php

                                    if (
                                        $borrow["return_date"] != null
                                    ) {

                                        echo $borrow["return_date"];

                                    } else {

                                        echo "Not returned";

                                    }

                                    ?>

                                </td>



                                <!-- STATUS -->

                                <td>


                                    <?php

                                    if (
                                        $borrow["status"] == "borrowed"
                                    ):

                                    ?>

                                        📖 Borrowed


                                    <?php else: ?>


                                        ✅ Returned


                                    <?php endif; ?>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <p>

                    📖 You don't have any borrowing records yet.

                </p>


            <?php endif; ?>


        </div>


    </main>


</div>


</body>

</html>