<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $user_id);

$stmt = $pdo->prepare("SELECT o.*, n.number, n.type, c.name as country, c.flag_icon, s.name as service FROM orders o JOIN numbers n ON o.number_id = n.id LEFT JOIN countries c ON n.country_id = c.id LEFT JOIN services s ON n.service_id = s.id WHERE o.user_id = ? AND o.status IN ('pending','waiting_otp','otp_received') ORDER BY o.created_at DESC");
$stmt->execute([$user_id]); $orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Active Orders - <?= $theme['site_name'] ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"','sans-serif'] } } } }</script>
<style>
  ::-webkit-scrollbar { width: 5px; height: 5px; }
  ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
  .sidebar-scroll::-webkit-scrollbar-thumb { background: #2d3056; }
  @keyframes pulse-dot { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:0.6; transform:scale(1.3); } }
  .live-dot { animation: pulse-dot 1.5s infinite; }
</style>
</head>
<body class="bg-[#f4f6fc] text-slate-800 antialiased font-sans flex h-screen overflow-hidden text-sm">
<?php include '../includes/user_sidebar.php'; ?>

<div class="flex-1 flex flex-col h-screen overflow-hidden bg-[#f5f7fc]">
  <header class="h-14 bg-white border-b border-slate-200/80 px-5 flex items-center justify-between flex-shrink-0">
    <div class="flex items-center gap-3 w-80">
      <button class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-width="2"></path></svg></button>
      <div class="relative w-full">
        <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full" placeholder="Search orders..." type="text"/>
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-width="2"></path></svg></span>
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
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Active Orders</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Real-time OTP tracking for your live orders.</p>
      </div>
      <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-full">
        <span class="w-2 h-2 bg-emerald-500 rounded-full live-dot"></span>
        <span class="text-[11px] font-bold text-emerald-700">Live</span>
      </div>
    </div>

    <?php if(empty($orders)): ?>
      <div class="bg-white p-10 rounded-xl border border-slate-200/70 shadow-sm text-center">
        <div class="text-5xl mb-3">📭</div>
        <h3 class="text-sm font-bold text-slate-500">No active orders</h3>
        <a href="buy-number.php" class="inline-block mt-4 bg-[#6366f1] hover:bg-indigo-600 text-white text-xs font-bold px-5 py-2 rounded-lg shadow-md">Buy a Number</a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
        <?php foreach($orders as $o):
          $st = $o['status'];
          $stLabel = ucwords(str_replace('_',' ',$st));
          $stGrad = $st=='waiting_otp' ? 'from-[#f59e0b] to-[#fbbf24]' : ($st=='otp_received' ? 'from-[#10b981] to-[#34d399]' : 'from-[#6366f1] to-[#8b5cf6]');
        ?>
        <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r <?= $stGrad ?> p-3 flex items-center justify-between">
            <div class="flex items-center gap-2 text-white">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-width="2"></path></svg>
              <span class="text-[11px] font-bold uppercase tracking-wider"><?= str_replace('_',' ', $st) ?></span>
            </div>
            <span class="text-[10px] font-bold text-white bg-white/25 px-2 py-0.5 rounded-full">#ORD-<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?></span>
          </div>
          <div class="p-4">
            <h3 class="text-base font-bold text-slate-800 font-mono"><?= $o['number'] ?></h3>
            <p class="text-[11px] text-slate-500 mt-1"><?= $o['flag_icon'] ?> <?= $o['country'] ?> · <?= $o['service'] ?> · <?= ucfirst($o['type']) ?></p>

            <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-slate-100 text-center">
              <div><p class="text-[9px] text-slate-400 uppercase font-semibold">Amount</p><p class="text-[11px] font-bold text-slate-700">PKR <?= number_format($o['amount'], 0) ?></p></div>
              <div><p class="text-[9px] text-slate-400 uppercase font-semibold">Created</p><p class="text-[11px] font-bold text-slate-700"><?= date('h:i A', strtotime($o['created_at'])) ?></p></div>
              <div><p class="text-[9px] text-slate-400 uppercase font-semibold">Expires</p><p class="text-[11px] font-bold text-rose-600"><?= $o['expires_at'] ? date('h:i A', strtotime($o['expires_at'])) : 'N/A' ?></p></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<script>setInterval(() => location.reload(), 15000);</script>

</body>
</html>