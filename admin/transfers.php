<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$search = sanitize($_GET['search'] ?? '');

$sql = "SELECT t.*, u1.username as sender_username, u1.full_name as sender_name, 
        u2.username as receiver_username, u2.full_name as receiver_name
        FROM wallet_transactions t 
        JOIN users u1 ON t.user_id = u1.id
        LEFT JOIN users u2 ON t.description LIKE CONCAT('%', u2.username, '%')
        WHERE t.type = 'transfer'";
$params = [];
if ($search) {
    $sql .= " AND (u1.username LIKE ? OR u1.email LIKE ? OR t.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY t.created_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transfers = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM wallet_transactions WHERE type='transfer'")->fetchColumn();
$total_amount = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE type='transfer'")->fetchColumn();
$today = $pdo->query("SELECT COUNT(*) FROM wallet_transactions WHERE type='transfer' AND DATE(created_at)=CURDATE()")->fetchColumn();
$today_amount = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE type='transfer' AND DATE(created_at)=CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Transfers - <?= $theme['site_name'] ?></title>
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
  <form method="GET" class="flex-1 max-w-xl mx-4 hidden md:block">
    <div class="relative">
      <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-width="2"></path></svg></span>
      <input name="search" value="<?= htmlspecialchars($search) ?>" class="w-full pl-10 pr-4 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs placeholder-slate-400 focus:outline-none focus:border-brand-purple" placeholder="Search transfers..." type="text"/>
    </div>
  </form>
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
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">All Transfers</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">User-to-user wallet transfers history.</p>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-[#8b5cf6] p-4 rounded-xl shadow-lg shadow-violet-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Transfers</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($total) ?></h3>
        </div>
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Amount</p>
          <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($total_amount, 0) ?></h3>
        </div>
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Today</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($today) ?></h3>
        </div>
        <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Today's Volume</p>
          <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($today_amount, 0) ?></h3>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead><tr class="bg-gradient-to-r from-indigo-50 to-violet-50">
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">ID</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Sender</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Amount</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Description</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Status</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase text-right">Date</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
              <?php if(empty($transfers)): ?>
                <tr><td colspan="6" class="py-10 text-center text-slate-400 text-[11px]">No transfers found</td></tr>
              <?php else: foreach($transfers as $t):
                $stCls = $t['status']=='completed' ? 'bg-emerald-500' : 'bg-amber-500';
              ?>
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3 px-4 text-slate-500 text-[11px]">#<?= $t['id'] ?></td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#8b5cf6] to-[#6366f1] text-white flex items-center justify-center font-bold text-[10px]">
                      <?= strtoupper(substr($t['sender_name'] ?: $t['sender_username'], 0, 1)) ?>
                    </div>
                    <div>
                      <p class="font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($t['sender_name'] ?: $t['sender_username']) ?></p>
                      <p class="text-[10px] text-slate-400">@<?= htmlspecialchars($t['sender_username']) ?></p>
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4 font-bold text-rose-600 text-[11px]">-PKR <?= number_format($t['amount'], 2) ?></td>
                <td class="py-3 px-4 text-slate-500 text-[10px]"><?= htmlspecialchars(substr($t['description'], 0, 50)) ?></td>
                <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold text-white uppercase <?= $stCls ?>"><?= $t['status'] ?></span></td>
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