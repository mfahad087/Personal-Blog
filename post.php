<?php
include 'includes/config.php';
$page_title = "Post Details";
include 'includes/header.php';

// Get post by slug
if(isset($_GET['slug']) && !empty($_GET['slug'])) {
    $slug = mysqli_real_escape_string($conn, $_GET['slug']);
    
    // Debug: Log the slug being searched
    error_log("Searching for slug: " . $slug);
    
    $query = "SELECT p.*, c.name as category_name FROM posts p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.slug = '$slug' AND p.status = 'published'";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        // Debug: Log query error
        error_log("Query error: " . mysqli_error($conn));
        echo "<div class='container'><h2>Database error occurred</h2></div>";
        include 'includes/footer.php';
        exit();
    }
    
    if(mysqli_num_rows($result) > 0) {
        $post = mysqli_fetch_assoc($result);
        // Debug: Log success
        error_log("Post found: " . $post['title']);
    } else {
        // Debug: Log not found
        error_log("Post not found for slug: " . $slug);
        echo "<div class='container'><h2>Post not found</h2><p>The post you're looking for doesn't exist or has been removed.</p><a href='index.php' class='read-more'>← Back to Home</a></div>";
        include 'includes/footer.php';
        exit();
    }
} else {
    // Debug: Log missing slug
    error_log("No slug provided");
    header('Location: index.php');
    exit();
}
?>

<!-- Single Post Section -->
<section class="single-post" style="padding-top: 120px;">
    <div class="container">
        <div class="post-detail">
            <?php if($post['featured_image']): ?>
            <div class="post-image-container">
                <img src="images/uploads/<?php echo $post['featured_image']; ?>" alt="<?php echo $post['title']; ?>" class="post-detail-img">
            </div>
            <?php endif; ?>
            
            <div class="post-detail-content">
                <div class="post-meta">
                    <span><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                    <span><?php echo $post['category_name'] ?? 'Uncategorized'; ?></span>
                </div>
                
                <h1 class="post-detail-title"><?php echo $post['title']; ?></h1>
                
                <div class="post-detail-body">
                    <?php echo $post['content']; ?>
                </div>
                
                <div class="post-actions">
                    <a href="index.php" class="read-more">← Back to Posts</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
