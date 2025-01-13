<?php
// submit.php

// Database connection function
function connectDB() {
    $host = 'localhost';
    $dbname = 'demo';
    $username = 'root';
    $password = '';

    // Create a new mysqli connection
    $conn = new mysqli($host, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize form inputs
    $book_title = htmlspecialchars(trim($_POST['book_title'] ?? ''));
    $author_name = htmlspecialchars(trim($_POST['author_name'] ?? ''));
    $isbn = htmlspecialchars(trim($_POST['isbn'] ?? ''));
    $count = (int)$_POST['count'];  // Convert to integer
    $category = htmlspecialchars(trim($_POST['category'] ?? ''));

    // Validate required fields
    if (empty($book_title) || empty($author_name) || empty($isbn) || empty($count) || empty($category)) {
        die("All fields are required.");
    }

    // Insert the data into the database
    try {
        $conn = connectDB();

        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO books (book_title, author_name, isbn, count, category) VALUES (?, ?, ?, ?, ?)");

        // Bind parameters to the SQL statement
        $stmt->bind_param("sssis", $book_title, $author_name, $isbn, $count, $category);

        // Execute the statement
        if ($stmt->execute()) {
            echo "Book details saved successfully!";
        } else {
            echo "Error saving book details: " . $stmt->error;
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
