<?php

session_start();

require_once __DIR__ . '/../lib/db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #1a1a2e;
            color: #eee;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #16213e;
            border: 1px solid #f4c542;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
        }
        .login-title {
            color: #f4c542;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-control {
            background: #0f3460;
            border: 1px solid #2a2a4a;
            color: #eee;
        }
        .form-control:focus {
            background: #0f3460;
            border-color: #f4c542;
            color: #eee;
            box-shadow: none;
        }
        .btn-gold {
            background: #f4c542;
            color: #1a1a2e;
            border: none;
            font-weight: bold;
            width: 100%;
        }
        .btn-gold:hover {
            background: #d4a532;
            color: #1a1a2e;
        }
        .alert {
            background: rgba(248, 113, 113, 0.2);
            border: 1px solid rgba(248, 113, 113, 0.5);
            color: #f87171;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1 class="login-title">
            <i class="bi bi-shield-lock me-2"></i>Admin Login
        </h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger mb-3"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-gold btn-lg">Login</button>
        </form>
        
        <div class="text-center mt-4 text-muted small">
            <p>Default: admin / morningstarhuamak</p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</body>
</html>
