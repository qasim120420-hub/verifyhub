<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
      'site_url' => sanitize($_POST['site_url'] ?? ''),
      'support_email' => sanitize($_POST['support_email'] ?? ''),
      'support_phone' => sanitize($_POST['support_phone'] ?? ''),
      'currency' => sanitize($_POST['currency'] ?? 'PKR'),
      'min_deposit' => (float)($_POST['min_deposit'] ?? 100),
      'max_deposit' => (float)($_POST['max_deposit'] ?? 100000),
      'min_transfer' => (float)($_POST['min_transfer'] ?? 1),
      'max_transfer' => (float)($_POST['max_transfer'] ?? 50000),
      'order_expiry' => (int)($_POST['order_expiry'] ?? 20),
      'otp_expiry' => (int)($_POST['otp_expiry'] ?? 300),
      'max_login_attempts' => (int)($_POST['max_login_attempts'] ?? 5),
      'registration_enabled' => isset($_POST['registration_enabled']) ? 1 : 0,
    ];
    foreach ($settings as $k => $v) {
        $stmt = $pdo->prepare("INSERT INTO theme_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$k, $v, $v]);
    }
    $msg = "System settings saved!";
    $theme = [];
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM theme_settings");
    while($row = $stmt->fetch()) $theme[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>System Settings - <?= $theme['site_name'] ?></title>
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
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">System Settings</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Configure platform-wide settings and limits.</p>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">

        <!-- General -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#6366f1] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" stroke-width="2"></circle><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">General Settings</h3>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Site URL</label>
              <input type="text" name="site_url" value="<?= htmlspecialchars($theme['site_url'] ?? '') ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="https://example.com">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Currency</label>
              <select name="currency" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="PKR" <?= ($theme['currency'] ?? 'PKR')=='PKR'?'selected':'' ?>>PKR — Pakistani Rupee</option>
                <option value="USD" <?= ($theme['currency'] ?? '')=='USD'?'selected':'' ?>>USD — US Dollar</option>
                <option value="INR" <?= ($theme['currency'] ?? '')=='INR'?'selected':'' ?>>INR — Indian Rupee</option>
                <option value="AED" <?= ($theme['currency'] ?? '')=='AED'?'selected':'' ?>>AED — UAE Dirham</option>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Support Email</label>
              <input type="email" name="support_email" value="<?= htmlspecialchars($theme['support_email'] ?? '') ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Support Phone</label>
              <input type="text" name="support_phone" value="<?= htmlspecialchars($theme['support_phone'] ?? '') ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
          </div>
        </div>

        <!-- Wallet Limits -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#10b981] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Wallet Limits</h3>
          </div>
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Min Deposit</label>
              <input type="number" name="min_deposit" value="<?= htmlspecialchars($theme['min_deposit'] ?? 100) ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Max Deposit</label>
              <input type="number" name="max_deposit" value="<?= htmlspecialchars($theme['max_deposit'] ?? 100000) ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Min Transfer</label>
              <input type="number" name="min_transfer" value="<?= htmlspecialchars($theme['min_transfer'] ?? 1) ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Max Transfer</label>
              <input type="number" name="max_transfer" value="<?= htmlspecialchars($theme['max_transfer'] ?? 50000) ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
          </div>
        </div>

        <!-- Orders & OTP -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#f59e0b] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"></circle><path d="M12 6v6l4 2" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Orders & OTP</h3>
          </div>
          <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Order Expiry (minutes)</label>
              <input type="number" name="order_expiry" value="<?= htmlspecialchars($theme['order_expiry'] ?? 20) ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">OTP Expiry (seconds)</label>
              <input type="number" name="otp_expiry" value="<?= htmlspecialchars($theme['otp_expiry'] ?? 300) ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Max Login Attempts</label>
              <input type="number" name="max_login_attempts" value="<?= htmlspecialchars($theme['max_login_attempts'] ?? 5) ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>
          </div>
        </div>

        <!-- Registration -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#8b5cf6] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Registration Settings</h3>
          </div>
          <label class="flex items-center gap-2.5 cursor-pointer">
            <input type="checkbox" name="registration_enabled" <?= !empty($theme['registration_enabled']) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
            <span class="text-[12px] font-semibold text-slate-700">Allow new user registrations</span>
          </label>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white font-bold text-xs py-3 rounded-lg shadow-lg shadow-indigo-500/30 hover:shadow-xl transition-all">
          Save System Settings
        </button>

      </form>
    </div>
  </main>
</div>

</body>
</html>