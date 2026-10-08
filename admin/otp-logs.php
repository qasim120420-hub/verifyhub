<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$search = sanitize($_GET['search'] ?? '');

$sql = "SELECT om.*, o.id as order_id, n.number, u.username, u.full_name, s.name as service
        FROM otp_messages om 
        JOIN orders o ON om.order_id = o.id 
        JOIN numbers n ON om.number_id = n.id 
        JOIN users u ON o.user_id = u.id
        LEFT JOIN services s ON n.service_id = s.id
        WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (om.otp_code LIKE ? OR n.number LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY om.received_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM otp_messages")->fetchColumn();
$today = $pdo->query("SELECT COUNT(*) FROM otp_messages WHERE DATE(received_at)=CURDATE()")->fetchColumn();
$hour = $pdo->query("SELECT COUNT(*) FROM otp_messages WHERE received_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>OTP Logs - <?= $theme['site_name'] ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] }, colors: { brand: { sidebar:'#0d1026', sidebarHover:'#171c3d', purple:'#5e48e8' } } } } }</script>
<style>
  ::-webkit-scrollbar { width: 5px; height: 5px; }
  ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  @keyframes pulse-dot { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
  .live-dot { animation: pulse-dot 1.5s infinite; }
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
  <form method="GET" class="flex-1 max-w-xl mx-4 hidden md:block">
    <div class="relative">
      <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-width="2"></path></svg></span>
      <input name="search" value="<?= htmlspecialchars($search) ?>" class="w-full pl-10 pr-4 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs placeholder-slate-400 focus:outline-none focus:border-brand-purple" placeholder="Search OTP, number, user..." type="text"/>
    </div>
  </form>
  <div class="flex items-center gap-3">
    <div class="flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
      <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full live-dot"></span>
      <span class="text-[10px] font-bold text-emerald-700">Live</span>
    </div>
    <div class="w-7 h-7 rounded-full bg-slate-900 border border-slate-300 flex items-center justify-center text-white text-[11px] font-semibold">SA</div>
  </div>
</header>

<div class="flex flex-1 overflow-hidden">
  <?php include '../includes/admin_sidebar.php'; ?>

  <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
    <div class="max-w-7xl mx-auto space-y-4">

      <div>
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">OTP Logs</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">All OTP messages received across the platform.</p>
      </div>

      <div class="grid grid-cols-3 gap-3">
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total OTPs</p>
              <h3 class="text-xl font-bold text-white mt-1"><?= number_format($total) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Today</p>
              <h3 class="text-xl font-bold text-white mt-1"><?= number_format($today) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"></rect><path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
        <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Last Hour</p>
              <h3 class="text-xl font-bold text-white mt-1"><?= number_format($hour) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"></circle><path d="M12 6v6l4 2" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead><tr class="bg-gradient-to-r from-indigo-50 to-violet-50">
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">ID</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">OTP</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Number</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">User</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Service</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Message</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase text-right">Received</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
              <?php if(empty($logs)): ?>
                <tr><td colspan="7" class="py-10 text-center text-slate-400 text-[11px]">No OTP logs found</td></tr>
              <?php else: foreach($logs as $l): ?>
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3 px-4 text-slate-500 text-[11px]">#<?= $l['id'] ?></td>
                <td class="py-3 px-4">
                  <span class="px-2 py-1 rounded bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white font-mono font-bold text-[11px] tracking-wider">
                    <?= htmlspecialchars($l['otp_code']) ?>
                  </span>
                </td>
                <td class="py-3 px-4 font-mono font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($l['number']) ?></td>
                <td class="py-3 px-4">
                  <p class="font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($l['full_name'] ?: $l['username']) ?></p>
                  <p class="text-[10px] text-slate-400">@<?= htmlspecialchars($l['username']) ?></p>
                </td>
                <td class="py-3 px-4 text-slate-600 text-[11px]"><?= htmlspecialchars($l['service'] ?? 'N/A') ?></td>
                <td class="py-3 px-4 text-slate-500 text-[10px] max-w-xs truncate"><?= htmlspecialchars(substr($l['full_message'], 0, 60)) ?></td>
                <td class="py-3 px-4 text-right text-slate-400 text-[10px]"><?= date('M j, h:i A', strtotime($l['received_at'])) ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<script>setInterval(() => location.reload(), 20000);</script>

</body>
</html>