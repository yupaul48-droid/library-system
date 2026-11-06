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
// ADD BOOK
// ==========================================

if (isset($_POST["add_book"])) {

    $title = trim($_POST["title"]);
    $author = trim($_POST["author"]);
    $category = trim($_POST["category"]);
    $quantity = (int) $_POST["quantity"];


    if (
        $title != "" &&
        $author != "" &&
        $category != "" &&
        $quantity >= 0
    ) {

        $sql = "INSERT INTO books
                (title, author, category, quantity)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "sssi",
            $title,
            $author,
            $category,
            $quantity
        );

        $stmt->execute();

        $stmt->close();

        header("Location: books.php");
        exit();

    }

}


// ==========================================
// DELETE BOOK
// ==========================================

if (isset($_GET["delete"])) {

    $id = (int) $_GET["delete"];


    // Check if book has borrowing records
    $check_sql = "SELECT COUNT(*) AS total
                  FROM borrowings
                  WHERE book_id = ?";

    $check_stmt = $conn->prepare($check_sql);

    $check_stmt->bind_param(
        "i",
        $id
    );

    $check_stmt->execute();

    $check_result = $check_stmt->get_result();

    $check_row = $check_result->fetch_assoc();

    $check_stmt->close();


    if ($check_row["total"] > 0) {

        $error = "This book cannot be deleted because it has borrowing records.";

    } else {

        $sql = "DELETE FROM books
                WHERE id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "i",
            $id
        );

        $stmt->execute();

        $stmt->close();

        header("Location: books.php");
        exit();

    }

}


// ==========================================
// GET BOOK FOR EDIT
// ==========================================

$edit_book = null;


if (isset($_GET["edit"])) {

    $id = (int) $_GET["edit"];


    $sql = "SELECT *
            FROM books
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $id
    );

    $stmt->execute();

    $result = $stmt->get_result();


    if ($result->num_rows == 1) {

        $edit_book = $result->fetch_assoc();

    }


    $stmt->close();

}


// ==========================================
// UPDATE BOOK
// ==========================================

if (isset($_POST["update_book"])) {

    $id = (int) $_POST["id"];

    $title = trim($_POST["title"]);
    $author = trim($_POST["author"]);
    $category = trim($_POST["category"]);
    $quantity = (int) $_POST["quantity"];


    if (
        $title != "" &&
        $author != "" &&
        $category != "" &&
        $quantity >= 0
    ) {

        $sql = "UPDATE books

                SET title = ?,
                    author = ?,
                    category = ?,
                    quantity = ?

                WHERE id = ?";


        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "sssii",
            $title,
            $author,
            $category,
            $quantity,
            $id
        );

        $stmt->execute();

        $stmt->close();

        header("Location: books.php");
        exit();

    }

}


// ==========================================
// GET ALL BOOKS
// ==========================================

$result = $conn->query(
    "SELECT *
     FROM books
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Books - Library System</title>

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

                <h1>Manage Books</h1>

                <p>
                    Add, edit, view, and delete books.
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



        <!-- ERROR -->

        <?php if (isset($error)): ?>

            <div class="content-box">

                <p class="error-message">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </p>

            </div>

        <?php endif; ?>



        <!-- ====================================== -->
        <!-- ADD / EDIT FORM -->
        <!-- ====================================== -->

        <div class="content-box">


            <?php if ($edit_book): ?>

                <h2>
                    ✏️ Edit Book
                </h2>

                <br>


                <form method="POST">

                    <input
                        type="hidden"
                        name="id"
                        value="<?php
                        echo $edit_book["id"];
                        ?>"
                    >


                    <div class="form-group">

                        <label>
                            Book Title
                        </label>

                        <input
                            type="text"
                            name="title"
                            value="<?php
                            echo htmlspecialchars(
                                $edit_book["title"]
                            );
                            ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Author
                        </label>

                        <input
                            type="text"
                            name="author"
                            value="<?php
                            echo htmlspecialchars(
                                $edit_book["author"]
                            );
                            ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Category
                        </label>

                        <input
                            type="text"
                            name="category"
                            value="<?php
                            echo htmlspecialchars(
                                $edit_book["category"]
                            );
                            ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Quantity
                        </label>

                        <input
                            type="number"
                            name="quantity"
                            min="0"
                            value="<?php
                            echo $edit_book["quantity"];
                            ?>"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        name="update_book"
                        class="btn btn-primary"
                    >

                        💾 Update Book

                    </button>


                    <a
                        href="books.php"
                        class="btn"
                    >

                        ❌ Cancel

                    </a>

                </form>


            <?php else: ?>


                <h2>
                    ➕ Add New Book
                </h2>

                <br>


                <form method="POST">


                    <div class="form-group">

                        <label>
                            Book Title
                        </label>

                        <input
                            type="text"
                            name="title"
                            placeholder="Enter book title"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Author
                        </label>

                        <input
                            type="text"
                            name="author"
                            placeholder="Enter author"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Category
                        </label>

                        <input
                            type="text"
                            name="category"
                            placeholder="Example: Programming"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Quantity
                        </label>

                        <input
                            type="number"
                            name="quantity"
                            min="0"
                            placeholder="Enter quantity"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        name="add_book"
                        class="btn btn-success"
                    >

                        ➕ Add Book

                    </button>


                </form>


            <?php endif; ?>


        </div>



        <!-- ====================================== -->
        <!-- BOOK TABLE -->
        <!-- ====================================== -->

        <div class="content-box">

            <h2>
                📚 All Books
            </h2>

            <br>


            <?php if ($result->num_rows > 0): ?>


                <div style="overflow-x:auto;">

                    <table>

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Title</th>

                                <th>Author</th>

                                <th>Category</th>

                                <th>Quantity</th>

                                <th>Actions</th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while ($book = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php echo $book["id"]; ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $book["title"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $book["author"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $book["category"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php echo $book["quantity"]; ?>
                                </td>


                                <td>


                                    <a
                                        href="books.php?edit=<?php
                                        echo $book["id"];
                                        ?>"
                                        class="btn btn-primary"
                                    >

                                        ✏️ Edit

                                    </a>


                                    <a
                                        href="books.php?delete=<?php
                                        echo $book["id"];
                                        ?>"
                                        class="btn btn-danger"

                                        onclick="return confirm(
                                            'Are you sure you want to delete this book?'
                                        );"
                                    >

                                        🗑️ Delete

                                    </a>


                                </td>

                            </tr>

                        <?php endwhile; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>

                <p>
                    📚 No books have been added yet.
                </p>

            <?php endif; ?>


        </div>


    </main>

</div>


</body>

</html>