<?php
include 'includes/config.php';
$page_title = "Categories";
include 'includes/header.php';

// Get category from URL
$category_id = $_GET['category'] ?? 0;
$category_name = "All Posts";

if($category_id > 0) {
    $cat_query = "SELECT name FROM categories WHERE id = $category_id";
    $cat_result = mysqli_query($conn, $cat_query);
    if($cat = mysqli_fetch_assoc($cat_result)) {
        $category_name = $cat['name'];
    }
}
?>

<section class="featured-posts" style="padding-top: 120px;">
    <div class="container">
        <h2 class="section-title"><?php echo $category_name; ?></h2>
        
        <!-- Category Filter -->
        <div class="category-filter" style="margin-bottom: 40px; display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
            <a href="categories.php" class="btn <?php echo $category_id == 0 ? 'active' : ''; ?>">All</a>
            <?php
            $cat_query = "SELECT * FROM categories";
            $cat_result = mysqli_query($conn, $cat_query);
            while($cat = mysqli_fetch_assoc($cat_result)):
            ?>
            <a href="categories.php?category=<?php echo $cat['id']; ?>" 
               class="btn <?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
                <?php echo $cat['name']; ?>
            </a>
            <?php endwhile; ?>
        </div>
        
        <!-- Posts Grid -->
        <div class="posts-grid">
            <?php
            $posts_query = "SELECT p.*, c.name as category_name FROM posts p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.status = 'published'";
            
            if($category_id > 0) {
                $posts_query .= " AND p.category_id = $category_id";
            }
            
            $posts_query .= " ORDER BY p.created_at DESC";
            $posts_result = mysqli_query($conn, $posts_query);
            
            if(mysqli_num_rows($posts_result) > 0):
                while($post = mysqli_fetch_assoc($posts_result)):
            ?>
            <div class="post-card">
                <?php if($post['featured_image']): ?>
                <img src="images/uploads/<?php echo $post['featured_image']; ?>" alt="<?php echo $post['title']; ?>" class="post-img">
                <?php endif; ?>
                <div class="post-content">
                    <div class="post-meta">
                        <span><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                        <span><?php echo $post['category_name'] ?? 'Uncategorized'; ?></span>
                    </div>
                    <h3 class="post-title"><?php echo $post['title']; ?></h3>
                    <p class="post-excerpt"><?php echo substr($post['excerpt'] ?: $post['content'], 0, 150); ?>...</p>
                    <a href="post.php?slug=<?php echo $post['slug']; ?>" class="read-more">Read More →</a>
                </div>
            </div>
            <?php 
                endwhile;
            else:
            ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <h3>No posts found in this category.</h3>
                <p>Check back later for new content!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>