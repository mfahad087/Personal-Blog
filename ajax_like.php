<?php
include 'includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$ip_address = $_SERVER['REMOTE_ADDR'];

if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

// Check if already liked
$check_query = "SELECT id FROM post_likes WHERE post_id = $post_id AND ip_address = '$ip_address'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    // UNLIKE: Remove record and decrement
    $delete_query = "DELETE FROM post_likes WHERE post_id = $post_id AND ip_address = '$ip_address'";
    if (mysqli_query($conn, $delete_query)) {
        $update_query = "UPDATE posts SET likes_count = GREATEST(0, likes_count - 1) WHERE id = $post_id";
        mysqli_query($conn, $update_query);
        $action = 'unliked';
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error during unlike']);
        exit;
    }
} else {
    // LIKE: Insert record and increment
    $insert_query = "INSERT INTO post_likes (post_id, ip_address) VALUES ($post_id, '$ip_address')";
    if (mysqli_query($conn, $insert_query)) {
        $update_query = "UPDATE posts SET likes_count = likes_count + 1 WHERE id = $post_id";
        mysqli_query($conn, $update_query);
        $action = 'liked';
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error during like']);
        exit;
    }
}

// Get new count
$count_query = "SELECT likes_count FROM posts WHERE id = $post_id";
$count_result = mysqli_query($conn, $count_query);
$row = mysqli_fetch_assoc($count_result);

echo json_encode([
    'success' => true, 
    'action' => $action,
    'likes_count' => $row['likes_count']
]);
?>
