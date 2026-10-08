<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $user_id);

$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_prefs'])) {
    $email_notif = isset($_POST['email_notif']) ? 1 : 0;
    $otp_notif = isset($_POST['otp_notif']) ? 1 : 0;
    $deposit_notif = isset($_POST['deposit_notif']) ? 1 : 0;
    $order_notif = isset($_POST['order_notif']) ? 1 : 0;
    $language = sanitize($_POST['language'] ?? 'en');
    $_SESSION['prefs'] = compact('email_notif','otp_notif','deposit_notif','order_notif','language');
    $msg = "Settings saved successfully!";
}

$prefs = $_SESSION['prefs'] ?? ['email_notif'=>1,'otp_notif'=>1,'deposit_notif'=>1,'order_notif'=>1,'language'=>'en'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Settings - <?= $theme['site_name'] ?></title>
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
      <h1 class="text-lg font-bold text-slate-900 tracking-tight">Settings</h1>
      <p class="text-[11px] text-slate-500 mt-0.5">Customize your account preferences and notifications.</p>
    </div>

    <?php if($msg): ?>
      <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if($error): ?>
      <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">

      <!-- Notification Prefs -->
      <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-8 h-8 rounded-lg bg-[#6366f1] flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
          <h3 class="text-sm font-bold text-slate-800">Notification Preferences</h3>
        </div>

        <div class="space-y-2">
          <?php
          $notifs = [
            ['email_notif', 'Email Notifications', 'Receive email updates for important account activity', 'indigo'],
            ['otp_notif', 'OTP Notifications', 'Get notified when new OTP messages arrive', 'emerald'],
            ['deposit_notif', 'Deposit Notifications', 'Alerts when deposits are approved or rejected', 'pink'],
            ['order_notif', 'Order Notifications', 'Order status changes and refund updates', 'amber']
          ];
          foreach($notifs as $n):
            $checked = !empty($prefs[$n[0]]);
          ?>
          <label class="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:border-<?= $n[3] ?>-300 hover:bg-<?= $n[3] ?>-50/30 transition-all cursor-pointer">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-<?= $n[3] ?>-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-<?= $n[3] ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-width="2"></path></svg>
              </div>
              <div>
                <p class="text-[12px] font-bold text-slate-800"><?= $n[1] ?></p>
                <p class="text-[10px] text-slate-500"><?= $n[2] ?></p>
              </div>
            </div>
            <input type="checkbox" name="<?= $n[0] ?>" <?= $checked ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-<?= $n[3] ?>-600 focus:ring-<?= $n[3] ?>-500 cursor-pointer">
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Preferences -->
      <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-8 h-8 rounded-lg bg-[#8b5cf6] flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
          <h3 class="text-sm font-bold text-slate-800">Regional Preferences</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Language</label>
            <select name="language" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500">
              <option value="en" <?= $prefs['language']=='en'?'selected':'' ?>>English</option>
              <option value="ur" <?= $prefs['language']=='ur'?'selected':'' ?>>Urdu</option>
              <option value="ar" <?= $prefs['language']=='ar'?'selected':'' ?>>Arabic</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Currency</label>
            <input type="text" value="PKR" disabled class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-slate-50 text-slate-500">
          </div>
        </div>
      </div>

      <!-- Save -->
      <button type="submit" name="save_prefs" class="w-full sm:w-auto bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white font-bold text-xs px-6 py-3 rounded-lg shadow-lg shadow-indigo-500/30 hover:shadow-xl transition-all">
        Save Settings
      </button>

    </form>
  </main>
</div>

</body>
</html>