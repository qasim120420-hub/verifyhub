<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));

$stmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id = ?");
$stmt->execute([$user_id]); $wallet = $stmt->fetch();
$balance = $wallet['balance'] ?? 0;

$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM deposits WHERE user_id = ? AND status='pending'");
$stmt->execute([$user_id]); $pending = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 8");
$stmt->execute([$user_id]); $transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Wallet - <?= $theme['site_name'] ?></title>
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
        <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full" placeholder="Search..." type="text"/>
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
      <h1 class="text-lg font-bold text-slate-900 tracking-tight">My Wallet</h1>
      <p class="text-[11px] text-slate-500 mt-0.5">Track your balance, transactions and refunds.</p>
    </div>

    <!-- Wallet Hero -->
    <div class="bg-gradient-to-br from-[#6366f1] via-[#8b5cf6] to-[#ec4899] p-6 rounded-2xl shadow-xl shadow-indigo-500/30 relative overflow-hidden">
      <div class="absolute -top-16 -right-16 w-64 h-64 bg-white/10 rounded-full"></div>
      <div class="relative z-10">
        <p class="text-xs text-white/80 font-semibold uppercase tracking-wider">Available Balance</p>
        <h2 class="text-3xl font-bold text-white mt-2 mb-4">PKR <?= number_format($balance, 2) ?></h2>
        <div class="flex gap-2 flex-wrap">
          <a href="deposit.php" class="bg-white text-[#6366f1] font-bold text-xs px-5 py-2 rounded-full shadow-lg hover:shadow-xl transition">+ Add Funds</a>
          <a href="transfer.php" class="bg-white/20 backdrop-blur-sm border border-white/30 text-white font-bold text-xs px-5 py-2 rounded-full hover:bg-white/30 transition">↔ Transfer</a>
        </div>
      </div>
    </div>

    <!-- Stat Grid Colorful -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="bg-[#10b981] p-3.5 rounded-xl shadow-lg shadow-emerald-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Deposited</p>
        <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($wallet['total_deposited'] ?? 0, 2) ?></h3>
      </div>
      <div class="bg-[#ef4444] p-3.5 rounded-xl shadow-lg shadow-rose-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Spent</p>
        <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($wallet['total_spent'] ?? 0, 2) ?></h3>
      </div>
      <div class="bg-[#06b6d4] p-3.5 rounded-xl shadow-lg shadow-cyan-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Refunded</p>
        <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($wallet['total_refunded'] ?? 0, 2) ?></h3>
      </div>
      <div class="bg-[#f59e0b] p-3.5 rounded-xl shadow-lg shadow-amber-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Pending</p>
        <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($pending, 2) ?></h3>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
      <div class="p-3.5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 rounded-md bg-[#6366f1] flex items-center justify-center">
            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
          <h3 class="text-xs font-bold text-slate-800">Recent Transactions</h3>
        </div>
        <a href="transactions.php" class="text-[11px] font-semibold text-[#6366f1] hover:underline">View All →</a>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead><tr class="text-slate-500 font-semibold bg-gradient-to-r from-indigo-50 to-violet-50">
            <th class="py-2.5 px-4 font-medium text-[10px]">Type</th>
            <th class="py-2.5 px-4 font-medium text-[10px]">Amount</th>
            <th class="py-2.5 px-4 font-medium text-[10px]">Description</th>
            <th class="py-2.5 px-4 font-medium text-[10px]">Status</th>
            <th class="py-2.5 px-4 font-medium text-[10px] text-right">Date</th>
          </tr></thead>
          <tbody class="divide-y divide-slate-100">
            <?php if(empty($transactions)): ?>
              <tr><td colspan="5" class="py-8 text-center text-slate-400 text-[11px]">No transactions yet</td></tr>
            <?php else: foreach($transactions as $t):
              $income = in_array($t['type'], ['deposit','refund','admin_credit']);
              $iconBg = $income ? 'bg-emerald-500' : 'bg-rose-500';
              $stCls = $t['status']=='completed' ? 'bg-emerald-100 text-emerald-700' : ($t['status']=='pending'?'bg-amber-100 text-amber-700':'bg-slate-100 text-slate-500');
            ?>
            <tr class="hover:bg-slate-50/60 transition-colors">
              <td class="py-3 px-4">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full <?= $iconBg ?> text-white flex items-center justify-center text-[10px] font-bold">
                    <?= $income ? '↓' : '↑' ?>
                  </div>
                  <span class="font-semibold text-slate-800 text-[11px] capitalize"><?= str_replace('_',' ',$t['type']) ?></span>
                </div>
              </td>
              <td class="py-3 px-4 font-bold text-[11px] <?= $income ? 'text-emerald-600' : 'text-rose-600' ?>">
                <?= $income ? '+' : '-' ?> PKR <?= number_format($t['amount'], 2) ?>
              </td>
              <td class="py-3 px-4 text-slate-500 text-[11px]"><?= htmlspecialchars(substr($t['description'], 0, 40)) ?></td>
              <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= $stCls ?>"><?= ucfirst($t['status']) ?></span></td>
              <td class="py-3 px-4 text-right text-slate-400 text-[10px]"><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
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