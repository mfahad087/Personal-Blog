<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config
include '../includes/config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Set default timezone
date_default_timezone_set('Asia/Karachi');

// Get stats
$total_posts = 0;
$total_categories = 0;
$total_messages = 0;
$recent_posts = [];

try {
    // Get total posts
    $posts_query = "SELECT COUNT(*) as count FROM posts";
    $posts_result = mysqli_query($conn, $posts_query);
    if($posts_result) {
        $posts_data = mysqli_fetch_assoc($posts_result);
        $total_posts = $posts_data['count'];
    }
    
    // Get total categories
    $cats_query = "SELECT COUNT(*) as count FROM categories";
    $cats_result = mysqli_query($conn, $cats_query);
    if($cats_result) {
        $cats_data = mysqli_fetch_assoc($cats_result);
        $total_categories = $cats_data['count'];
    }
    
    // Get total messages
    $msg_query = "SELECT COUNT(*) as count FROM messages";
    $msg_result = mysqli_query($conn, $msg_query);
    if($msg_result) {
        $msg_data = mysqli_fetch_assoc($msg_result);
        $total_messages = $msg_data['count'];
    }
    
    // Get recent posts
    $recent_query = "SELECT p.*, c.name as category_name FROM posts p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC LIMIT 5";
    $recent_result = mysqli_query($conn, $recent_query);
    if($recent_result) {
        while($row = mysqli_fetch_assoc($recent_result)) {
            $recent_posts[] = $row;
        }
    }
} catch(Exception $e) {
    // Handle error silently for now
    error_log("Dashboard error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - The Fahad's Space</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding-top: 20px;
        }
        
        .admin-sidebar h2 {
            padding: 0 20px 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: white;
        }
        
        .admin-sidebar ul {
            list-style: none;
        }
        
        .admin-sidebar li {
            padding: 12px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .admin-sidebar li:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .admin-sidebar li.active {
            background-color: #3498db;
        }
        
        .admin-sidebar a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-sidebar a:hover {
            color: white;
        }
        
        .admin-main {
            flex: 1;
            padding: 30px;
            background-color: #f5f5f5;
        }
        
        .dashboard-header {
            margin-bottom: 30px;
        }
        
        .dashboard-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stat-card h3 {
            font-size: 36px;
            color: #3498db;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .recent-posts {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
        }
        
        .recent-posts h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .no-posts {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .no-posts a {
            color: #3498db;
            text-decoration: none;
        }
        
        .no-posts a:hover {
            text-decoration: underline;
        }
        
        .posts-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .posts-table th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            color: #495057;
            font-weight: 600;
        }
        
        .posts-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .posts-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-published {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-draft {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .action-links {
            display: flex;
            gap: 10px;
        }
        
        .action-links a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .edit-btn {
            background-color: #3498db;
            color: white;
        }
        
        .delete-btn {
            background-color: #e74c3c;
            color: white;
        }
        
        .view-btn {
            background-color: #27ae60;
            color: white;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
                padding: 10px;
            }
            
            .admin-sidebar ul {
                display: flex;
                flex-wrap: wrap;
            }
            
            .admin-sidebar li {
                flex: 1;
                min-width: 120px;
            }
            
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            
            .posts-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li class="active">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="add_post.php">
                        <i class="fas fa-plus-circle"></i> Add Post
                    </a>
                </li>
                <li>
                    <a href="manage_posts.php">
                        <i class="fas fa-list"></i> Manage Posts
                    </a>
                </li>
                <li>
                    <a href="manage_categories.php">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                </li>
                <li>
                    <a href="messages.php">
                        <i class="fas fa-envelope"></i> Messages
                        <?php if($total_messages > 0): ?>
                        <span style="background-color: #e74c3c; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: auto;">
                            <?php echo $total_messages; ?>
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
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="dashboard-header">
                <h1>Dashboard</h1>
                <p>Welcome to your blog admin panel</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3><?php echo $total_posts; ?></h3>
                    <p>Total Posts</p>
                </div>
                
                <div class="stat-card">
                    <h3><?php echo $total_categories; ?></h3>
                    <p>Categories</p>
                </div>
                
                <div class="stat-card">
                    <h3><?php echo $total_messages; ?></h3>
                    <p>Messages</p>
                </div>
            </div>
            
            <!-- Recent Posts -->
            <div class="recent-posts">
                <h2>Recent Posts</h2>
                
                <?php if(count($recent_posts) > 0): ?>
                <table class="posts-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_posts as $post): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($post['title'], 0, 40)); ?>...</td>
                            <td><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $post['status']; ?>">
                                    <?php echo ucfirst($post['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-links">
                                    <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="edit-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="manage_posts.php?delete=<?php echo $post['id']; ?>" 
                                       onclick="return confirm('Delete this post?')" 
                                       class="delete-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-posts">
                    <p>No posts found yet.</p>
                    <p><a href="add_post.php">Add your first post</a> to get started.</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            // Add active class to current page in sidebar
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.admin-sidebar a');
            
            menuItems.forEach(item => {
                const href = item.getAttribute('href');
                if (href === currentPage || (currentPage === '' && href === 'dashboard.php')) {
                    item.parentElement.classList.add('active');
                } else {
                    item.parentElement.classList.remove('active');
                }
            });
            
            // Make sure tables are responsive on mobile
            const tables = document.querySelectorAll('.posts-table');
            tables.forEach(table => {
                const headers = [];
                table.querySelectorAll('th').forEach(th => {
                    headers.push(th.textContent);
                });
                
                table.querySelectorAll('tr').forEach((row, rowIndex) => {
                    if (rowIndex === 0) return; // Skip header row
                    row.querySelectorAll('td').forEach((cell, cellIndex) => {
                        if (window.innerWidth <= 768) {
                            cell.setAttribute('data-label', headers[cellIndex]);
                        } else {
                            cell.removeAttribute('data-label');
                        }
                    });
                });
            });
            
            // Update on window resize
            window.addEventListener('resize', function() {
                tables.forEach(table => {
                    const headers = [];
                    table.querySelectorAll('th').forEach(th => {
                        headers.push(th.textContent);
                    });
                    
                    table.querySelectorAll('tr').forEach((row, rowIndex) => {
                        if (rowIndex === 0) return;
                        row.querySelectorAll('td').forEach((cell, cellIndex) => {
                            if (window.innerWidth <= 768) {
                                cell.setAttribute('data-label', headers[cellIndex]);
                            } else {
                                cell.removeAttribute('data-label');
                            }
                        });
                    });
                });
            });
        });
    </script>
</body>
</html>
