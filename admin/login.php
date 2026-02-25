<?php
require_once '../includes/config.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, email, password, first_name, last_name, role FROM users WHERE email = ? AND role = 'admin' AND is_active = 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['admin_role'] = $user['role'];
        
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?php echo SITE_NAME; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lato:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --color-bg-primary: #FFFBF7;
            --color-accent-blush: #E8B4B8;
            --color-accent-peach: #F5D0C5;
            --color-text-primary: #3D3D3D;
        }
        
        body {
            font-family: 'Lato', sans-serif;
            background: linear-gradient(135deg, var(--color-bg-primary) 0%, #f5e6e8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            padding: 2.5rem;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            color: var(--color-text-primary);
            margin-bottom: 0.25rem;
        }
        
        .login-logo span {
            color: var(--color-accent-blush);
        }
        
        .login-logo p {
            color: #888;
            font-size: 0.9rem;
        }
        
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 0.875rem 1rem;
        }
        
        .form-control:focus {
            border-color: var(--color-accent-blush);
            box-shadow: 0 0 0 0.2rem rgba(232, 180, 184, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--color-accent-blush) 0%, var(--color-accent-peach) 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #d9a0a5 0%, #e6c0b5 100%);
        }
        
        .input-group-text {
            background: transparent;
            border: 1px solid #e0e0e0;
            border-right: none;
            color: #888;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .back-link {
            color: #888;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .back-link:hover {
            color: var(--color-accent-blush);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <h1>Elegance <span>Admin</span></h1>
                <p>Dupatta Store Management</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="admin@example.com" required autofocus>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
                
                <div class="text-center">
                    <a href="<?php echo SITE_URL; ?>" class="back-link">
                        <i class="bi bi-arrow-left me-1"></i>Back to Website
                    </a>
                </div>
            </form>
        </div>
        
        <p class="text-center text-muted mt-4 small">
            &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
        </p>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
