<?php
session_start();

if(isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard_new.php');
    exit();
}

include '../includes/config.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Check database for admin
    $admin_query = "SELECT * FROM admins WHERE username = '$username' LIMIT 1";
    $admin_result = mysqli_query($conn, $admin_query);
    
    if($admin_result && mysqli_num_rows($admin_result) > 0) {
        $admin = mysqli_fetch_assoc($admin_result);
        
        // Verify password
        if(password_verify($password, $admin['password']) || $password === $admin['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header('Location: dashboard_new.php');
            exit();
        } else {
            $error = 'Invalid password!';
        }
    } else {
        // Create admin if none exists
        if($username === 'admin' && $password === 'admin123') {
            $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO admins (username, password, email, full_name) VALUES ('admin', '$hashed_password', 'admin@example.com', 'Administrator')");
            
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header('Location: dashboard_new.php');
            exit();
        } else {
            $error = 'Invalid username or password!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - The Fahad's Space</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        
        .login-box {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 28px;
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
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
            color: white;
            font-size: 32px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .icon-input {
            position: relative;
        }
        
        .icon-input i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .icon-input .form-control {
            padding-left: 45px;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 480px) {
            body {
                padding: 15px 10px;
                align-items: center;
                justify-content: center;
                padding-top: 40px;
            }
            
            .login-container {
                max-width: 320px;
                padding: 0;
                margin: 0;
                width: 100%;
            }
            
            .login-box {
                padding: 35px 25px;
                border-radius: 15px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                width: 100%;
            }
            
            .login-header {
                margin-bottom: 25px;
                text-align: center;
            }
            
            .login-header h1 {
                font-size: 26px;
                margin-bottom: 8px;
            }
            
            .login-header p {
                font-size: 14px;
                line-height: 1.4;
                color: #666;
            }
            
            .logo {
                font-size: 28px;
                margin-bottom: 25px;
                text-align: center;
                width: 100%;
                display: block;
            }
            
            .form-group {
                margin-bottom: 18px;
            }
            
            .form-control {
                padding: 14px 15px;
                font-size: 16px;
                border-radius: 10px;
                border: 2px solid #e1e8ed;
            }
            
            .icon-input .form-control {
                padding-left: 45px;
            }
            
            .icon-input i {
                left: 15px;
                font-size: 15px;
                color: #888;
            }
            
            .login-btn {
                padding: 14px;
                font-size: 15px;
                font-weight: 600;
                border-radius: 10px;
                margin-top: 10px;
                letter-spacing: 0.5px;
            }
            
            .form-group label {
                font-size: 14px;
                margin-bottom: 8px;
                font-weight: 600;
                color: #333;
            }
            
            .error-message {
                font-size: 14px;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 18px;
            }
        }
        
        @media (max-width: 360px) {
            body {
                padding: 10px 5px;
                padding-top: 30px;
                align-items: center;
                justify-content: center;
            }
            
            .login-container {
                max-width: 300px;
                padding: 0;
                margin: 0;
                width: 100%;
            }
            
            .login-box {
                padding: 30px 20px;
                border-radius: 12px;
                width: 100%;
            }
            
            .login-header {
                text-align: center;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .logo {
                font-size: 24px;
                margin-bottom: 20px;
                text-align: center;
                width: 100%;
                display: block;
            }
            
            .form-control {
                padding: 12px 15px;
                border-radius: 8px;
            }
            
            .icon-input .form-control {
                padding-left: 40px;
            }
            
            .icon-input i {
                left: 12px;
                font-size: 14px;
            }
            
            .login-btn {
                padding: 12px;
                font-size: 14px;
                border-radius: 8px;
            }
            
            .form-group {
                margin-bottom: 16px;
            }
        }
        
        /* Extra small devices */
        @media (max-width: 320px) {
            body {
                padding: 10px 5px;
                padding-top: 30px;
                align-items: center;
                justify-content: center;
            }
            
            .login-container {
                max-width: 280px;
                padding: 0;
                margin: 0;
                width: 100%;
            }
            
            .login-box {
                padding: 25px 15px;
                width: 100%;
            }
            
            .login-header {
                text-align: center;
            }
            
            .login-header h1 {
                font-size: 22px;
            }
            
            .logo {
                font-size: 22px;
                margin-bottom: 20px;
                text-align: center;
                width: 100%;
                display: block;
            }
            
            .form-control {
                padding: 10px 12px;
            }
            
            .icon-input .form-control {
                padding-left: 35px;
            }
            
            .icon-input i {
                left: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="logo">
        <i class="fas fa-rocket"></i> The Fahad's Space
    </div>
    
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1><i class="fas fa-lock"></i> Admin Login</h1>
                <p>Enter your credentials to access the dashboard</p>
            </div>
            
            <?php if($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="icon-input">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" class="form-control" required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="icon-input">
                        <i class="fas fa-key"></i>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                </button>
            </form>
            
                    </div>
    </div>
</body>
</html>
