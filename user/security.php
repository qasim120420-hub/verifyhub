<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $user_id);

$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $cur = $_POST['current_password']; $new = $_POST['new_password']; $conf = $_POST['confirm_password'];
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $hash = $stmt->fetchColumn();
    if (!password_verify($cur, $hash)) $error = "Current password is incorrect.";
    elseif ($new !== $conf) $error = "New passwords do not match.";
    elseif (strlen($new) < 8) $error = "Password must be at least 8 characters.";
    else {
        $newHash = password_hash($new, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHash, $user_id]);
        try { addNotification($pdo, $user_id, "Password Changed", "Your password was updated."); } catch (Exception $e) {}
        $msg = "Password changed successfully!";
    }
}

if (isset($_GET['logout_all'])) {
    try {
        $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?")->execute([$user_id]);
    } catch (Exception $e) {}
    session_destroy();
    redirect('../public/login.php');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM login_logs WHERE user_id = ? ORDER BY logged_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $logs = $stmt->fetchAll();
} catch (Exception $e) {
    $logs = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Security - <?= $theme['site_name'] ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"','sans-serif'] } } } }</script>
<style>
  ::-webkit-scrollbar { width: 5px; height: 5px; }
  ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
  .sidebar-scroll::-webkit-scrollbar-thumb { background: #2d3056; }
</style>
</head>
<body class="bg-[#f4f6fc] text-slate-800 antialiased font-sans flex h-screen overflow-hidden text-sm">
<?php include '../includes/user_sidebar.php'; ?>

<div class="flex-1 flex flex-col h-screen overflow-hidden bg-[#f5f7fc]">
  <header class="h-14 bg-white border-b border-slate-200/80 px-5 flex items-center justify-between flex-shrink-0">
    <div class="flex items-center gap-3 w-80">
      <button class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-width="2"></path></svg></button>
      <div class="relative w-full">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-width="2"></path></svg></span>
        <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full" placeholder="Search..." type="text"/>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <div class="flex items-center bg-[#f1f5f9] border border-slate-200 rounded-full px-3 py-1 gap-2 text-xs font-semibold text-slate-700">
        <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-600 flex items-center justify-center text-[10px]">₨</span>
        <span>PKR <?= number_format($balance, 2) ?></span>
      </div>
      <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
        <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-[11px]"><?= $user_initial ?></div>
        <span class="text-xs font-semibold"><?= htmlspecialchars($user_full_name) ?></span>
      </div>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-5 space-y-4">
    <div>
      <h1 class="text-lg font-bold text-slate-900 tracking-tight">Security</h1>
      <p class="text-[11px] text-slate-500 mt-0.5">Manage your password and account security.</p>
    </div>

    <?php if($msg): ?>
      <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if($error): ?>
      <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Security Status Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Password</p>
            <h3 class="text-sm font-bold text-white mt-1">Strong</h3>
          </div>
          <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
        </div>
      </div>
      <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Account</p>
            <h3 class="text-sm font-bold text-white mt-1">Active</h3>
          </div>
          <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
        </div>
      </div>
      <div class="bg-[#8b5cf6] p-4 rounded-xl shadow-lg shadow-violet-500/30">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">2FA</p>
            <h3 class="text-sm font-bold text-white mt-1">Optional</h3>
          </div>
          <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
        </div>
      </div>
      <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Sessions</p>
            <h3 class="text-sm font-bold text-white mt-1">1 Active</h3>
          </div>
          <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2" stroke-width="2"></rect><line x1="8" y1="21" x2="16" y2="21" stroke-linecap="round" stroke-width="2"></line><line x1="12" y1="17" x2="12" y2="21" stroke-linecap="round" stroke-width="2"></line></svg>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

      <!-- Change Password -->
      <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-8 h-8 rounded-lg bg-[#ec4899] flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
          <h3 class="text-sm font-bold text-slate-800">Change Password</h3>
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
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Confirm New Password</label>
            <input type="password" name="confirm_password" required minlength="8" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500">
          </div>
          <button type="submit" name="change_password" class="bg-gradient-to-r from-[#ec4899] to-[#f472b6] text-white font-bold text-xs px-5 py-2.5 rounded-lg shadow-md shadow-pink-500/30 hover:shadow-lg transition-all">
            Update Password
          </button>
        </form>
      </div>

      <!-- Session & Danger Zone -->
      <div class="space-y-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#6366f1] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2" stroke-width="2"></rect><line x1="8" y1="21" x2="16" y2="21" stroke-linecap="round" stroke-width="2"></line></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Active Sessions</h3>
          </div>
          <div class="space-y-2">
            <div class="flex items-center justify-between p-3 rounded-lg bg-emerald-50 border border-emerald-200">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-width="2"></path></svg>
                </div>
                <div>
                  <p class="text-[11px] font-bold text-slate-800">Current Session</p>
                  <p class="text-[10px] text-slate-500">This device · <?= date('M j, Y h:i A') ?></p>
                </div>
              </div>
              <span class="text-[9px] font-bold bg-emerald-500 text-white px-2 py-0.5 rounded-full uppercase">Active</span>
            </div>
          </div>
          <a href="?logout_all=1" onclick="return confirm('Logout from all devices?')" class="mt-3 inline-flex items-center gap-1.5 bg-gradient-to-r from-[#f59e0b] to-[#fbbf24] text-white font-bold text-xs px-4 py-2 rounded-lg shadow-md shadow-amber-500/30">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke-linecap="round" stroke-width="2.5"></path></svg>
            Logout All Devices
          </a>
        </div>

        <!-- Login History -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#8b5cf6] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"></circle><path d="M12 6v6l4 2" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Recent Login Activity</h3>
          </div>
          <?php if(empty($logs)): ?>
            <div class="text-center py-4">
              <p class="text-[11px] text-slate-400">No login history available</p>
            </div>
          <?php else: ?>
            <div class="space-y-2">
              <?php foreach($logs as $l): ?>
              <div class="flex items-center gap-2.5 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#8b5cf6] to-[#6366f1] flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-width="2"></path></svg>
                </div>
                <div class="flex-1">
                  <p class="text-[11px] font-semibold text-slate-800"><?= htmlspecialchars($l['ip_address'] ?? 'Unknown IP') ?></p>
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