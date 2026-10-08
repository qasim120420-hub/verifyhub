<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_admin_pass'])) {
    $cur = $_POST['current_password'];
    $new = $_POST['new_password'];
    $conf = $_POST['confirm_password'];
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $hash = $stmt->fetchColumn();
    if (!password_verify($cur, $hash)) $msg = "Current password is incorrect.";
    elseif ($new !== $conf) $msg = "New passwords do not match.";
    elseif (strlen($new) < 8) $msg = "Password must be at least 8 characters.";
    else {
        $newHash = password_hash($new, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHash, $_SESSION['user_id']]);
        $msg = "Admin password changed successfully!";
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM login_logs WHERE user_id = ? ORDER BY logged_at DESC LIMIT 10");
    $stmt->execute([$_SESSION['user_id']]);
    $logs = $stmt->fetchAll();
} catch (Exception $e) { $logs = []; }

$total_logs = $pdo->query("SELECT COUNT(*) FROM login_logs")->fetchColumn();
$today_logs = $pdo->query("SELECT COUNT(*) FROM login_logs WHERE DATE(logged_at)=CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Security - <?= $theme['site_name'] ?></title>
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
    <div class="max-w-5xl mx-auto space-y-4">

      <div>
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Security</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Manage admin password and monitor login activity.</p>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <div class="grid grid-cols-3 gap-3">
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Password</p>
              <h3 class="text-sm font-bold text-white mt-1">Secured</h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" stroke-width="2"></rect><path d="M7 11V7a5 5 0 0110 0v4" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Logins</p>
              <h3 class="text-xl font-bold text-white mt-1"><?= number_format($total_logs) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
        <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Today</p>
              <h3 class="text-xl font-bold text-white mt-1"><?= number_format($today_logs) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"></circle><path d="M12 6v6l4 2" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- Change Password -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#ec4899] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" stroke-width="2"></rect><path d="M7 11V7a5 5 0 0110 0v4" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Change Admin Password</h3>
          </div>
          <form method="POST" class="space-y-3">
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Current Password</label>
              <input type="password" name="current_password" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">New Password</label>
              <input type="password" name="new_password" required minlength="8" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Confirm Password</label>
              <input type="password" name="confirm_password" required minlength="8" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500">
            </div>
            <button type="submit" name="change_admin_pass" class="bg-gradient-to-r from-[#ec4899] to-[#f472b6] text-white font-bold text-xs px-5 py-2.5 rounded-lg shadow-md shadow-pink-500/30">
              Update Password
            </button>
          </form>
        </div>

        <!-- Login History -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#8b5cf6] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"></circle><path d="M12 6v6l4 2" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Recent Admin Logins</h3>
          </div>
          <?php if(empty($logs)): ?>
            <p class="text-center py-6 text-[11px] text-slate-400">No login history available</p>
          <?php else: ?>
            <div class="space-y-2 max-h-[400px] overflow-y-auto pr-1">
              <?php foreach($logs as $l): ?>
              <div class="flex items-center gap-2.5 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#8b5cf6] to-[#6366f1] flex items-center justify-center flex-shrink-0">
                  <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-width="2"></path></svg>
                </div>
                <div class="flex-1">
                  <p class="text-[11px] font-bold text-slate-800"><?= htmlspecialchars($l['ip_address'] ?? 'Unknown IP') ?></p>
                  <p class="text-[10px] text-slate-500"><?= date('M j, Y h:i A', strtotime($l['logged_at'])) ?></p>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </main>
</div>

</body>
</html>