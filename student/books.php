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
// BORROW BOOK
// ==========================================

if (isset($_POST["borrow_book"])) {

    $book_id = (int) $_POST["book_id"];


    // --------------------------------------
    // CHECK BOOK
    // --------------------------------------

    $sql = "SELECT *
            FROM books
            WHERE id = ?
            AND quantity > 0";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $book_id
    );

    $stmt->execute();

    $result = $stmt->get_result();


    if ($result->num_rows == 1) {


        // --------------------------------------
        // CHECK IF ALREADY BORROWED
        // --------------------------------------

        $check_sql = "SELECT id
                      FROM borrowings
                      WHERE user_id = ?
                      AND book_id = ?
                      AND status = 'borrowed'";

        $check_stmt = $conn->prepare($check_sql);

        $check_stmt->bind_param(
            "ii",
            $user_id,
            $book_id
        );

        $check_stmt->execute();

        $check_result = $check_stmt->get_result();


        if ($check_result->num_rows > 0) {

            $error = "You already borrowed this book.";

        } else {


            // --------------------------------------
            // START TRANSACTION
            // --------------------------------------

            $conn->begin_transaction();


            try {


                // ----------------------------------
                // INSERT BORROWING
                // ----------------------------------

                $insert_sql = "INSERT INTO borrowings
                               (
                                   user_id,
                                   book_id,
                                   borrow_date,
                                   status
                               )

                               VALUES
                               (
                                   ?,
                                   ?,
                                   CURDATE(),
                                   'borrowed'
                               )";


                $insert_stmt = $conn->prepare(
                    $insert_sql
                );


                $insert_stmt->bind_param(
                    "ii",
                    $user_id,
                    $book_id
                );


                $insert_stmt->execute();



                // ----------------------------------
                // DECREASE QUANTITY
                // ----------------------------------

                $update_sql = "UPDATE books

                               SET quantity = quantity - 1

                               WHERE id = ?

                               AND quantity > 0";


                $update_stmt = $conn->prepare(
                    $update_sql
                );


                $update_stmt->bind_param(
                    "i",
                    $book_id
                );


                $update_stmt->execute();



                // ----------------------------------
                // COMMIT
                // ----------------------------------

                $conn->commit();


                $message = "Book borrowed successfully!";


            } catch (Exception $e) {


                // ----------------------------------
                // ROLLBACK
                // ----------------------------------

                $conn->rollback();


                $error = "Something went wrong. Please try again.";

            }

        }


        $check_stmt->close();


    } else {

        $error = "This book is currently unavailable.";

    }


    $stmt->close();

}


// ==========================================
// GET AVAILABLE BOOKS
// ==========================================

$result = $conn->query(
    "SELECT *
     FROM books
     WHERE quantity > 0
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Browse Books - Library System</title>

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

                <h1>Browse Books</h1>

                <p>
                    Browse available books in the library.
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
        <!-- SUCCESS MESSAGE -->
        <!-- ====================================== -->

        <?php if (isset($message)): ?>

            <div class="content-box">

                <p>

                    ✅

                    <?php

                    echo htmlspecialchars(
                        $message
                    );

                    ?>

                </p>

            </div>

        <?php endif; ?>



        <!-- ====================================== -->
        <!-- ERROR MESSAGE -->
        <!-- ====================================== -->

        <?php if (isset($error)): ?>

            <div class="content-box">

                <p class="error-message">

                    ❌

                    <?php

                    echo htmlspecialchars(
                        $error
                    );

                    ?>

                </p>

            </div>

        <?php endif; ?>



        <!-- ====================================== -->
        <!-- BOOK TABLE -->
        <!-- ====================================== -->

        <div class="content-box">


            <h2>
                📚 Available Books
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
                                    Title
                                </th>

                                <th>
                                    Author
                                </th>

                                <th>
                                    Category
                                </th>

                                <th>
                                    Available
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>



                        <tbody>


                        <?php while ($book = $result->fetch_assoc()): ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <?php

                                    echo $book["id"];

                                    ?>

                                </td>



                                <!-- TITLE -->

                                <td>

                                    📚

                                    <?php

                                    echo htmlspecialchars(
                                        $book["title"]
                                    );

                                    ?>

                                </td>



                                <!-- AUTHOR -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $book["author"]
                                    );

                                    ?>

                                </td>



                                <!-- CATEGORY -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $book["category"]
                                    );

                                    ?>

                                </td>



                                <!-- QUANTITY -->

                                <td>

                                    <?php

                                    echo $book["quantity"];

                                    ?>

                                </td>



                                <!-- BORROW -->

                                <td>


                                    <form method="POST">


                                        <input
                                            type="hidden"
                                            name="book_id"
                                            value="<?php
                                            echo $book["id"];
                                            ?>"
                                        >


                                        <button
                                            type="submit"
                                            name="borrow_book"
                                            class="btn btn-success"

                                            onclick="return confirm(
                                                'Do you want to borrow this book?'
                                            );"
                                        >

                                            📖 Borrow

                                        </button>


                                    </form>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <p>

                    📚 No books are currently available.

                </p>


            <?php endif; ?>


        </div>


    </main>


</div>


</body>

</html>