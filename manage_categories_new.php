<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login_new.php');
    exit();
}

include '../includes/config.php';

// Handle add category
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $slug = strtolower(str_replace(' ', '-', $name));
    
    $query = "INSERT INTO categories (name, slug, description) VALUES ('$name', '$slug', '$description')";
    if(mysqli_query($conn, $query)) {
        header('Location: manage_categories_new.php?success=1');
        exit();
    } else {
        $error = "Error adding category: " . mysqli_error($conn);
    }
}

// Handle delete category
if(isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    
    // Check if category has posts
    $posts_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM posts WHERE category_id = $category_id");
    $posts_count = mysqli_fetch_assoc($posts_check)['count'];
    
    if($posts_count == 0) {
        mysqli_query($conn, "DELETE FROM categories WHERE id = $category_id");
    }
    
    header('Location: manage_categories_new.php');
    exit();
}

// Get all categories
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

// Handle success message
$success = '';
if(isset($_GET['success'])) {
    $success = "Category added successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - The Fahad's Space</title>
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
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
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
        
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
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
            
            .content-grid {
                grid-template-columns: 1fr;
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
                <li><a href="manage_posts_new.php"><i class="fas fa-edit"></i> Manage Posts</a></li>
                <li><a href="manage_categories_new.php" class="active"><i class="fas fa-tags"></i> Categories</a></li>
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
                    <h1><i class="fas fa-tags"></i> Manage Categories</h1>
                    <p class="text-muted">Manage blog post categories</p>
                </div>
            </div>
            
            <?php if($success): ?>
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="content-grid">
                <!-- Add Category Form -->
                <div class="content-card">
                    <div class="content-header">
                        <h3><i class="fas fa-plus"></i> Add New Category</h3>
                    </div>
                    <div class="content-body">
                        <form method="POST">
                            <input type="hidden" name="add_category" value="1">
                            <div class="form-group">
                                <label for="name">Category Name</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Enter category description..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Category
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Categories List -->
                <div class="content-card">
                    <div class="content-header">
                        <h3><i class="fas fa-list"></i> All Categories</h3>
                    </div>
                    <div class="content-body">
                        <?php if(mysqli_num_rows($categories) > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Posts Count</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($category = mysqli_fetch_assoc($categories)): 
                                        $posts_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM posts WHERE category_id = " . $category['id']));
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $category['name']; ?></strong>
                                        </td>
                                        <td>
                                            <?php echo $category['description'] ? substr($category['description'], 0, 80) . '...' : '<span style="color: #999; font-style: italic;">No description</span>'; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo $posts_count; ?> posts</span>
                                        </td>
                                        <td>
                                            <?php if($posts_count == 0): ?>
                                                <a href="manage_categories_new.php?delete=<?php echo $category['id']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this category?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-danger" disabled title="Cannot delete category with posts">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-tags"></i>
                                <h3>No categories found</h3>
                                <p>Create your first category to organize your posts</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
