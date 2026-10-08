<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $user_id);

$stmt = $pdo->prepare("SELECT om.*, o.id as order_id, n.number, s.name as service, c.flag_icon FROM otp_messages om JOIN orders o ON om.order_id = o.id JOIN numbers n ON om.number_id = n.id LEFT JOIN services s ON n.service_id = s.id LEFT JOIN countries c ON n.country_id = c.id WHERE o.user_id = ? ORDER BY om.received_at DESC LIMIT 100");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM otp_messages om JOIN orders o ON om.order_id = o.id WHERE o.user_id = ?");
$stmt->execute([$user_id]); $total_otp = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM otp_messages om JOIN orders o ON om.order_id = o.id WHERE o.user_id = ? AND DATE(om.received_at) = CURDATE()");
$stmt->execute([$user_id]); $today_otp = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>OTP Messages - <?= $theme['site_name'] ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"','sans-serif'] } } } }</script>
<style>
  ::-webkit-scrollbar { width: 5px; height: 5px; }
  ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
  .sidebar-scroll::-webkit-scrollbar-thumb { background: #2d3056; }
  @keyframes pulse-dot { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
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
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-width="2"></path></svg></span>
        <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full" placeholder="Search OTP messages..." type="text"/>
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
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">OTP Messages</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">All verification codes received on your numbers.</p>
      </div>
      <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-full">
        <span class="w-2 h-2 bg-emerald-500 rounded-full live-dot"></span>
        <span class="text-[11px] font-bold text-emerald-700">Live</span>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total OTPs</p>
            <h3 class="text-2xl font-bold text-white mt-1"><?= $total_otp ?></h3>
          </div>
          <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
        </div>
      </div>
      <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Today</p>
            <h3 class="text-2xl font-bold text-white mt-1"><?= $today_otp ?></h3>
          </div>
          <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
        </div>
      </div>
      <div class="bg-[#8b5cf6] p-4 rounded-xl shadow-lg shadow-violet-500/30">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Latest Code</p>
            <h3 class="text-xl font-bold text-white mt-1 font-mono"><?= $messages[0]['otp_code'] ?? '—' ?></h3>
          </div>
          <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Messages Timeline -->
    <?php if(empty($messages)): ?>
      <div class="bg-white p-10 rounded-xl border border-slate-200/70 shadow-sm text-center">
        <div class="w-16 h-16 mx-auto rounded-full bg-indigo-50 flex items-center justify-center mb-3">
          <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" stroke-linecap="round" stroke-width="2"></path></svg>
        </div>
        <h3 class="text-sm font-bold text-slate-500">No OTP messages yet</h3>
        <p class="text-[11px] text-slate-400 mt-1">OTPs will appear here in real-time.</p>
      </div>
    <?php else: ?>
      <div class="space-y-2">
        <?php foreach($messages as $m): ?>
        <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm p-4 hover:shadow-md hover:border-indigo-300 transition-all">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#6366f1] to-[#8b5cf6] flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-3 mb-2">
                <div>
                  <p class="font-bold text-slate-800 text-[12px]"><?= $m['flag_icon'] ?> <?= $m['number'] ?> · <?= $m['service'] ?></p>
                  <p class="text-[10px] text-slate-500">Order #ORD-<?= str_pad($m['order_id'],4,'0',STR_PAD_LEFT) ?> · <?= date('M j, Y h:i A', strtotime($m['received_at'])) ?></p>
                </div>
                <div class="bg-gradient-to-r from-[#10b981] to-[#34d399] text-white px-3 py-1 rounded-lg font-mono font-bold text-sm tracking-wider flex-shrink-0">
                  <?= $m['otp_code'] ?>
                </div>
              </div>
              <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-lg text-[11px] text-slate-600 font-mono break-all">
                <?= htmlspecialchars($m['full_message']) ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<script>setInterval(() => location.reload(), 20000);</script>

</body>
</html>