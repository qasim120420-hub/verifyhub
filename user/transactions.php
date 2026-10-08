<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $user_id);

$type_filter = sanitize($_GET['type'] ?? '');
$sql = "SELECT * FROM wallet_transactions WHERE user_id = ?";
$params = [$user_id];
if ($type_filter) { $sql .= " AND type = ?"; $params[] = $type_filter; }
$sql .= " ORDER BY created_at DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE user_id=? AND type IN ('deposit','refund','admin_credit')");
$stmt->execute([$user_id]); $total_in = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE user_id=? AND type IN ('purchase','admin_debit')");
$stmt->execute([$user_id]); $total_out = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM wallet_transactions WHERE user_id=?"); $stmt->execute([$user_id]); $total_count = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Transactions - <?= $theme['site_name'] ?></title>
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
        <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full" placeholder="Search transactions..." type="text"/>
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
      <h1 class="text-lg font-bold text-slate-900 tracking-tight">Transaction History</h1>
      <p class="text-[11px] text-slate-500 mt-0.5">Complete record of all wallet movements.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Money In</p>
            <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($total_in, 2) ?></h3>
          </div>
          <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7" stroke-linecap="round" stroke-width="2.5"></path></svg>
          </div>
        </div>
      </div>
      <div class="bg-[#ef4444] p-4 rounded-xl shadow-lg shadow-rose-500/30">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Money Out</p>
            <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($total_out, 2) ?></h3>
          </div>
          <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v14M19 12l-7 7-7-7" stroke-linecap="round" stroke-width="2.5"></path></svg>
          </div>
        </div>
      </div>
      <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Transactions</p>
            <h3 class="text-base font-bold text-white mt-1"><?= $total_count ?></h3>
          </div>
          <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
        </div>
      </div>
    </div>

    <div class="flex gap-2 flex-wrap">
      <a href="transactions.php" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='' ? 'bg-[#6366f1] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">All</a>
      <a href="?type=deposit" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='deposit' ? 'bg-[#10b981] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Deposits</a>
      <a href="?type=purchase" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='purchase' ? 'bg-[#ef4444] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Purchases</a>
      <a href="?type=refund" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='refund' ? 'bg-[#06b6d4] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Refunds</a>
      <a href="?type=transfer" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='transfer' ? 'bg-[#8b5cf6] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Transfers</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead><tr class="bg-gradient-to-r from-indigo-50 to-violet-50">
            <th class="py-2.5 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Type</th>
            <th class="py-2.5 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Amount</th>
            <th class="py-2.5 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Balance</th>
            <th class="py-2.5 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Description</th>
            <th class="py-2.5 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Status</th>
            <th class="py-2.5 px-4 font-semibold text-[10px] text-indigo-700 uppercase text-right">Date</th>
          </tr></thead>
          <tbody class="divide-y divide-slate-100">
            <?php if(empty($transactions)): ?>
              <tr><td colspan="6" class="py-10 text-center text-slate-400 text-[11px]">No transactions found</td></tr>
            <?php else: foreach($transactions as $t):
              $income = in_array($t['type'], ['deposit','refund','admin_credit']);
              $stCls = $t['status']=='completed'?'bg-emerald-500':($t['status']=='pending'?'bg-amber-500':'bg-rose-500');
            ?>
            <tr class="hover:bg-slate-50/60 transition-colors">
              <td class="py-3 px-4">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full <?= $income ? 'bg-emerald-500' : 'bg-rose-500' ?> text-white flex items-center justify-center">
                    <?php if($income): ?>
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7" stroke-linecap="round" stroke-width="2.5"></path></svg>
                    <?php else: ?>
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v14M19 12l-7 7-7-7" stroke-linecap="round" stroke-width="2.5"></path></svg>
                    <?php endif; ?>
                  </div>
                  <span class="font-semibold text-slate-800 text-[11px] capitalize"><?= str_replace('_',' ',$t['type']) ?></span>
                </div>
              </td>
              <td class="py-3 px-4 font-bold text-[11px] <?= $income ? 'text-emerald-600' : 'text-rose-600' ?>">
                <?= $income ? '+' : '-' ?> PKR <?= number_format($t['amount'], 2) ?>
              </td>
              <td class="py-3 px-4 text-slate-600 text-[11px]">PKR <?= number_format($t['new_balance'], 2) ?></td>
              <td class="py-3 px-4 text-slate-500 text-[10px]"><?= htmlspecialchars(substr($t['description'], 0, 40)) ?></td>
              <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold text-white <?= $stCls ?>"><?= ucfirst($t['status']) ?></span></td>
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