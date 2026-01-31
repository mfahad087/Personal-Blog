<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login_new.php');
    exit();
}

include '../includes/config.php';

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $excerpt = mysqli_real_escape_string($conn, $_POST['excerpt']);
    $category_id = intval($_POST['category_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $slug = strtolower(str_replace(' ', '-', $title)) . '-' . time();
    
    // Handle image upload
    $featured_image = '';
    if(isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
        $target_path = "../images/uploads/" . $file_name;
        
        // Validate image
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['featured_image']['type'];
        $file_size = $_FILES['featured_image']['size'];
        
        if(!in_array($file_type, $allowed_types)) {
            $error = "Only JPG, PNG, and GIF images are allowed.";
        } elseif($file_size > 5 * 1024 * 1024) { // 5MB limit
            $error = "Image size must be less than 5MB.";
        } else {
            // Create uploads directory if it doesn't exist
            if(!is_dir("../images/uploads")) {
                mkdir("../images/uploads", 0777, true);
            }
            
            if(move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_path)) {
                $featured_image = $file_name;
            }
        }
    }
    
    if(!$error) {
        $query = "INSERT INTO posts (title, slug, content, excerpt, featured_image, category_id, status) 
                  VALUES ('$title', '$slug', '$content', '$excerpt', '$featured_image', '$category_id', '$status')";
        
        if(mysqli_query($conn, $query)) {
            $success = "Post added successfully!";
            // Clear form
            $_POST = array();
        } else {
            $error = "Error adding post: " . mysqli_error($conn);
        }
    }
}

// Get categories
$categories_query = "SELECT * FROM categories";
$categories_result = mysqli_query($conn, $categories_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post - The Fahad's Space</title>
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
        
        .post-preview-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        
        .image-preview-box {
            position: relative;
        }
        
        .preview-placeholder {
            width: 100%;
            height: 300px;
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: var(--light-color);
            color: var(--gray-color);
            transition: var(--transition);
        }
        
        .preview-placeholder i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .preview-placeholder span {
            font-size: 16px;
            font-weight: 500;
        }
        
        .preview-placeholder.has-image {
            border: none;
            background-color: transparent;
            padding: 0;
            overflow: hidden;
        }
        
        .preview-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            border-radius: 8px;
        }
        
        .post-details-box {
            display: flex;
            flex-direction: column;
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
        
        .form-control textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group select {
            cursor: pointer;
        }
        
        .status-toggle {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .status-option {
            flex: 1;
            padding: 10px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .status-option.active {
            border-color: var(--secondary-color);
            background-color: var(--secondary-color);
            color: white;
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
            
            .post-preview-container {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 20px;
            }
            
            .preview-placeholder {
                height: 200px;
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
                <li><a href="add_post_new.php" class="active"><i class="fas fa-plus-circle"></i> Add Post</a></li>
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
                <div>
                    <h1><i class="fas fa-plus-circle"></i> Add New Post</h1>
                    <p class="text-muted">Create a new blog post</p>
                </div>
                <div>
                    <a href="manage_posts_new.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> View All Posts
                    </a>
                </div>
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
            
            <form method="POST" enctype="multipart/form-data">
                <div class="post-preview-container">
                    <div class="image-preview-box">
                        <div class="preview-placeholder" id="imagePreview">
                            <i class="fas fa-image"></i>
                            <span>Image Preview</span>
                        </div>
                    </div>
                    <div class="post-details-box">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="excerpt">Excerpt (Short Description)</label>
                            <textarea id="excerpt" name="excerpt" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="featured_image">Featured Image</label>
                            <input type="file" id="featured_image" name="featured_image" class="form-control" accept="image/*">
                            <small class="text-muted">JPG, PNG, GIF (Max 5MB)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Content</label>
                            <textarea id="content" name="content" class="form-control" rows="8" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Status</label>
                            <div class="status-toggle">
                                <div class="status-option" data-status="draft">
                                    <i class="fas fa-edit"></i> Draft
                                </div>
                                <div class="status-option active" data-status="published">
                                    <i class="fas fa-check-circle"></i> Published
                                </div>
                            </div>
                            <input type="hidden" id="status" name="status" value="published">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Post
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
    
    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('content', {
            height: 400,
            font_names: 'Noto Nastaliq Urdu/Noto Nastaliq Urdu; Arial/Arial, Helvetica, sans-serif; Times New Roman/Times New Roman, Times, serif; Verdana/Verdana, Geneva, sans-serif;',
            contentsCss: [
                'https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap',
                '../css/style.css'
            ]
        });

        // Image preview
        document.getElementById('featured_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    preview.classList.add('has-image');
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<i class="fas fa-image"></i><span>Image Preview</span>';
                preview.classList.remove('has-image');
            }
        });
        
        // Status toggle
        document.querySelectorAll('.status-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.status-option').forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('status').value = this.dataset.status;
            });
        });
    </script>
</body>
</html>
