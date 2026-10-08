<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = '';

if (isset($_GET['cancel'])) {
    $oid = (int)$_GET['cancel'];
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND status NOT IN ('completed','cancelled')");
    $stmt->execute([$oid]);
    $order = $stmt->fetch();
    if ($order) {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
        $stmt->execute([$order['user_id']]);
        $old = (float)$stmt->fetchColumn();
        $new = $old + $order['amount'];
        $pdo->prepare("UPDATE wallets SET balance = ?, total_refunded = total_refunded + ? WHERE user_id = ?")->execute([$new, $order['amount'], $order['user_id']]);
        $pdo->prepare("UPDATE numbers SET status='available', assigned_to=NULL WHERE id = ?")->execute([$order['number_id']]);
        $pdo->prepare("UPDATE orders SET status='cancelled' WHERE id = ?")->execute([$oid]);
        $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, previous_balance, new_balance, description, status) VALUES (?, 'refund', ?, ?, ?, ?, 'completed')")
            ->execute([$order['user_id'], $order['amount'], $old, $new, "Refund for cancelled order #$oid"]);
        try { addNotification($pdo, $order['user_id'], "Order Cancelled", "Order #$oid cancelled & refunded."); } catch (Exception $e) {}
        $pdo->commit();
        $msg = "Order cancelled and refunded!";
    }
}

$status_filter = sanitize($_GET['status'] ?? '');

$sql = "SELECT o.*, u.username, u.full_name, n.number, n.type, c.name as country, c.flag_icon, s.name as service FROM orders o JOIN users u ON o.user_id = u.id JOIN numbers n ON o.number_id = n.id LEFT JOIN countries c ON n.country_id = c.id LEFT JOIN services s ON n.service_id = s.id WHERE 1=1";
$params = [];
if ($status_filter === 'active') $sql .= " AND o.status IN ('pending','waiting_otp','otp_received')";
elseif ($status_filter) { $sql .= " AND o.status = ?"; $params[] = $status_filter; }
$sql .= " ORDER BY o.created_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$active = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending','waiting_otp','otp_received')")->fetchColumn();
$completed = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='completed'")->fetchColumn();
$cancelled = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='cancelled'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Orders - <?= $theme['site_name'] ?></title>
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

      <div>
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Order Management</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">View, filter and manage all user orders.</p>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Orders</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($total) ?></h3>
        </div>
        <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Active</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($active) ?></h3>
        </div>
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Completed</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($completed) ?></h3>
        </div>
        <div class="bg-[#ef4444] p-4 rounded-xl shadow-lg shadow-rose-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Cancelled</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($cancelled) ?></h3>
        </div>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <div class="flex gap-2 flex-wrap">
        <a href="orders.php" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='' ? 'bg-[#6366f1] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">All</a>
        <a href="?status=active" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='active' ? 'bg-[#f59e0b] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Active</a>
        <a href="?status=completed" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='completed' ? 'bg-[#10b981] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Completed</a>
        <a href="?status=cancelled" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='cancelled' ? 'bg-[#ef4444] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Cancelled</a>
        <a href="?status=expired" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='expired' ? 'bg-[#8b5cf6] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Expired</a>
      </div>

      <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead><tr class="bg-gradient-to-r from-indigo-50 to-violet-50">
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Order ID</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">User</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Number</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Service</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Amount</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Status</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
              <?php if(empty($orders)): ?>
                <tr><td colspan="7" class="py-10 text-center text-slate-400 text-[11px]">No orders found</td></tr>
              <?php else: foreach($orders as $o):
                $st = $o['status'];
                $stCls = $st=='completed'?'bg-emerald-500':($st=='cancelled'?'bg-rose-500':($st=='expired'?'bg-violet-500':($st=='waiting_otp'?'bg-cyan-500':'bg-amber-500')));
              ?>
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3 px-4 font-bold text-indigo-600 text-[11px]">#ORD-<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?></td>
                <td class="py-3 px-4">
                  <p class="font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($o['full_name'] ?: $o['username']) ?></p>
                  <p class="text-[10px] text-slate-400">@<?= htmlspecialchars($o['username']) ?></p>
                </td>
                <td class="py-3 px-4 font-mono text-slate-700 text-[11px]"><?= $o['number'] ?></td>
                <td class="py-3 px-4 text-slate-600 text-[11px]"><?= $o['service'] ?> · <?= ucfirst($o['type']) ?></td>
                <td class="py-3 px-4 font-bold text-emerald-600 text-[11px]">PKR <?= number_format($o['amount'], 2) ?></td>
                <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold text-white uppercase <?= $stCls ?>"><?= str_replace('_',' ',$st) ?></span></td>
                <td class="py-3 px-4 text-right">
                  <?php if(!in_array($o['status'], ['completed','cancelled','expired'])): ?>
                    <a href="?cancel=<?= $o['id'] ?>" onclick="return confirm('Cancel and refund this order?')" class="bg-[#ef4444] text-white text-[10px] font-bold px-2 py-1 rounded-md">Cancel + Refund</a>
                  <?php else: ?>
                    <span class="text-[10px] text-slate-400">—</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

</body>
</html>