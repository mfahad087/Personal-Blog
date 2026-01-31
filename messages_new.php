<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login_new.php');
    exit();
}

include '../includes/config.php';

// Handle delete message
if(isset($_GET['delete'])) {
    $message_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM messages WHERE id = $message_id");
    header('Location: messages_new.php');
    exit();
}

// Handle mark as read
if(isset($_GET['mark_read'])) {
    $message_id = intval($_GET['mark_read']);
    mysqli_query($conn, "UPDATE messages SET is_read = 1 WHERE id = $message_id");
    header('Location: messages_new.php');
    exit();
}

// Get all messages
$messages_query = "SELECT * FROM messages ORDER BY created_at DESC";
$messages = mysqli_query($conn, $messages_query);

// Get unread count
$unread_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM messages WHERE is_read = 0"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - The Fahad's Space</title>
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
        
        .unread-badge {
            background-color: var(--danger-color);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
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
        
        .message-item {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
            transition: var(--transition);
        }
        
        .message-item:hover {
            box-shadow: var(--shadow);
        }
        
        .message-item.unread {
            border-left: 4px solid var(--secondary-color);
            background-color: #f8f9fa;
        }
        
        .message-header {
            padding: 15px;
            background-color: var(--light-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .message-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .message-name {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .message-email {
            color: var(--gray-color);
            font-size: 14px;
        }
        
        .message-date {
            color: var(--gray-color);
            font-size: 12px;
        }
        
        .message-subject {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .message-content {
            padding: 15px;
        }
        
        .message-text {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
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
        
        .badge-primary {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-color);
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: var(--border-color);
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
            
            .message-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .message-actions {
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
                <li><a href="manage_posts_new.php"><i class="fas fa-edit"></i> Manage Posts</a></li>
                <li><a href="manage_categories_new.php"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="messages_new.php" class="active"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-eye"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="main-header">
                <div>
                    <h1><i class="fas fa-envelope"></i> Messages</h1>
                    <p class="text-muted">Manage contact form messages</p>
                </div>
                <div>
                    <?php if($unread_count > 0): ?>
                        <span class="unread-badge"><?php echo $unread_count; ?> unread</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="content-card">
                <div class="content-header">
                    <h3><i class="fas fa-inbox"></i> All Messages</h3>
                    <span class="badge badge-primary"><?php echo mysqli_num_rows($messages); ?> total</span>
                </div>
                <div class="content-body">
                    <?php if(mysqli_num_rows($messages) > 0): ?>
                        <?php while($message = mysqli_fetch_assoc($messages)): ?>
                            <div class="message-item <?php echo $message['is_read'] == 0 ? 'unread' : ''; ?>">
                                <div class="message-header">
                                    <div class="message-info">
                                        <div class="message-name"><?php echo htmlspecialchars($message['name']); ?></div>
                                        <div class="message-email"><?php echo htmlspecialchars($message['email']); ?></div>
                                        <div class="message-date">
                                            <i class="fas fa-clock"></i> <?php echo date('M d, Y h:i A', strtotime($message['created_at'])); ?>
                                        </div>
                                    </div>
                                    <div>
                                        <?php if($message['is_read'] == 0): ?>
                                            <span class="badge badge-primary">Unread</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Read</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="message-content">
                                    <div class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                                    <div class="message-text"><?php echo nl2br(htmlspecialchars($message['message'])); ?></div>
                                    <div class="message-actions">
                                        <?php if($message['is_read'] == 0): ?>
                                            <a href="messages_new.php?mark_read=<?php echo $message['id']; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Mark as Read
                                            </a>
                                        <?php endif; ?>
                                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-reply"></i> Reply
                                        </a>
                                        <a href="messages_new.php?delete=<?php echo $message['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this message?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No messages found</h3>
                            <p>You haven't received any messages yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
