<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $user_id);

$status_filter = sanitize($_GET['status'] ?? '');

$sql = "SELECT o.*, n.number, n.type, c.name as country, c.flag_icon, s.name as service FROM orders o JOIN numbers n ON o.number_id = n.id LEFT JOIN countries c ON n.country_id = c.id LEFT JOIN services s ON n.service_id = s.id WHERE o.user_id = ?";
$params = [$user_id];
if ($status_filter) { $sql .= " AND o.status = ?"; $params[] = $status_filter; }
$sql .= " ORDER BY o.created_at DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?"); $stmt->execute([$user_id]); $total_count = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status='completed'"); $stmt->execute([$user_id]); $completed_count = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status='cancelled'"); $stmt->execute([$user_id]); $cancelled_count = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status='expired'"); $stmt->execute([$user_id]); $expired_count = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Order History - <?= $theme['site_name'] ?></title>
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
        <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full focus:outline-none focus:ring-1 focus:ring-indigo-500" placeholder="Search orders..." type="text"/>
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
      <h1 class="text-lg font-bold text-slate-900 tracking-tight">Order History</h1>
      <p class="text-[11px] text-slate-500 mt-0.5">Complete record of all your past orders.</p>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="bg-[#6366f1] p-3.5 rounded-xl shadow-lg shadow-indigo-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Orders</p>
        <h3 class="text-xl font-bold text-white mt-1"><?= $total_count ?></h3>
      </div>
      <div class="bg-[#10b981] p-3.5 rounded-xl shadow-lg shadow-emerald-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Completed</p>
        <h3 class="text-xl font-bold text-white mt-1"><?= $completed_count ?></h3>
      </div>
      <div class="bg-[#ef4444] p-3.5 rounded-xl shadow-lg shadow-rose-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Cancelled</p>
        <h3 class="text-xl font-bold text-white mt-1"><?= $cancelled_count ?></h3>
      </div>
      <div class="bg-[#f59e0b] p-3.5 rounded-xl shadow-lg shadow-amber-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Expired</p>
        <h3 class="text-xl font-bold text-white mt-1"><?= $expired_count ?></h3>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex gap-2 flex-wrap">
      <a href="order-history.php" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='' ? 'bg-[#6366f1] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">All</a>
      <a href="?status=completed" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='completed' ? 'bg-[#10b981] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Completed</a>
      <a href="?status=pending" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='pending' ? 'bg-[#f59e0b] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Pending</a>
      <a href="?status=cancelled" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='cancelled' ? 'bg-[#ef4444] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Cancelled</a>
      <a href="?status=expired" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='expired' ? 'bg-[#8b5cf6] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Expired</a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead><tr class="bg-gradient-to-r from-indigo-50 to-violet-50">
            <th class="py-2.5 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Order ID</th>
            <th class="py-2.5 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Number</th>
            <th class="py-2.5 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Service</th>
            <th class="py-2.5 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Amount</th>
            <th class="py-2.5 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Status</th>
            <th class="py-2.5 px-4 font-semibold text-[10px] text-indigo-700 uppercase text-right">Date</th>
          </tr></thead>
          <tbody class="divide-y divide-slate-100">
            <?php if(empty($orders)): ?>
              <tr><td colspan="6" class="py-10 text-center text-slate-400 text-[11px]">No orders found</td></tr>
            <?php else: foreach($orders as $o):
              $st = $o['status'];
              $stCls = $st=='completed'?'bg-emerald-500':($st=='cancelled'?'bg-rose-500':($st=='expired'?'bg-violet-500':'bg-amber-500'));
            ?>
            <tr class="hover:bg-slate-50/60 transition-colors">
              <td class="py-3 px-4 font-bold text-indigo-600 text-[11px]">#ORD-<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?></td>
              <td class="py-3 px-4">
                <p class="font-bold text-slate-800 text-[11px] font-mono"><?= $o['number'] ?></p>
                <p class="text-[10px] text-slate-400"><?= ucfirst($o['type']) ?> · <?= $o['country'] ?? 'N/A' ?></p>
              </td>
              <td class="py-3 px-4 text-slate-600 text-[11px]"><?= $o['service'] ?? 'N/A' ?></td>
              <td class="py-3 px-4 font-bold text-slate-800 text-[11px]">PKR <?= number_format($o['amount'], 2) ?></td>
              <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold text-white uppercase <?= $stCls ?>"><?= str_replace('_',' ',$st) ?></span></td>
              <td class="py-3 px-4 text-right text-slate-400 text-[10px]"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

</body>
</html>