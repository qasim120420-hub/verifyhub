<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'otp_length' => (int)($_POST['otp_length'] ?? 6),
        'otp_expiry_minutes' => (int)($_POST['otp_expiry_minutes'] ?? 5),
        'otp_max_attempts' => (int)($_POST['otp_max_attempts'] ?? 3),
        'otp_resend_seconds' => (int)($_POST['otp_resend_seconds'] ?? 60),
        'otp_auto_approve' => isset($_POST['otp_auto_approve']) ? 1 : 0,
        'otp_sms_enabled' => isset($_POST['otp_sms_enabled']) ? 1 : 0,
        'otp_email_enabled' => isset($_POST['otp_email_enabled']) ? 1 : 0,
    ];
    foreach ($settings as $k => $v) {
        $stmt = $pdo->prepare("INSERT INTO theme_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$k, $v, $v]);
    }
    $msg = "OTP settings saved!";
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
<title>OTP Settings - <?= $theme['site_name'] ?></title>
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

      <div>
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">OTP Settings</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Configure OTP behavior and delivery preferences.</p>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <div class="grid grid-cols-3 gap-3">
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">OTP Length</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= $theme['otp_length'] ?? 6 ?> digits</h3>
        </div>
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Expiry</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= $theme['otp_expiry_minutes'] ?? 5 ?> min</h3>
        </div>
        <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Max Attempts</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= $theme['otp_max_attempts'] ?? 3 ?></h3>
        </div>
      </div>

      <form method="POST" class="space-y-4">

        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#6366f1] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" stroke-width="2"></rect><path d="M7 11V7a5 5 0 0110 0v4" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">OTP Configuration</h3>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">OTP Length (digits)</label>
              <input type="number" name="otp_length" min="4" max="8" value="<?= $theme['otp_length'] ?? 6 ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">OTP Expiry (minutes)</label>
              <input type="number" name="otp_expiry_minutes" min="1" max="60" value="<?= $theme['otp_expiry_minutes'] ?? 5 ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Max Verification Attempts</label>
              <input type="number" name="otp_max_attempts" min="1" max="10" value="<?= $theme['otp_max_attempts'] ?? 3 ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Resend Cooldown (seconds)</label>
              <input type="number" name="otp_resend_seconds" min="30" max="300" value="<?= $theme['otp_resend_seconds'] ?? 60 ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
          </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#10b981] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Delivery Options</h3>
          </div>
          <div class="space-y-2">
            <label class="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:border-emerald-300 cursor-pointer">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke-linecap="round" stroke-width="2"></path><polyline points="22,6 12,13 2,6" stroke-linecap="round" stroke-width="2"></polyline></svg>
                </div>
                <div>
                  <p class="text-[12px] font-bold text-slate-800">Email Delivery</p>
                  <p class="text-[10px] text-slate-500">Send OTPs via email</p>
                </div>
              </div>
              <input type="checkbox" name="otp_email_enabled" <?= !empty($theme['otp_email_enabled']) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            </label>
            <label class="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:border-emerald-300 cursor-pointer">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" stroke-width="2"></rect><path d="M12 18h.01" stroke-linecap="round" stroke-width="2"></path></svg>
                </div>
                <div>
                  <p class="text-[12px] font-bold text-slate-800">SMS Delivery</p>
                  <p class="text-[10px] text-slate-500">Send OTPs via SMS gateway</p>
                </div>
              </div>
              <input type="checkbox" name="otp_sms_enabled" <?= !empty($theme['otp_sms_enabled']) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            </label>
            <label class="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:border-emerald-300 cursor-pointer">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-width="2.5"></path></svg>
                </div>
                <div>
                  <p class="text-[12px] font-bold text-slate-800">Auto-Approve Orders</p>
                  <p class="text-[10px] text-slate-500">Auto-complete when OTP received</p>
                </div>
              </div>
              <input type="checkbox" name="otp_auto_approve" <?= !empty($theme['otp_auto_approve']) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            </label>
          </div>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white font-bold text-xs py-3 rounded-lg shadow-lg shadow-indigo-500/30 hover:shadow-xl transition-all">
          Save OTP Settings
        </button>

      </form>
    </div>
  </main>
</div>

</body>
</html>