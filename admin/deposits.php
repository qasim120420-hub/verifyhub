<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['deposit_id'])) {
    $did = (int)$_POST['deposit_id'];
    $action = $_POST['action'];
    $remarks = sanitize($_POST['remarks'] ?? '');
    $stmt = $pdo->prepare("SELECT * FROM deposits WHERE id = ? AND status = 'pending'");
    $stmt->execute([$did]);
    $dep = $stmt->fetch();
    if ($dep) {
        try {
            $pdo->beginTransaction();
            if ($action === 'approve') {
                $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
                $stmt->execute([$dep['user_id']]);
                $old = (float)$stmt->fetchColumn();
                $new = $old + $dep['amount'];
                $pdo->prepare("UPDATE wallets SET balance = ?, total_deposited = total_deposited + ? WHERE user_id = ?")->execute([$new, $dep['amount'], $dep['user_id']]);
                $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, previous_balance, new_balance, description, status) VALUES (?, 'deposit', ?, ?, ?, 'Deposit approved', 'completed')")
                    ->execute([$dep['user_id'], $dep['amount'], $old, $new]);
                $pdo->prepare("UPDATE deposits SET status='approved', admin_remarks=? WHERE id=?")->execute([$remarks, $did]);
                try { addNotification($pdo, $dep['user_id'], "Deposit Approved", "Your deposit of PKR ".number_format($dep['amount'],2)." was approved."); } catch (Exception $e) {}
                $msg = "Deposit approved and wallet credited!";
            } else {
                $pdo->prepare("UPDATE deposits SET status='rejected', admin_remarks=? WHERE id=?")->execute([$remarks, $did]);
                try { addNotification($pdo, $dep['user_id'], "Deposit Rejected", "Reason: $remarks"); } catch (Exception $e) {}
                $msg = "Deposit rejected.";
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Error processing deposit.";
        }
    }
}

$status_filter = sanitize($_GET['status'] ?? '');
$sql = "SELECT d.*, u.username, u.full_name, u.email FROM deposits d JOIN users u ON d.user_id = u.id WHERE 1=1";
$params = [];
if ($status_filter) { $sql .= " AND d.status = ?"; $params[] = $status_filter; }
$sql .= " ORDER BY d.created_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$deposits = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM deposits")->fetchColumn();
$pending = $pdo->query("SELECT COUNT(*) FROM deposits WHERE status='pending'")->fetchColumn();
$approved = $pdo->query("SELECT COUNT(*) FROM deposits WHERE status='approved'")->fetchColumn();
$rejected = $pdo->query("SELECT COUNT(*) FROM deposits WHERE status='rejected'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Deposits - <?= $theme['site_name'] ?></title>
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
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Deposit Management</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Approve or reject user deposits with payment proof.</p>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($total) ?></h3>
        </div>
        <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Pending</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($pending) ?></h3>
        </div>
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Approved</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($approved) ?></h3>
        </div>
        <div class="bg-[#ef4444] p-4 rounded-xl shadow-lg shadow-rose-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Rejected</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($rejected) ?></h3>
        </div>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <div class="flex gap-2 flex-wrap">
        <a href="deposits.php" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='' ? 'bg-[#6366f1] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">All</a>
        <a href="?status=pending" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='pending' ? 'bg-[#f59e0b] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Pending</a>
        <a href="?status=approved" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='approved' ? 'bg-[#10b981] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Approved</a>
        <a href="?status=rejected" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='rejected' ? 'bg-[#ef4444] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Rejected</a>
      </div>

      <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead><tr class="bg-gradient-to-r from-indigo-50 to-violet-50">
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">ID</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">User</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Amount</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Method</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">TXN ID</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Receipt</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Status</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
              <?php if(empty($deposits)): ?>
                <tr><td colspan="8" class="py-10 text-center text-slate-400 text-[11px]">No deposits found</td></tr>
              <?php else: foreach($deposits as $d):
                $stCls = $d['status']=='approved'?'bg-emerald-500':($d['status']=='rejected'?'bg-rose-500':'bg-amber-500');
              ?>
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3 px-4 font-bold text-indigo-600 text-[11px]">#<?= $d['id'] ?></td>
                <td class="py-3 px-4">
                  <p class="font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($d['full_name'] ?: $d['username']) ?></p>
                  <p class="text-[10px] text-slate-400">@<?= htmlspecialchars($d['username']) ?></p>
                </td>
                <td class="py-3 px-4 font-bold text-emerald-600 text-[11px]">PKR <?= number_format($d['amount'], 2) ?></td>
                <td class="py-3 px-4 text-slate-600 text-[11px]"><?= htmlspecialchars($d['method']) ?></td>
                <td class="py-3 px-4 text-slate-500 text-[10px] font-mono"><?= htmlspecialchars($d['transaction_id']) ?></td>
                <td class="py-3 px-4">
                  <?php if($d['receipt_image']): ?>
                    <a href="../assets/uploads/receipts/<?= $d['receipt_image'] ?>" target="_blank" class="inline-flex items-center gap-1 bg-[#6366f1] text-white text-[10px] font-bold px-2 py-1 rounded-md">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-width="2"></path><path d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" stroke-linecap="round" stroke-width="2"></path></svg>
                      View
                    </a>
                  <?php else: ?><span class="text-slate-400">—</span><?php endif; ?>
                </td>
                <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold text-white uppercase <?= $stCls ?>"><?= $d['status'] ?></span></td>
                <td class="py-3 px-4 text-right">
                  <?php if($d['status'] === 'pending'): ?>
                    <div class="flex items-center justify-end gap-1.5">
                      <form method="POST" class="inline">
                        <input type="hidden" name="deposit_id" value="<?= $d['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" onclick="return confirm('Approve this deposit?')" class="bg-[#10b981] text-white text-[10px] font-bold px-2.5 py-1 rounded-md">Approve</button>
                      </form>
                      <form method="POST" class="inline">
                        <input type="hidden" name="deposit_id" value="<?= $d['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="remarks" value="Payment not verified">
                        <button type="submit" onclick="return confirm('Reject this deposit?')" class="bg-[#ef4444] text-white text-[10px] font-bold px-2.5 py-1 rounded-md">Reject</button>
                      </form>
                    </div>
                  <?php else: ?>
                    <span class="text-[10px] text-slate-400">Handled</span>
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