<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "demo"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch books from the database
$sql = "SELECT book_title, author_name, isbn, count, category FROM books"; // Update with your table and column names
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book List</title>
    <style>
        .top-box1 {
            border: 1px solid #ccc;
            padding: 15px;
            width: 90%;
            margin: 20px auto;
            background-color: #f9f9f9;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .book-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .book-item:last-child {
            border-bottom: none;
        }
        .book-title {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }
        .book-details {
            font-size: 14px;
            color: #555;
        }
        .book-details span {
            display: inline-block;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="top-box1">
        <h2>Book List</h2>
        <?php if ($result->num_rows > 0): ?>
            <div>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="book-item">
                        <div class="book-title">
                            <?php echo htmlspecialchars($row['book_title']); ?>
                        </div>
                        <div class="book-details">
                            <span>Author: <?php echo htmlspecialchars($row['author_name']); ?></span>
                            <span>ISBN: <?php echo htmlspecialchars($row['isbn']); ?></span>
                            <span>Available: <?php echo htmlspecialchars($row['count']); ?></span>
                            <span>Category: <?php echo htmlspecialchars($row['category']); ?></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No books found in the database.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
