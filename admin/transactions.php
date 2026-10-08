<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$type_filter = sanitize($_GET['type'] ?? '');

$sql = "SELECT t.*, u.username, u.full_name FROM wallet_transactions t JOIN users u ON t.user_id = u.id WHERE 1=1";
$params = [];
if ($type_filter) { $sql .= " AND t.type = ?"; $params[] = $type_filter; }
$sql .= " ORDER BY t.created_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM wallet_transactions")->fetchColumn();
$total_in = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE type IN ('deposit','refund','admin_credit')")->fetchColumn();
$total_out = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE type IN ('purchase','admin_debit','transfer')")->fetchColumn();
$today = $pdo->query("SELECT COUNT(*) FROM wallet_transactions WHERE DATE(created_at)=CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Transactions - <?= $theme['site_name'] ?></title>
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
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">All Transactions</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Complete ledger of all wallet movements.</p>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($total) ?></h3>
        </div>
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Money In</p>
          <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($total_in, 0) ?></h3>
        </div>
        <div class="bg-[#ef4444] p-4 rounded-xl shadow-lg shadow-rose-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Money Out</p>
          <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($total_out, 0) ?></h3>
        </div>
        <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Today</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($today) ?></h3>
        </div>
      </div>

      <div class="flex gap-2 flex-wrap">
        <a href="transactions.php" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='' ? 'bg-[#6366f1] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">All</a>
        <a href="?type=deposit" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='deposit' ? 'bg-[#10b981] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Deposits</a>
        <a href="?type=purchase" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='purchase' ? 'bg-[#ef4444] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Purchases</a>
        <a href="?type=refund" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='refund' ? 'bg-[#06b6d4] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Refunds</a>
        <a href="?type=transfer" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='transfer' ? 'bg-[#8b5cf6] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Transfers</a>
        <a href="?type=admin_credit" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='admin_credit' ? 'bg-[#f59e0b] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Admin Credit</a>
        <a href="?type=admin_debit" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='admin_debit' ? 'bg-[#ec4899] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Admin Debit</a>
      </div>

      <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead><tr class="bg-gradient-to-r from-indigo-50 to-violet-50">
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">ID</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">User</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Type</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Amount</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Prev</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">New</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Description</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase text-right">Date</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
              <?php if(empty($transactions)): ?>
                <tr><td colspan="8" class="py-10 text-center text-slate-400 text-[11px]">No transactions found</td></tr>
              <?php else: foreach($transactions as $t):
                $income = in_array($t['type'], ['deposit','refund','admin_credit']);
                $typeCls = $t['type']=='deposit'?'bg-emerald-500':($t['type']=='purchase'?'bg-rose-500':($t['type']=='refund'?'bg-cyan-500':($t['type']=='transfer'?'bg-violet-500':($t['type']=='admin_credit'?'bg-amber-500':'bg-pink-500'))));
              ?>
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3 px-4 text-slate-500 text-[11px]">#<?= $t['id'] ?></td>
                <td class="py-3 px-4">
                  <p class="font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($t['full_name'] ?: $t['username']) ?></p>
                  <p class="text-[10px] text-slate-400">@<?= htmlspecialchars($t['username']) ?></p>
                </td>
                <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold text-white uppercase <?= $typeCls ?>"><?= str_replace('_',' ',$t['type']) ?></span></td>
                <td class="py-3 px-4 font-bold text-[11px] <?= $income ? 'text-emerald-600' : 'text-rose-600' ?>">
                  <?= $income ? '+' : '-' ?> PKR <?= number_format($t['amount'], 2) ?>
                </td>
                <td class="py-3 px-4 text-slate-500 text-[11px]">PKR <?= number_format($t['previous_balance'], 2) ?></td>
                <td class="py-3 px-4 font-bold text-slate-700 text-[11px]">PKR <?= number_format($t['new_balance'], 2) ?></td>
                <td class="py-3 px-4 text-slate-500 text-[10px]"><?= htmlspecialchars(substr($t['description'], 0, 40)) ?></td>
                <td class="py-3 px-4 text-right text-slate-400 text-[10px]"><?= date('M j, Y h:i A', strtotime($t['created_at'])) ?></td>
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