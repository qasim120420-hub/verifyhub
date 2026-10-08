<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$range = sanitize($_GET['range'] ?? '7');
$days = (int)$range;

$stmt = $pdo->prepare("SELECT DATE(created_at) as d, COALESCE(SUM(amount),0) as total FROM wallet_transactions WHERE type='purchase' AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) GROUP BY DATE(created_at) ORDER BY d ASC");
$stmt->execute([$days]);
$revenue_chart = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT DATE(created_at) as d, COUNT(*) as total FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) GROUP BY DATE(created_at) ORDER BY d ASC");
$stmt->execute([$days]);
$orders_chart = $stmt->fetchAll();

$total_revenue = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE type='purchase'")->fetchColumn();
$total_deposits = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM deposits WHERE status='approved'")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$avg_order = $total_orders > 0 ? $total_revenue / $total_orders : 0;

$top_services = $pdo->query("SELECT s.name, COUNT(o.id) as cnt, COALESCE(SUM(o.amount),0) as rev FROM services s LEFT JOIN numbers n ON n.service_id = s.id LEFT JOIN orders o ON o.number_id = n.id GROUP BY s.id ORDER BY cnt DESC LIMIT 5")->fetchAll();

$top_countries = $pdo->query("SELECT c.name, c.flag_icon, COUNT(o.id) as cnt FROM countries c LEFT JOIN numbers n ON n.country_id = c.id LEFT JOIN orders o ON o.number_id = n.id GROUP BY c.id ORDER BY cnt DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Reports - <?= $theme['site_name'] ?></title>
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
    <div class="max-w-7xl mx-auto space-y-4">

      <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h1 class="text-lg font-bold text-slate-900 tracking-tight">Reports & Analytics</h1>
          <p class="text-[11px] text-slate-500 mt-0.5">Business insights and performance metrics.</p>
        </div>
        <div class="flex gap-2">
          <a href="?range=7" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $days==7 ? 'bg-[#6366f1] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">7 Days</a>
          <a href="?range=30" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $days==30 ? 'bg-[#6366f1] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">30 Days</a>
          <a href="?range=90" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $days==90 ? 'bg-[#6366f1] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">90 Days</a>
        </div>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Revenue</p>
              <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($total_revenue, 0) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke-linecap="round" stroke-width="2.5"></path></svg>
            </div>
          </div>
        </div>
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Deposits</p>
              <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($total_deposits, 0) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v14M19 12l-7 7-7-7" stroke-linecap="round" stroke-width="2.5"></path></svg>
            </div>
          </div>
        </div>
        <div class="bg-[#8b5cf6] p-4 rounded-xl shadow-lg shadow-violet-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Orders</p>
              <h3 class="text-base font-bold text-white mt-1"><?= number_format($total_orders) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
        <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Avg Order Value</p>
              <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($avg_order, 0) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 3v18h18M18 17V9M13 17V5M8 17v-3" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- Revenue Chart -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#10b981] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 8-8" stroke-linecap="round" stroke-width="2.5"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Revenue Trend</h3>
          </div>
          <div class="space-y-2">
            <?php
            $max_rev = max(array_column($revenue_chart ?: [['total'=>1]], 'total')) ?: 1;
            foreach($revenue_chart as $r):
              $pct = ($r['total'] / $max_rev) * 100;
            ?>
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-semibold text-slate-500 w-16"><?= date('M j', strtotime($r['d'])) ?></span>
              <div class="flex-1 h-6 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-[#10b981] to-[#34d399] rounded-full" style="width: <?= max($pct, 3) ?>%"></div>
              </div>
              <span class="text-[10px] font-bold text-slate-700 w-24 text-right">PKR <?= number_format($r['total'], 0) ?></span>
            </div>
            <?php endforeach; ?>
            <?php if(empty($revenue_chart)): ?>
              <p class="text-center text-[11px] text-slate-400 py-8">No data available</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Orders Chart -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#8b5cf6] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Orders Trend</h3>
          </div>
          <div class="space-y-2">
            <?php
            $max_ord = max(array_column($orders_chart ?: [['total'=>1]], 'total')) ?: 1;
            foreach($orders_chart as $o):
              $pct = ($o['total'] / $max_ord) * 100;
            ?>
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-semibold text-slate-500 w-16"><?= date('M j', strtotime($o['d'])) ?></span>
              <div class="flex-1 h-6 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-[#8b5cf6] to-[#a855f7] rounded-full" style="width: <?= max($pct, 3) ?>%"></div>
              </div>
              <span class="text-[10px] font-bold text-slate-700 w-16 text-right"><?= $o['total'] ?> orders</span>
            </div>
            <?php endforeach; ?>
            <?php if(empty($orders_chart)): ?>
              <p class="text-center text-[11px] text-slate-400 py-8">No data available</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Top Services -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#ec4899] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7l3-7z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Top Services</h3>
          </div>
          <div class="space-y-2">
            <?php foreach($top_services as $i => $s): ?>
            <div class="flex items-center justify-between p-3 rounded-lg bg-gradient-to-r from-pink-50 to-white border border-pink-100">
              <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-pink-500 text-white flex items-center justify-center text-[10px] font-bold"><?= $i+1 ?></span>
                <span class="text-[12px] font-bold text-slate-800"><?= htmlspecialchars($s['name']) ?></span>
              </div>
              <div class="text-right">
                <p class="text-[11px] font-bold text-slate-800"><?= $s['cnt'] ?> orders</p>
                <p class="text-[10px] text-slate-500">PKR <?= number_format($s['rev'], 0) ?></p>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Top Countries -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#06b6d4] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"></circle><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Top Countries</h3>
          </div>
          <div class="space-y-2">
            <?php foreach($top_countries as $i => $c): ?>
            <div class="flex items-center justify-between p-3 rounded-lg bg-gradient-to-r from-cyan-50 to-white border border-cyan-100">
              <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-cyan-500 text-white flex items-center justify-center text-[10px] font-bold"><?= $i+1 ?></span>
                <span class="text-[12px] font-bold text-slate-800"><?= $c['flag_icon'] ?> <?= htmlspecialchars($c['name']) ?></span>
              </div>
              <span class="text-[11px] font-bold text-slate-700"><?= $c['cnt'] ?> orders</span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>

</body>
</html>