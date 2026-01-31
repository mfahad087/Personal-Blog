<?php
include 'includes/config.php';

// Add likes_count column to posts table if it doesn't exist
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM posts LIKE 'likes_count'");
if (mysqli_num_rows($check_col) == 0) {
    $sql = "ALTER TABLE posts ADD COLUMN likes_count INT DEFAULT 0";
    if (mysqli_query($conn, $sql)) {
        echo "Column 'likes_count' added successfully.<br>";
    } else {
        echo "Error adding column: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Column 'likes_count' already exists.<br>";
}

// Create post_likes table to track IP addresses
$sql = "CREATE TABLE IF NOT EXISTS post_likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (post_id, ip_address)
)";

if (mysqli_query($conn, $sql)) {
    echo "Table 'post_likes' created or checked successfully.<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

echo "Database setup complete.";
?>
