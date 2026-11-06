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
// RETURN BOOK
// ==========================================

if (isset($_POST["return_book"])) {

    $borrowing_id = (int) $_POST["borrowing_id"];


    // Get borrowing information
    $sql = "SELECT *
            FROM borrowings
            WHERE id = ?
            AND status = 'borrowed'";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $borrowing_id
    );

    $stmt->execute();

    $result = $stmt->get_result();


    if ($result->num_rows == 1) {

        $borrowing = $result->fetch_assoc();

        $book_id = $borrowing["book_id"];


        // Start transaction
        $conn->begin_transaction();


        try {


            // Update borrowing
            $update_sql = "UPDATE borrowings

                           SET status = 'returned',
                               return_date = CURDATE()

                           WHERE id = ?
                           AND status = 'borrowed'";


            $update_stmt = $conn->prepare($update_sql);

            $update_stmt->bind_param(
                "i",
                $borrowing_id
            );

            $update_stmt->execute();


            // Increase book quantity
            $book_sql = "UPDATE books

                         SET quantity = quantity + 1

                         WHERE id = ?";


            $book_stmt = $conn->prepare($book_sql);

            $book_stmt->bind_param(
                "i",
                $book_id
            );

            $book_stmt->execute();


            // Complete transaction
            $conn->commit();


            $message = "Book returned successfully!";


        } catch (Exception $e) {


            $conn->rollback();

            $error = "Something went wrong. Please try again.";

        }

    } else {

        $error = "This borrowing record is not available or has already been returned.";

    }


    $stmt->close();

}


// ==========================================
// GET BORROWING RECORDS
// ==========================================

$sql = "SELECT

            borrowings.id,
            borrowings.borrow_date,
            borrowings.return_date,
            borrowings.status,

            users.username,

            books.title,
            books.author

        FROM borrowings

        INNER JOIN users
        ON borrowings.user_id = users.id

        INNER JOIN books
        ON borrowings.book_id = books.id

        ORDER BY borrowings.id DESC";


$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Borrowed Books - Library System</title>

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


        <div class="topbar">

            <div>

                <h1>Borrowed Books</h1>

                <p>
                    Manage book borrowing and returns.
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

                    echo htmlspecialchars($message);

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

                    echo htmlspecialchars($error);

                    ?>

                </p>

            </div>

        <?php endif; ?>



        <!-- ====================================== -->
        <!-- BORROWING TABLE -->
        <!-- ====================================== -->

        <div class="content-box">

            <h2>
                📖 Borrowing Records
            </h2>

            <br>


            <?php if ($result->num_rows > 0): ?>


                <div style="overflow-x:auto;">

                    <table>


                        <thead>

                            <tr>

                                <th>
                                    Student
                                </th>

                                <th>
                                    Book
                                </th>

                                <th>
                                    Author
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

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while ($borrow = $result->fetch_assoc()): ?>


                            <tr>


                                <!-- STUDENT -->

                                <td>

                                    👤

                                    <?php

                                    echo htmlspecialchars(
                                        $borrow["username"]
                                    );

                                    ?>

                                </td>



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



                                <!-- ACTION -->

                                <td>


                                    <?php

                                    if (
                                        $borrow["status"] == "borrowed"
                                    ):

                                    ?>


                                        <form
                                            method="POST"
                                            style="display:inline;"
                                        >

                                            <input
                                                type="hidden"
                                                name="borrowing_id"
                                                value="<?php
                                                echo $borrow["id"];
                                                ?>"
                                            >


                                            <button
                                                type="submit"
                                                name="return_book"
                                                class="btn btn-success"

                                                onclick="return confirm(
                                                    'Mark this book as returned?'
                                                );"
                                            >

                                                ↩️ Return

                                            </button>

                                        </form>


                                    <?php else: ?>


                                        <span>
                                            ✅ Completed
                                        </span>


                                    <?php endif; ?>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>

                </div>


            <?php else: ?>


                <p>
                    📖 No borrowing records found.
                </p>


            <?php endif; ?>


        </div>


    </main>

</div>


</body>

</html>