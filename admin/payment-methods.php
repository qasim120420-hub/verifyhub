<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = '';

// Default methods
$default_methods = [
    ['key'=>'bank_transfer', 'name'=>'Bank Transfer', 'icon'=>'building', 'color'=>'indigo'],
    ['key'=>'easypaisa', 'name'=>'EasyPaisa', 'icon'=>'mobile', 'color'=>'emerald'],
    ['key'=>'jazzcash', 'name'=>'JazzCash', 'icon'=>'mobile', 'color'=>'rose'],
    ['key'=>'usdt', 'name'=>'USDT (Crypto)', 'icon'=>'coin', 'color'=>'violet'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($default_methods as $m) {
        $key = 'payment_instructions_'.$m['key'];
        $val = sanitize($_POST[$key] ?? '');
        $stmt = $pdo->prepare("INSERT INTO theme_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $val, $val]);
    }
    $msg = "Payment instructions updated successfully!";
    $theme = [];
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM theme_settings");
    while($row = $stmt->fetch()) $theme[$row['setting_key']] = $row['setting_value'];
}

// Fetch current instructions
$methods = [];
foreach ($default_methods as $m) {
    $key = 'payment_instructions_'.$m['key'];
    $m['instructions'] = $theme[$key] ?? '';
    $methods[] = $m;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Payment Methods - <?= $theme['site_name'] ?></title>
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
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Payment Methods</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Configure payment instructions shown to users during deposit.</p>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Bank Transfer</p>
              <h3 class="text-xs font-bold text-white mt-1">Configured</h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1M9 13h1m4 0h1M9 17h1m4 0h1" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">EasyPaisa</p>
              <h3 class="text-xs font-bold text-white mt-1">Configured</h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" stroke-width="2"></rect><path d="M12 18h.01" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
        <div class="bg-[#ef4444] p-4 rounded-xl shadow-lg shadow-rose-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">JazzCash</p>
              <h3 class="text-xs font-bold text-white mt-1">Configured</h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" stroke-width="2"></rect><path d="M12 18h.01" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
        <div class="bg-[#8b5cf6] p-4 rounded-xl shadow-lg shadow-violet-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">USDT</p>
              <h3 class="text-xs font-bold text-white mt-1">Configured</h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"></circle><path d="M9 9h6M12 9v6M9 12h6" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">

        <?php foreach($methods as $m): ?>
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-<?= $m['color'] ?>-500 flex items-center justify-center">
              <?php if($m['icon'] === 'building'): ?>
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1M9 13h1m4 0h1M9 17h1m4 0h1" stroke-linecap="round" stroke-width="2"></path></svg>
              <?php elseif($m['icon'] === 'mobile'): ?>
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" stroke-width="2"></rect><path d="M12 18h.01" stroke-linecap="round" stroke-width="2"></path></svg>
              <?php else: ?>
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"></circle><path d="M9 9h6M12 9v6M9 12h6" stroke-linecap="round" stroke-width="2"></path></svg>
              <?php endif; ?>
            </div>
            <h3 class="text-sm font-bold text-slate-800"><?= htmlspecialchars($m['name']) ?> Instructions</h3>
          </div>
          <textarea name="payment_instructions_<?= $m['key'] ?>" rows="3" class="w-full px-3 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-<?= $m['color'] ?>-500 font-mono resize-none" placeholder="Enter account details, title, instructions..."><?= htmlspecialchars($m['instructions']) ?></textarea>
        </div>
        <?php endforeach; ?>

        <button type="submit" class="w-full bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white font-bold text-xs py-3 rounded-lg shadow-lg shadow-indigo-500/30 hover:shadow-xl transition-all">
          Save Payment Instructions
        </button>
      </form>

      <div class="bg-gradient-to-br from-[#10b981] to-[#059669] p-5 rounded-xl text-white shadow-lg shadow-emerald-500/30">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
          <div>
            <h4 class="text-sm font-bold mb-1">How It Works</h4>
            <p class="text-[11px] opacity-90 leading-relaxed">These instructions are displayed to users on the deposit page. When a user selects a payment method, they'll see the corresponding bank details to complete the transfer.</p>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

</body>
</html>