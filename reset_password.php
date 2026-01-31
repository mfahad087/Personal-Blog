<?php
// PASSWORD RESET TOOL - Force reset to fix authentication issues
session_start();

// Only allow access if not logged in (to prevent abuse)
if(isset($_SESSION['admin_logged_in'])) {
    session_destroy();
}

include '../includes/config.php';

$message = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if($action === 'reset') {
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);
        
        if(empty($new_password)) {
            $error = "Password cannot be empty";
        } elseif($new_password !== $confirm_password) {
            $error = "Passwords do not match";
        } elseif(strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters";
        } else {
            // Force reset admin password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update or insert admin
            $check_admin = "SELECT id FROM admins WHERE username = 'admin' LIMIT 1";
            $result = mysqli_query($conn, $check_admin);
            
            if(mysqli_num_rows($result) > 0) {
                $admin = mysqli_fetch_assoc($result);
                $update_query = "UPDATE admins SET password = '$hashed_password' WHERE id = " . $admin['id'];
            } else {
                $update_query = "INSERT INTO admins (username, password, email, full_name) VALUES ('admin', '$hashed_password', 'admin@example.com', 'Administrator')";
            }
            
            if(mysqli_query($conn, $update_query)) {
                $message = "✅ Password reset successfully! You can now login with the new password.";
                
                // Clear any existing sessions
                session_destroy();
            } else {
                $error = "❌ Error resetting password: " . mysqli_error($conn);
            }
        }
    }
}

// Check current admin status
$admin_exists = false;
$password_type = 'Unknown';

$check_admin = "SELECT * FROM admins WHERE username = 'admin' LIMIT 1";
$result = mysqli_query($conn, $check_admin);
if(mysqli_num_rows($result) > 0) {
    $admin_exists = true;
    $admin = mysqli_fetch_assoc($result);
    
    if(strpos($admin['password'], '$') === 0) {
        $password_type = 'Hashed (Secure)';
    } else {
        $password_type = 'Plain Text (Insecure)';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password - The Fahad's Space</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .reset-container {
            width: 100%;
            max-width: 500px;
            padding: 20px;
        }
        
        .reset-box {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .reset-header h1 {
            color: #e74c3c;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .status-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .status-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .status-label {
            font-weight: 600;
            color: #495057;
        }
        
        .status-value {
            color: #28a745;
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            margin-top: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .warning-box h4 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .warning-box p {
            color: #856404;
            font-size: 14px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-box">
            <div class="reset-header">
                <h1><i class="fas fa-key"></i> Reset Admin Password</h1>
                <p>Force reset to fix authentication issues</p>
            </div>
            
            <?php if($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="status-card">
                <h3 style="margin-bottom: 15px; color: #495057;">Current Status</h3>
                <div class="status-item">
                    <span class="status-label">Admin Exists:</span>
                    <span class="status-value"><?php echo $admin_exists ? '✅ Yes' : '❌ No'; ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Password Type:</span>
                    <span class="status-value"><?php echo $password_type; ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Authentication:</span>
                    <span class="status-value">Database Only</span>
                </div>
            </div>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> IMPORTANT</h4>
                <p>• This will completely reset the admin password</p>
                <p>• After reset, ONLY the new password will work</p>
                <p>• Old passwords will be completely disabled</p>
                <p>• This fixes any authentication issues</p>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="reset">
                
                <div class="form-group">
                    <label for="new_password">New Password (min 6 characters):</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                </div>
                
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-sync-alt"></i> RESET PASSWORD NOW
                </button>
            </form>
            
            <a href="login_new.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</body>
</html>
