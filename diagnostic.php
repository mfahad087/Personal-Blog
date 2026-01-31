<?php
// Simple diagnostic test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Simple Database Diagnostic</h1>";

// Test 1: Direct connection with your exact credentials
echo "<h2>Test 1: Direct Connection Test</h2>";

$host = 'sql1313.epizy.com';
$user = 'if0_41032088';
$pass = 'f4020b51b';
$dbname = 'if0_41032088_thefahadspace_db';

$conn = @mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    echo "<p style='color:red;'>❌ Direct connection failed: " . mysqli_connect_error() . "</p>";
    
    // Test 2: Try without database first
    echo "<h2>Test 2: Connection without Database</h2>";
    $conn2 = @mysqli_connect($host, $user, $pass);
    if ($conn2) {
        echo "<p style='color:orange;'>⚠️ Connected to MySQL server but database '$dbname' not found</p>";
        echo "<p>Available databases:</p>";
        $dbs = mysqli_query($conn2, "SHOW DATABASES");
        while ($db = mysqli_fetch_array($dbs)) {
            echo "- " . $db[0] . "<br>";
        }
        mysqli_close($conn2);
    } else {
        echo "<p style='color:red;'>❌ Cannot connect to MySQL server at all</p>";
    }
} else {
    echo "<p style='color:green;'>✅ Connected successfully!</p>";
    
    // Test 3: Check tables
    echo "<h2>Test 3: Check Tables</h2>";
    $tables = mysqli_query($conn, "SHOW TABLES");
    $table_count = mysqli_num_rows($tables);
    echo "<p>Found $table_count tables:</p>";
    
    $has_posts = false;
    while ($table = mysqli_fetch_array($tables)) {
        echo "- " . $table[0] . "<br>";
        if ($table[0] === 'posts') {
            $has_posts = true;
        }
    }
    
    if (!$has_posts) {
        echo "<p style='color:red;'>❌ 'posts' table not found!</p>";
        echo "<p><strong>SOLUTION:</strong> Import your database from localhost</p>";
    } else {
        // Test 4: Check posts
        echo "<h2>Test 4: Check Posts</h2>";
        $count = mysqli_query($conn, "SELECT COUNT(*) as total FROM posts");
        $row = mysqli_fetch_assoc($count);
        echo "<p>Posts in database: " . $row['total'] . "</p>";
        
        if ($row['total'] > 0) {
            $sample = mysqli_query($conn, "SELECT slug, title, status FROM posts LIMIT 3");
            while ($post = mysqli_fetch_assoc($sample)) {
                echo "Post: " . $post['title'] . " (Status: " . $post['status'] . ")<br>";
                echo "Slug: " . $post['slug'] . "<br>";
                echo "<a href='post.php?slug=" . $post['slug'] . "' target='_blank'>Test Link</a><br><br>";
            }
        }
    }
    
    mysqli_close($conn);
}

// Test 5: Show current config
echo "<h2>Test 5: Current Config Settings</h2>";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "<br>";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'Not defined') . "<br>";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "<br>";
echo "Server: " . $_SERVER['HTTP_HOST'] . "<br>";

echo "<hr>";
echo "<h3>🚀 Quick Fix Instructions:</h3>";
echo "<p>If you see 'posts' table not found above:</p>";
echo "<ol>";
echo "<li>Go to localhost phpMyAdmin</li>";
echo "<li>Export your 'the_fahad_space' database</li>";
echo "<li>Go to InfinityFree phpMyAdmin</li>";
echo "<li>Import the SQL file into 'if0_41032088_thefahadspace_db'</li>";
echo "</ol>";
?>
