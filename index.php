<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "demo"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Token management: Read the token data from token.json file
$tokenFile = 'token.json';
$availableTokens = [];
$usedTokens = [];

if (file_exists($tokenFile)) {
    $jsonData = file_get_contents($tokenFile);
    $data = json_decode($jsonData, true);

    // Handle errors in JSON decoding
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Error decoding JSON: ' . json_last_error_msg());
    }

    $availableTokens = $data['availableTokens'] ?? [];
    $usedTokens = $data['usedTokens'] ?? [];
}

// Handle Update, Delete, and Borrow actions based on ISBN
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle Delete action
    if (isset($_POST['delete']) && isset($_POST['isbn'])) {
        $isbn = $_POST['isbn'];
        $sql = "DELETE FROM books WHERE isbn = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $stmt->close();
        echo "Book deleted successfully!";
    }

    // Handle Update action
    if (isset($_POST['update']) && isset($_POST['isbn'])) {
        $isbn = $_POST['isbn'];
        $book_title = $_POST['book_title'];
        $author_name = $_POST['author_name'];
        $count = $_POST['count'];
        $category = $_POST['category'];

        $sql = "UPDATE books SET book_title = ?, author_name = ?, count = ?, category = ? WHERE isbn = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssds", $book_title, $author_name, $count, $category, $isbn);
        $stmt->execute();
        $stmt->close();
        echo "Book updated successfully!";
    }

    // Handle Book Borrow action with Token
    if (isset($_POST['borrow']) && isset($_POST['token'])) {
        $token = $_POST['token'];
        $borrow_date = $_POST['borrow_date'];
        $return_date = $_POST['return_date'];

        // Check if the token is valid (exists in availableTokens)
        if (in_array($token, $availableTokens)) {
            // Extend the borrow period by 10 days
            $extendedReturnDate = date('Y-m-d', strtotime($return_date . ' +10 days'));

            // Insert Borrow Data into database
            $sql = "INSERT INTO borrow_book (token, borrow_date, return_date) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $token, $borrow_date, $extendedReturnDate);
            $stmt->execute();
            $stmt->close();

            // Move the token to used tokens and update the available tokens
            $usedTokens[] = $token;
            $availableTokens = array_diff($availableTokens, [$token]);

            // Save the updated tokens to the JSON file
            $data['availableTokens'] = $availableTokens;
            $data['usedTokens'] = $usedTokens;
            file_put_contents($tokenFile, json_encode($data));

            echo "Book rented successfully! Your return date has been extended to $extendedReturnDate.";
        } else {
            // If token is not found in availableTokens
            echo "Invalid token. You cannot rent the book.";
        }
    }
}

// Fetch books from the database
$sql = "SELECT book_title, author_name, isbn, count, category FROM books";
$result = $conn->query($sql);

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main">
        <!-- Top Box 1: Display Book List -->
        <div class="container">
            <div class="top-box1">
                <h2>Book List</h2>
                <?php if ($result->num_rows > 0): ?>
                      <?php while ($row = $result->fetch_assoc()): ?>
                           <?php if (!empty($row['book_title']) || !empty($row['author_name']) || !empty($row['isbn']) || !empty($row['count']) || !empty($row['category'])): ?>  <div class="book-item">
                       <div class="book-title"><?php echo htmlspecialchars($row['book_title']); ?></div>
                          <div class="book-details">
                    <?php if (!empty($row['author_name'])): ?>
                        <span>Author: <?php echo htmlspecialchars($row['author_name']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($row['isbn'])): ?>
                        <span>ISBN: <?php echo htmlspecialchars($row['isbn']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($row['count'])): ?>
                        <span>Available: <?php echo htmlspecialchars($row['count']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($row['category'])): ?>
                        <span>Category: <?php echo htmlspecialchars($row['category']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endwhile; ?>
<?php endif; ?>
            </div>
        </div>

        <!-- Middle Section: Update/Delete Book Form -->
        <div class="container">
            <div class="middle-box">
                <h2>Update or Delete Book</h2>
                <form action="index.php" method="post">
                    <label for="isbn">Enter ISBN of the Book:</label><br>
                    <input type="text" id="isbn" name="isbn" required><br>

                    
                    <label for="book_title">Enter Book Title</label><br>
                    <input type="text" id="book_title" name="book_title"><br>

                    <label for="author_name">Enter Author Name</label><br>
                    <input type="text" id="author_name" name="author_name"><br>

                    <label for="count">Available Count</label><br>
                    <input type="number" id="count" name="count"><br>

                    <label for="category">Category</label><br>
                    <input type="text" id="category" name="category"><br>

                    <input type="submit" name="update" value="Update" class="btn-update">
                    <input type="submit" name="delete" value="Delete" class="btn-delete" onclick="return confirm('Are you sure you want to delete this book?');">
                </form>
            </div>
        </div>

        <!-- Tokens Section (Token Info) -->
        <div class="rectangle-container">
            <div class="section" id="availableTokens">
                <strong>Available Tokens:</strong>
                <ul>
                    <?php foreach ($availableTokens as $token): ?>
                        <li><?php echo htmlspecialchars($token); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="section" id="usedTokens">
                <strong>Used Tokens:</strong>
                <ul>
                    <?php foreach ($usedTokens as $token): ?>
                        <li><?php echo htmlspecialchars($token); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Middle Section: Image Boxes -->
        <div class="mid">
            <div class="squarebox">
                <img src="image/41TSiA6ooJL.jpg" alt="Image 1" class="img">
            </div>
            <div class="squarebox">
                <img src="image/51pS8Ww5XvL.jpg" alt="Image 2" class="img">
            </div>
            <div class="squarebox">
                <img src="image/images.jpg" alt="Image 3" class="img">
            </div>
        </div>

        <!-- Lower Section: Forms -->
        <div class="lower">
            <!-- Borrow Book Form -->
            <div class="lowerBox1">
                <h2>Borrow Book</h2>
                <form action="process.php" method="post">
                    <label for="name">Enter Your Full Name</label><br>
                    <input type="text" id="name" name="name" required><br>

                    <label for="id">Enter Your ID</label><br>
                    <input type="text" id="id" name="id" required><br>

                    <label for="bookTitle">Book Title:</label><br>
                    <select id="bookTitle" name="bookTitle" required>
                        <option value="Gatsby">Gatsby</option>
                        <option value="Stars">Our Stars</option>
                        <option value="Sunday">Sunday</option>
                        <option value="Pride">Pride</option>
                        <option value="Divergent">Divergent</option>
                        <option value="Azkaban">Azkaban</option>
                    </select><br>

                    <label for="borrow_date">Borrow Date:</label><br>
                    <input type="date" id="borrow_date" name="borrow_date" required><br>

                    <label for="token">Token</label><br>
                    <input type="text" id="token" name="token" required><br>

                    <label for="return_date">Return Date:</label><br>
                    <input type="date" id="return_date" name="return_date" required><br>

                    <label for="fees">Fees</label><br>
                    <input type="number" id="fees" name="fees" required><br>

                    <label for="paid">Paid</label>
                    <select id="paid" name="paid" required>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select><br>

                    <input type="submit" value="Submit">
                </form>
            </div>

            <!-- Book Entry Form -->
            <div class="lowerBox2">
            <h2>Add A New Book</h2>
                <form action="submit.php" method="post">
                    <label for="book_title">Enter Book Title</label><br>
                    <input type="text" id="book_title" name="book_title" required><br>

                    <label for="author_name">Enter Author Name</label><br>
                    <input type="text" id="author_name" name="author_name" required><br>

                    <label for="isbn">Enter ISBN Number</label><br>
                    <input type="text" id="isbn" name="isbn" required><br>

                    <label for="count">Count</label><br>
                    <input type="number" id="count" name="count" required><br>

                    <label for="category">Category</label><br>
                    <select id="category" name="category" required>
                        <option value="Science Fiction">Science Fiction</option>
                        <option value="The Fault in Our Stars">The Fault in Our Stars</option>
                        <option value="Sunday Or Now">Sunday Or Now</option>
                        <option value="Pride and Prejudice">Pride and Prejudice</option>
                        <option value="Divergent">Divergent</option>
                        <option value="Harry Potter and the Prisoner of Azkaban">Harry Potter and the Prisoner of Azkaban</option>
                    </select><br>

                    <input type="submit" value="Submit">
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
