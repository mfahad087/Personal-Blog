<?php
// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}
?>
<div class="admin-sidebar">
    <h2>Admin Panel</h2>
    <ul>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_post.php' ? 'active' : ''; ?>">
            <a href="add_post.php">
                <i class="fas fa-plus-circle"></i> Add Post
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_posts.php' ? 'active' : ''; ?>">
            <a href="manage_posts.php">
                <i class="fas fa-list"></i> Manage Posts
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_categories.php' ? 'active' : ''; ?>">
            <a href="manage_categories.php">
                <i class="fas fa-tags"></i> Categories
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
            <a href="messages.php">
                <i class="fas fa-envelope"></i> Messages
                <?php
                $unread_query = "SELECT COUNT(*) as count FROM messages WHERE is_read = 0";
                $unread_result = mysqli_query($conn, $unread_query);
                $unread_count = mysqli_fetch_assoc($unread_result)['count'];
                if($unread_count > 0): ?>
                <span style="background-color: #e74c3c; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px;">
                    <?php echo $unread_count; ?>
                </span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>
