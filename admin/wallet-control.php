<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wallet_action'])) {
    $uid = (int)$_POST['user_id'];
    $amt = (float)$_POST['amount'];
    $action = $_POST['wallet_action'];
    $reason = sanitize($_POST['reason']);
    if ($amt <= 0 || empty($reason)) $error = "Amount and reason required.";
    else {
        $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
        $stmt->execute([$uid]);
        $old = (float)$stmt->fetchColumn();
        $new = $action === 'credit' ? $old + $amt : $old - $amt;
        if ($new < 0) $error = "Insufficient balance for debit.";
        else {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE wallets SET balance = ? WHERE user_id = ?")->execute([$new, $uid]);
            $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, previous_balance, new_balance, description, status) VALUES (?, ?, ?, ?, ?, ?, 'completed')")
                ->execute([$uid, $action === 'credit' ? 'admin_credit' : 'admin_debit', $amt, $old, $new, "Admin: $reason"]);
            try { addNotification($pdo, $uid, "Wallet ".ucfirst($action), "PKR ".number_format($amt,2)." $action by admin."); } catch (Exception $e) {}
            $pdo->commit();
            $msg = "Wallet {$action}ed successfully!";
        }
    }
}

$search = sanitize($_GET['search'] ?? '');
$sql = "SELECT u.id, u.username, u.full_name, u.email, u.status, w.balance FROM users u LEFT JOIN wallets w ON u.id = w.user_id WHERE u.role='user'";
$params = [];
if ($search) { $sql .= " AND (u.username LIKE ? OR u.email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY w.balance DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$total_balance = $pdo->query("SELECT COALESCE(SUM(balance),0) FROM wallets")->fetchColumn();
$total_credit = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE type='admin_credit'")->fetchColumn();
$total_debit = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE type='admin_debit'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Wallet Control - <?= $theme['site_name'] ?></title>
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
      <input name="search" value="<?= htmlspecialchars($search) ?>" class="w-full pl-10 pr-4 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs placeholder-slate-400 focus:outline-none focus:border-brand-purple" placeholder="Search users by username or email..." type="text"/>
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
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Wallet Control</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Manually credit or debit user wallets.</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Wallet Balance</p>
              <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($total_balance, 0) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
          </div>
        </div>
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Admin Credits</p>
              <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($total_credit, 0) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7" stroke-linecap="round" stroke-width="2.5"></path></svg>
            </div>
          </div>
        </div>
        <div class="bg-[#ef4444] p-4 rounded-xl shadow-lg shadow-rose-500/30">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Admin Debits</p>
              <h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($total_debit, 0) ?></h3>
            </div>
            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v14M19 12l-7 7-7-7" stroke-linecap="round" stroke-width="2.5"></path></svg>
            </div>
          </div>
        </div>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>
      <?php if($error): ?>
        <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-[#6366f1] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">User Wallets</h3>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead><tr class="bg-gradient-to-r from-indigo-50 to-violet-50">
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">User</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Email</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Balance</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Status</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase text-right">Action</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
              <?php if(empty($users)): ?>
                <tr><td colspan="5" class="py-10 text-center text-slate-400 text-[11px]">No users found</td></tr>
              <?php else: foreach($users as $u):
                $initial = strtoupper(substr($u['full_name'] ?: $u['username'], 0, 1));
                $stCls = $u['status']=='active' ? 'bg-emerald-500' : 'bg-rose-500';
              ?>
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3 px-4">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#6366f1] to-[#8b5cf6] text-white flex items-center justify-center font-bold text-[11px]"><?= $initial ?></div>
                    <div>
                      <p class="font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($u['full_name'] ?: $u['username']) ?></p>
                      <p class="text-[10px] text-slate-400">@<?= htmlspecialchars($u['username']) ?></p>
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4 text-slate-500 text-[11px]"><?= htmlspecialchars($u['email']) ?></td>
                <td class="py-3 px-4 font-bold text-emerald-600 text-[11px]">PKR <?= number_format($u['balance'] ?? 0, 2) ?></td>
                <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold text-white uppercase <?= $stCls ?>"><?= $u['status'] ?></span></td>
                <td class="py-3 px-4 text-right">
                  <button onclick="openWallet(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>')" class="bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white text-[10px] font-bold px-3 py-1.5 rounded-md hover:shadow-md transition">
                    Manage Wallet
                  </button>
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

<!-- Wallet Modal -->
<div id="walletModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-xl max-w-md w-full p-5">
    <div class="flex items-center gap-2 mb-4">
      <div class="w-8 h-8 rounded-lg bg-[#6366f1] flex items-center justify-center">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke-linecap="round" stroke-width="2"></path></svg>
      </div>
      <h3 class="text-sm font-bold">Wallet Action — <span id="modalUser" class="text-indigo-600"></span></h3>
    </div>
    <form method="POST" class="space-y-3">
      <input type="hidden" name="user_id" id="modalUserId">
      <div>
        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Action</label>
        <select name="wallet_action" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="credit">Credit (+)</option>
          <option value="debit">Debit (−)</option>
        </select>
      </div>
      <div>
        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Amount (PKR)</label>
        <input type="number" name="amount" min="1" step="0.01" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>
      <div>
        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Reason</label>
        <input type="text" name="reason" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>
      <div class="flex gap-2 pt-2">
        <button type="submit" class="flex-1 bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white font-bold text-xs py-2.5 rounded-lg shadow-md">Confirm</button>
        <button type="button" onclick="document.getElementById('walletModal').classList.add('hidden')" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-2.5 rounded-lg">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openWallet(id, username) {
  document.getElementById('modalUserId').value = id;
  document.getElementById('modalUser').textContent = '@' + username;
  document.getElementById('walletModal').classList.remove('hidden');
}
</script>

</body>
</html>