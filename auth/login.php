<?php
// auth/login.php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Check if blocked
            if (isset($user['is_blocked']) && $user['is_blocked'] == 1) {
                $error = "Your account has been blocked by the admin.";
            } else {
                $loginType = $_GET['type'] ?? 'user';
                
                // Enforce Strict Role Matching
                if ($loginType === 'admin' && $user['role'] !== 'admin') {
                    $error = "Access denied. You do not have administrative privileges.";
                } elseif ($loginType === 'user' && $user['role'] !== 'user') {
                    $error = "Access denied. Please explicitly use the Admin Login portal.";
                } else {
                    // Set sessions
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header("Location: ../admin/dashboard.php");
                    } else {
                        header("Location: ../user/dashboard.php");
                    }
                    exit();
                }
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Garbage Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="../index.php" class="logo">EcoManage</a>
</nav>

<div class="container" style="max-width: 450px;">
    <div class="card">
        <h2>Sign In</h2>
        <?php if($error): ?><div class="error-msg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        
<?php $loginType = $_GET['type'] ?? 'user'; ?>
        <form action="?type=<?php echo htmlspecialchars($loginType); ?>" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-block"><?php echo $loginType === 'admin' ? 'Admin Login' : 'Login'; ?></button>
        </form>
        <?php if ($loginType !== 'admin'): ?>
            <a href="register.php" class="auth-links">Don't have an account? Register here</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
