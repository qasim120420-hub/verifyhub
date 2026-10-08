<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $user_id);

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]); $user = $stmt->fetch();

$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fn = sanitize($_POST['full_name']);
    $ph = sanitize($_POST['phone']);
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
    if ($stmt->execute([$fn, $ph, $user_id])) {
        $_SESSION['full_name'] = $fn;
        $msg = "Profile updated successfully!";
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]); $user = $stmt->fetch();
    } else $error = "Update failed.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $cur = $_POST['current_password']; $new = $_POST['new_password']; $conf = $_POST['confirm_password'];
    if (!password_verify($cur, $user['password'])) $error = "Current password is incorrect.";
    elseif ($new !== $conf) $error = "New passwords do not match.";
    elseif (strlen($new) < 8) $error = "Password must be at least 8 characters.";
    else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hash, $user_id])) {
            try { addNotification($pdo, $user_id, "Password Changed", "Your password was changed."); } catch (Exception $e) {}
            $msg = "Password changed successfully!";
        } else $error = "Password change failed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>My Profile - <?= $theme['site_name'] ?></title>
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
      <h1 class="text-lg font-bold text-slate-900 tracking-tight">My Profile</h1>
      <p class="text-[11px] text-slate-500 mt-0.5">Manage your personal information and password.</p>
    </div>

    <?php if($msg): ?>
      <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if($error): ?>
      <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

      <!-- Profile Card -->
      <div class="bg-gradient-to-br from-[#6366f1] to-[#8b5cf6] rounded-xl p-5 text-white shadow-lg shadow-indigo-500/30">
        <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white font-bold text-3xl mx-auto mb-3 ring-4 ring-white/30">
          <?= $user_initial ?>
        </div>
        <h3 class="text-center font-bold text-base"><?= htmlspecialchars($user['username']) ?></h3>
        <p class="text-center text-[11px] text-white/80 mb-4"><?= htmlspecialchars($user['email']) ?></p>
        <div class="space-y-2 text-[11px]">
          <div class="flex items-center gap-2 bg-white/15 backdrop-blur-sm rounded-lg px-3 py-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round" stroke-width="2"></path></svg>
            <span>Joined: <?= date('M j, Y', strtotime($user['created_at'])) ?></span>
          </div>
          <div class="flex items-center gap-2 bg-white/15 backdrop-blur-sm rounded-lg px-3 py-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-width="2"></path></svg>
            <span class="capitalize">Status: <?= $user['status'] ?></span>
          </div>
          <div class="flex items-center gap-2 bg-white/15 backdrop-blur-sm rounded-lg px-3 py-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke-linecap="round" stroke-width="2"></path></svg>
            <span>Balance: PKR <?= number_format($balance, 2) ?></span>
          </div>
        </div>
      </div>

      <!-- Edit Profile -->
      <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-7 h-7 rounded-lg bg-[#6366f1] flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke-linecap="round" stroke-width="2"></path><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
          <h3 class="text-sm font-bold text-slate-800">Edit Profile</h3>
        </div>
        <form method="POST" class="space-y-3">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Full Name</label>
              <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Phone</label>
              <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Email (locked)</label>
            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-slate-50 text-slate-500">
          </div>
          <button type="submit" name="update_profile" class="bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white font-bold text-xs px-5 py-2.5 rounded-lg shadow-md shadow-indigo-500/30 hover:shadow-lg transition-all">Update Profile</button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-200">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-7 h-7 rounded-lg bg-[#ec4899] flex items-center justify-center">
              <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Change Password</h3>
          </div>
          <form method="POST" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Current</label>
                <input type="password" name="current_password" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500">
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">New</label>
                <input type="password" name="new_password" required minlength="8" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500">
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Confirm</label>
                <input type="password" name="confirm_password" required minlength="8" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500">
              </div>
            </div>
            <button type="submit" name="change_password" class="bg-gradient-to-r from-[#ec4899] to-[#f472b6] text-white font-bold text-xs px-5 py-2.5 rounded-lg shadow-md shadow-pink-500/30 hover:shadow-lg transition-all">Change Password</button>
          </form>
        </div>
      </div>

    </div>
  </main>
</div>

</body>
</html>