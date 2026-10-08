<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $_SESSION['user_id']);

$faqs = [
    ['What are virtual numbers?', 'Virtual numbers are cloud-based phone numbers that can receive SMS without a physical SIM.', 'indigo'],
    ['How fast are OTPs delivered?', 'OTPs typically arrive within 5-30 seconds of being sent.', 'emerald'],
    ['What payment methods are supported?', 'We accept Bank Transfer, EasyPaisa, JazzCash, and USDT (Crypto).', 'pink'],
    ['What happens when an order expires?', 'Expired orders are automatically refunded to your wallet.', 'amber'],
    ['Is my data secure?', 'Yes — we use bank-grade encryption and hashed passwords.', 'violet'],
    ['How do I contact support?', 'You can create a support ticket from the Support section.', 'cyan'],
    ['Can I transfer money to other users?', 'Yes — use the Transfer option in your Wallet section.', 'rose'],
    ['How long does deposit approval take?', 'Manual approval typically takes 1-24 hours during business days.', 'teal'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Help Center - <?= $theme['site_name'] ?></title>
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
        <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full" placeholder="Search help articles..." type="text"/>
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
      <h1 class="text-lg font-bold text-slate-900 tracking-tight">Help Center</h1>
      <p class="text-[11px] text-slate-500 mt-0.5">Find answers to common questions about <?= $theme['site_name'] ?>.</p>
    </div>

    <div class="bg-gradient-to-br from-[#6366f1] to-[#8b5cf6] p-6 rounded-2xl text-white shadow-xl shadow-indigo-500/30 relative overflow-hidden">
      <div class="absolute -top-16 -right-16 w-64 h-64 bg-white/10 rounded-full"></div>
      <div class="relative z-10">
        <h2 class="text-xl font-bold mb-2">Hi <?= htmlspecialchars($user_full_name) ?>!</h2>
        <p class="text-sm opacity-90">How can we help you today?</p>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <?php
      $cats = [
        ['Account', 'user', 'indigo'],
        ['Wallet', 'wallet', 'emerald'],
        ['Numbers', 'phone', 'pink'],
        ['Support', 'chat', 'violet']
      ];
      foreach($cats as $c): ?>
        <div class="bg-white p-4 rounded-xl border border-slate-200/70 shadow-sm hover:shadow-md hover:border-<?= $c[2] ?>-300 transition-all cursor-pointer">
          <div class="w-9 h-9 rounded-lg bg-<?= $c[2] ?>-100 flex items-center justify-center mb-2">
            <svg class="w-4.5 h-4.5 text-<?= $c[2] ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <?php if($c[1]==='user'): ?><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke-linecap="round" stroke-width="2"></path><circle cx="12" cy="7" r="4" stroke-width="2"></circle><?php endif; ?>
              <?php if($c[1]==='wallet'): ?><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke-linecap="round" stroke-width="2"></path><?php endif; ?>
              <?php if($c[1]==='phone'): ?><rect x="5" y="2" width="14" height="20" rx="2" stroke-width="2"></rect><path d="M12 18h.01" stroke-linecap="round" stroke-width="2"></path><?php endif; ?>
              <?php if($c[1]==='chat'): ?><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" stroke-linecap="round" stroke-width="2"></path><?php endif; ?>
            </svg>
          </div>
          <h3 class="text-[12px] font-bold text-slate-800"><?= $c[0] ?></h3>
          <p class="text-[10px] text-slate-500 mt-0.5">Get help with <?= strtolower($c[0]) ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <div>
      <h2 class="text-base font-bold text-slate-900 mb-3">Frequently Asked Questions</h2>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
        <?php foreach($faqs as $i => $f): ?>
        <div class="bg-white p-4 rounded-xl border border-slate-200/70 shadow-sm hover:shadow-md transition-all cursor-pointer" onclick="this.querySelector('.answer').classList.toggle('hidden')">
          <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-<?= $f[2] ?>-500 to-<?= $f[2] ?>-600 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3" stroke-linecap="round" stroke-width="2"></path><circle cx="12" cy="17" r="1" fill="currentColor"></circle></svg>
            </div>
            <div class="flex-1">
              <h3 class="text-[12px] font-bold text-slate-800"><?= $f[0] ?></h3>
              <p class="answer hidden text-[11px] text-slate-600 mt-2 leading-relaxed"><?= $f[1] ?></p>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="bg-gradient-to-br from-[#ec4899] to-[#f472b6] p-6 rounded-2xl text-white shadow-xl shadow-pink-500/30 text-center">
      <div class="w-12 h-12 mx-auto rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mb-3">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536" stroke-linecap="round" stroke-width="2"></path><circle cx="12" cy="12" r="9" stroke-width="2"></circle></svg>
      </div>
      <h3 class="text-base font-bold mb-1">Still need help?</h3>
      <p class="text-[12px] opacity-90 mb-4">Our support team is always here to help you.</p>
      <a href="support.php" class="inline-block bg-white text-[#ec4899] font-bold text-xs px-5 py-2 rounded-full shadow-lg">Contact Support</a>
    </div>
  </main>
</div>

</body>
</html>