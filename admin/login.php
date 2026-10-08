<?php
require_once '../config/session.php';

if (isAdmin()) redirect('dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Please enter email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND role = 'admin'");
        $stmt->execute([$email, $email]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            $error = "Invalid admin credentials.";
        } elseif ($admin['status'] !== 'active') {
            $error = "Your admin account is not active.";
        } elseif (!password_verify($password, $admin['password'])) {
            $error = "Invalid admin credentials.";
        } else {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['role'] = 'admin';
            
            try {
                $pdo->prepare("INSERT INTO login_logs (user_id, ip_address) VALUES (?, ?)")
                    ->execute([$admin['id'], $_SERVER['REMOTE_ADDR']]);
            } catch (Exception $e) {}
            
            redirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — <?= $theme['site_name'] ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="auth-page">
    <!-- LEFT: Illustration -->
    <div class="auth-illustration" style="background:linear-gradient(135deg,#1E1B4B 0%,#4C1D95 50%,#7C3AED 100%);">
        <div class="auth-ill-content">
            <div class="logo-wrap" style="margin-bottom:24px;">
                <div class="logo-icon" style="background:rgba(255,255,255,0.2);box-shadow:none;">
                    <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 2L4 6v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V6l-8-4z"/><path d="M9 12l2 2 4-4"/></svg>
                </div>
                <div class="logo-text">
                    <h2 style="color:#fff;"><?= $theme['site_name'] ?></h2>
                    <p style="color:rgba(255,255,255,0.7);">Admin Portal</p>
                </div>
            </div>

            <h2>Admin Control Center</h2>
            <p>Manage users, approve deposits, control number inventory, and monitor real-time system activity — all from one powerful dashboard.</p>

            <ul class="ill-features">
                <li>
                    <span class="if-icon"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></span>
                    User management
                </li>
                <li>
                    <span class="if-icon"><svg class="icon icon-sm" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span>
                    Deposit approvals
                </li>
                <li>
                    <span class="if-icon"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/></svg></span>
                    Real-time reports
                </li>
                <li>
                    <span class="if-icon"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg></span>
                    Full audit logs
                </li>
            </ul>
        </div>
    </div>

    <!-- RIGHT: Form -->
    <div class="auth-form-side">
        <div class="auth-box">
            <div style="width:60px;height:60px;background:linear-gradient(135deg,#4C1D95,#A855F7);border-radius:16px;display:flex;align-items:center;justify-content:center;color:#fff;margin-bottom:20px;">
                <svg class="icon icon-lg" viewBox="0 0 24 24" style="width:30px;height:30px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>

            <h2>Admin Portal</h2>
            <p class="sub">Authorized personnel only. Please sign in.</p>

            <?php if($error): ?>
                <div class="alert alert-error">
                    <svg class="icon icon-sm" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email or Username</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                        <input type="text" name="email" required autofocus placeholder="admin@example.com">
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon"><svg class="icon icon-sm" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></span>
                        <input type="password" name="password" required placeholder="Enter admin password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="padding:13px;font-size:14.5px;">
                    Access Admin Panel
                </button>
            </form>

            <p style="text-align:center;margin-top:24px;font-size:12px;color:var(--text-muted-dark);">
                <svg class="icon icon-sm" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Protected Area — Login attempts are logged
            </p>
        </div>
    </div>
</div>

</body>
</html>