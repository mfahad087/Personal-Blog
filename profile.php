<?php
session_start();

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login_new.php');
    exit();
}

include '../includes/config.php';

$success = '';
$error = '';
$admin = null;

// Create admins table if not exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    full_name VARCHAR(100) DEFAULT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create admin images directory if not exists
if(!file_exists('../images/admin')) {
    mkdir('../images/admin', 0777, true);
}

// Get admin data
$result = mysqli_query($conn, "SELECT * FROM admins LIMIT 1");
if(mysqli_num_rows($result) == 0) {
    mysqli_query($conn, "INSERT INTO admins (username, password, email, full_name) VALUES ('admin', 'admin123', 'admin@example.com', 'Administrator')");
    $result = mysqli_query($conn, "SELECT * FROM admins LIMIT 1");
}
$admin = mysqli_fetch_assoc($result);

// Handle profile update
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        
        // Handle profile picture upload
        $profile_picture = $admin['profile_picture']; // Keep current picture by default
        
        if(isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $file = $_FILES['profile_picture'];
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if(in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
                // Generate unique filename
                $filename = 'admin_' . time() . '_' . basename($file['name']);
                $upload_path = '../images/admin/' . $filename;
                
                if(move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Delete old profile picture if exists
                    if($admin['profile_picture'] && file_exists('../images/admin/' . $admin['profile_picture'])) {
                        unlink('../images/admin/' . $admin['profile_picture']);
                    }
                    
                    $profile_picture = $filename;
                } else {
                    $error = "Error uploading profile picture!";
                }
            } else {
                $error = "Invalid file type or size! Only JPG, PNG, GIF (max 2MB) allowed.";
            }
        }
        
        if(!empty($username) && empty($error)) {
            $update_query = "UPDATE admins SET username = '$username', email = '$email', full_name = '$full_name', profile_picture = '$profile_picture' WHERE id = " . $admin['id'];
            
            if(mysqli_query($conn, $update_query)) {
                $success = "Profile updated successfully!";
                $_SESSION['admin_username'] = $username;
                
                $result = mysqli_query($conn, "SELECT * FROM admins LIMIT 1");
                $admin = mysqli_fetch_assoc($result);
            } else {
                $error = "Error updating profile: " . mysqli_error($conn);
            }
        } elseif(empty($error)) {
            $error = "Username is required!";
        }
    }
    
    if(isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if($current_password === $admin['password'] || password_verify($current_password, $admin['password'])) {
            if($new_password === $confirm_password) {
                if(strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    mysqli_query($conn, "UPDATE admins SET password = '$hashed_password' WHERE id = " . $admin['id']);
                    $success = "Password changed successfully!";
                } else {
                    $error = "Password must be at least 6 characters";
                }
            } else {
                $error = "New passwords do not match";
            }
        } else {
            $error = "Current password is incorrect";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - The Fahad's Space</title>
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
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h2 {
            font-size: 18px;
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
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255,255,255,0.1);
            padding-left: 25px;
        }
        
        .admin-main {
            margin-left: 250px;
            padding: 20px;
            flex: 1;
        }
        
        .main-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .main-header h1 {
            color: #2c3e50;
            font-size: 28px;
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            overflow: hidden;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 200px;
            }
            
            .admin-main {
                margin-left: 200px;
            }
            
            .cards-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
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
                <li><a href="messages_new.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="profile.php" class="active"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-eye"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        
        <main class="admin-main">
            <div class="main-header">
                <h1><i class="fas fa-user-cog"></i> Profile Settings</h1>
            </div>
            
            <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="cards-grid">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user"></i> Profile Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="profile-avatar">
                            <?php if($admin['profile_picture']): ?>
                                <img src="../images/admin/<?php echo htmlspecialchars($admin['profile_picture']); ?>" alt="Profile Picture">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" class="form-control" 
                                       value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($admin['full_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="profile_picture">Profile Picture</label>
                                <input type="file" id="profile_picture" name="profile_picture" class="form-control" 
                                       accept="image/*" onchange="previewImage(event)">
                                <small style="color: #666; font-size: 12px;">
                                    <i class="fas fa-info-circle"></i> Upload JPG, PNG, or GIF (max 2MB)
                                </small>
                                <?php if($admin['profile_picture']): ?>
                                <div style="margin-top: 10px;">
                                    <img src="../images/admin/<?php echo htmlspecialchars($admin['profile_picture']); ?>" 
                                         alt="Current Profile" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
                                    <small style="color: #666; margin-left: 10px;">Current photo</small>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-lock"></i> Change Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" 
                                       required minlength="6" placeholder="Minimum 6 characters">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                       required minlength="6" placeholder="Re-enter new password">
                            </div>
                            
                            <button type="submit" name="change_password" class="btn btn-secondary">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Update main avatar preview
                    const avatar = document.querySelector('.profile-avatar');
                    if (avatar) {
                        avatar.innerHTML = `<img src="${e.target.result}" alt="Profile Preview">`;
                    }
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>
