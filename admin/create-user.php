<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $role = sanitize($_POST['role']);
    $initial_balance = (float)($_POST['initial_balance'] ?? 0);

    if (empty($username) || empty($email) || empty($password)) $error = "Required fields missing.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Invalid email.";
    elseif (strlen($password) < 8) $error = "Password must be at least 8 characters.";
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) $error = "Username or Email already exists.";
        else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, full_name, email, phone, password, role) VALUES (?,?,?,?,?,?)");
            if ($stmt->execute([$username, $full_name, $email, $phone, $hash, $role])) {
                $uid = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, ?)")->execute([$uid, $initial_balance]);
                if ($initial_balance > 0) {
                    $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, previous_balance, new_balance, description, status) VALUES (?, 'admin_credit', ?, 0, ?, 'Initial balance', 'completed')")
                        ->execute([$uid, $initial_balance, $initial_balance]);
                }
                try { addNotification($pdo, $uid, "Welcome!", "Your account was created by admin."); } catch (Exception $e) {}
                $msg = "User created successfully!";
            } else $error = "Failed to create user.";
        }
    }
}

$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$total_admins = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Create User - <?= $theme['site_name'] ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] }, colors: { brand: { sidebar:'#0d1026', sidebarHover:'#171c3d', purple:'#5e48e8' } } } } }</script>
<style>
  ::-webkit-scrollbar { width: 5px; height: 5px; }
  ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
</style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans min-h-screen flex flex-col text-sm">

<header class="bg-white border-b border-slate-200 h-14 sticky top-0 z-30 flex items-center justify-between px-4 lg:px-6">
  <div class="flex items-center gap-3 w-60 flex-shrink-0">
    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-purple to-indigo-500 flex items-center justify-center text-white shadow-md">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-width="2.2"></path></svg>
    </div>
    <div class="flex items-center gap-1.5">
      <span class="text-base font-bold text-slate-900"><?= $theme['site_name'] ?></span>
      <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-brand-purple/10 text-brand-purple">Admin</span>
    </div>
  </div>
  <div class="flex items-center gap-3">
    <div class="w-7 h-7 rounded-full bg-slate-900 border border-slate-300 flex items-center justify-center text-white text-[11px] font-semibold">SA</div>
    <span class="text-xs font-semibold hidden sm:block">Super Admin</span>
  </div>
</header>

<div class="flex flex-1 overflow-hidden">
  <?php include '../includes/admin_sidebar.php'; ?>

  <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
    <div class="max-w-4xl mx-auto space-y-4">

      <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h1 class="text-lg font-bold text-slate-900 tracking-tight">Create New User</h1>
          <p class="text-[11px] text-slate-500 mt-0.5">Manually add a user or admin to the platform.</p>
        </div>
        <a href="users.php" class="bg-white border border-slate-200 text-slate-700 text-xs font-bold px-4 py-2 rounded-lg hover:bg-slate-100 transition flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-width="2.5"></path></svg>
          Back to Users
        </a>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Users</p>
              <h3 class="text-xl font-bold text-white mt-1"><?= number_format($total_users) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
        <div class="bg-[#8b5cf6] p-4 rounded-xl shadow-lg shadow-violet-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Admins</p>
              <h3 class="text-xl font-bold text-white mt-1"><?= number_format($total_admins) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>
      <?php if($error): ?>
        <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-8 h-8 rounded-lg bg-[#6366f1] flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
          <h3 class="text-sm font-bold text-slate-800">User Information</h3>
        </div>

        <form method="POST" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Username *</label>
              <input type="text" name="username" required class="w-full px-3 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="johndoe">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Full Name *</label>
              <input type="text" name="full_name" required class="w-full px-3 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="John Doe">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Email *</label>
              <input type="email" name="email" required class="w-full px-3 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="john@example.com">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Phone</label>
              <input type="text" name="phone" class="w-full px-3 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="+92 300 1234567">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Password *</label>
              <input type="password" name="password" required minlength="8" class="w-full px-3 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Min 8 characters">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Role *</label>
              <select name="role" required class="w-full px-3 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="user">User</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Initial Wallet Balance (PKR)</label>
            <input type="number" name="initial_balance" step="0.01" min="0" value="0" class="w-full px-3 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="0.00">
          </div>
          <button type="submit" class="w-full bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white font-bold text-xs py-3 rounded-lg shadow-lg shadow-indigo-500/30 hover:shadow-xl transition-all">
            Create User Account
          </button>
        </form>
      </div>

      <div class="bg-gradient-to-br from-[#f59e0b] to-[#fbbf24] p-5 rounded-xl text-white shadow-lg shadow-amber-500/30">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
          <div>
            <h4 class="text-sm font-bold mb-1">Important</h4>
            <p class="text-[11px] opacity-90 leading-relaxed">Be careful when creating <strong>admin</strong> accounts. Admin users have full access to all management, wallet, and system settings.</p>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

</body>
</html>