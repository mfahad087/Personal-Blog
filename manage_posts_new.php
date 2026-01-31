<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login_new.php');
    exit();
}

include '../includes/config.php';

// Handle delete action
if(isset($_GET['delete'])) {
    $post_id = intval($_GET['delete']);
    
    // Get post image to delete
    $image_query = "SELECT featured_image FROM posts WHERE id = $post_id";
    $image_result = mysqli_query($conn, $image_query);
    if($post = mysqli_fetch_assoc($image_result)) {
        if($post['featured_image']) {
            unlink("../images/uploads/" . $post['featured_image']);
        }
    }
    
    // Delete post
    $delete_query = "DELETE FROM posts WHERE id = $post_id";
    mysqli_query($conn, $delete_query);
    header('Location: manage_posts_new.php');
    exit();
}

// Get all posts
$posts_query = "SELECT p.*, c.name as category_name FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC";
$posts = mysqli_query($conn, $posts_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts - The Fahad's Space</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --gray-color: #95a5a6;
            --border-color: #ddd;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
            color: #333;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            color: white;
            padding: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
        }
        
        .sidebar-header h2 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-menu li {
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .sidebar-menu a {
            display: block;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255,255,255,0.1);
            padding-left: 25px;
        }
        
        .sidebar-menu a i {
            width: 20px;
            text-align: center;
        }
        
        .admin-main {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .main-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .main-header h1 {
            color: var(--primary-color);
            font-size: 28px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .content-card {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .content-header {
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            color: white;
            padding: 15px 20px;
        }
        
        .content-header h3 {
            font-size: 18px;
            margin: 0;
        }
        
        .content-body {
            padding: 20px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table th {
            background-color: var(--light-color);
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .table tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .post-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .text-muted {
            color: var(--gray-color);
            font-size: 12px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--gray-color);
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--border-color);
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 200px;
            }
            
            .admin-main {
                margin-left: 200px;
            }
            
            .table {
                font-size: 12px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
        
        @media (max-width: 576px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .main-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-rocket"></i> The Fahad's Space</h2>
                <p>Admin Panel</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard_new.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="add_post_new.php"><i class="fas fa-plus-circle"></i> Add Post</a></li>
                <li><a href="manage_posts_new.php" class="active"><i class="fas fa-edit"></i> Manage Posts</a></li>
                <li><a href="manage_categories_new.php"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="messages_new.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-eye"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="main-header">
                <div>
                    <h1><i class="fas fa-edit"></i> Manage Posts</h1>
                    <p class="text-muted">Manage all your blog posts</p>
                </div>
                <div>
                    <a href="add_post_new.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Post
                    </a>
                </div>
            </div>
            
            <div class="content-card">
                <div class="content-header">
                    <h3><i class="fas fa-list"></i> All Posts</h3>
                </div>
                <div class="content-body">
                    <?php if(mysqli_num_rows($posts) > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($post = mysqli_fetch_assoc($posts)): ?>
                                <tr>
                                    <td>
                                        <?php if($post['featured_image']): ?>
                                            <img src="../images/uploads/<?php echo $post['featured_image']; ?>" alt="<?php echo $post['title']; ?>" class="post-image">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; background: var(--light-color); border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-image" style="color: var(--gray-color);"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo $post['title']; ?></strong>
                                        <br>
                                        <span class="text-muted"><?php echo substr($post['excerpt'] ?: $post['content'], 0, 50); ?>...</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning"><?php echo $post['category_name'] ?: 'Uncategorized'; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $post['status'] == 'published' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($post['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div><?php echo date('M d, Y', strtotime($post['created_at'])); ?></div>
                                        <span class="text-muted"><?php echo date('h:i A', strtotime($post['created_at'])); ?></span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_post_new.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="manage_posts_new.php?delete=<?php echo $post['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            <h3>No posts found</h3>
                            <p>Start by creating your first blog post</p>
                            <a href="add_post_new.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Post
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
