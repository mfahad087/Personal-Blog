<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login_new.php');
    exit();
}

include '../includes/config.php';

// Get statistics
$total_posts = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM posts"));
$total_messages = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM messages"));
$total_categories = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM categories"));
$published_posts = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM posts WHERE status = 'published'"));

// Get recent posts
$recent_posts_query = "SELECT * FROM posts ORDER BY created_at DESC LIMIT 5";
$recent_posts = mysqli_query($conn, $recent_posts_query);

// Get recent messages
$recent_messages_query = "SELECT * FROM messages ORDER BY created_at DESC LIMIT 5";
$recent_messages = mysqli_query($conn, $recent_messages_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - The Fahad's Space</title>
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
        
        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
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
        
        .header-actions {
            display: flex;
            gap: 10px;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            border-left: 4px solid var(--secondary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stat-card.success {
            border-left-color: var(--success-color);
        }
        
        .stat-card.warning {
            border-left-color: var(--warning-color);
        }
        
        .stat-card.danger {
            border-left-color: var(--danger-color);
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: var(--secondary-color);
        }
        
        .stat-card.success .stat-icon {
            color: var(--success-color);
        }
        
        .stat-card.warning .stat-icon {
            color: var(--warning-color);
        }
        
        .stat-card.danger .stat-icon {
            color: var(--danger-color);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--gray-color);
            font-size: 14px;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .text-muted {
            color: var(--gray-color);
            font-size: 12px;
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
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .menu-toggle {
            display: none;
        }

        @media (max-width: 576px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
                z-index: 1000;
            }
            
            .admin-sidebar.active {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .main-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                position: relative;
            }
            
            .menu-toggle {
                display: block;
                position: absolute;
                left: 20px;
                top: 20px;
                background: none;
                border: none;
                font-size: 24px;
                color: var(--primary-color);
                cursor: pointer;
                z-index: 1001;
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
                <li><a href="dashboard_new.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="add_post_new.php"><i class="fas fa-plus-circle"></i> Add Post</a></li>
                <li><a href="manage_posts_new.php"><i class="fas fa-edit"></i> Manage Posts</a></li>
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
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                    <p class="text-muted">Welcome back, <?php echo $_SESSION['admin_username']; ?>!</p>
                </div>
                <div class="header-actions">
                    <a href="add_post_new.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Post
                    </a>
                    <a href="../index.php" target="_blank" class="btn btn-success">
                        <i class="fas fa-eye"></i> View Site
                    </a>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_posts; ?></div>
                    <div class="stat-label">Total Posts</div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo $published_posts; ?></div>
                    <div class="stat-label">Published Posts</div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_categories; ?></div>
                    <div class="stat-label">Categories</div>
                </div>
                
                <div class="stat-card danger">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_messages; ?></div>
                    <div class="stat-label">Messages</div>
                </div>
            </div>
            
            <!-- Recent Content -->
            <div class="content-grid">
                <!-- Recent Posts -->
                <div class="content-card">
                    <div class="content-header">
                        <h3><i class="fas fa-file-alt"></i> Recent Posts</h3>
                        <a href="manage_posts_new.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="content-body">
                        <?php if(mysqli_num_rows($recent_posts) > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($post = mysqli_fetch_assoc($recent_posts)): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo substr($post['title'], 0, 30); ?>...</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $post['status'] == 'published' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($post['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="text-align: center; color: #666;">No posts found</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Messages -->
                <div class="content-card">
                    <div class="content-header">
                        <h3><i class="fas fa-envelope"></i> Recent Messages</h3>
                        <a href="messages_new.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="content-body">
                        <?php if(mysqli_num_rows($recent_messages) > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($message = mysqli_fetch_assoc($recent_messages)): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $message['name']; ?></strong>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?php echo substr($message['email'], 0, 20); ?>...</span>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?php echo date('M d, Y', strtotime($message['created_at'])); ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="text-align: center; color: #666;">No messages found</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    
    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.admin-sidebar').classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.admin-sidebar');
            const toggle = document.getElementById('menuToggle');
            
            if (window.innerWidth <= 576 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target) && 
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>
