<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = '';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM numbers WHERE id = ?")->execute([(int)$_GET['delete']]);
    $msg = "Number deleted.";
}
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE numbers SET status = IF(status='available','disabled','available') WHERE id = ? AND status != 'assigned'")->execute([$id]);
    $msg = "Status updated.";
}

$status_filter = sanitize($_GET['status'] ?? '');
$type_filter = sanitize($_GET['type'] ?? '');

$sql = "SELECT n.*, c.name as country, c.flag_icon, s.name as service FROM numbers n LEFT JOIN countries c ON n.country_id = c.id LEFT JOIN services s ON n.service_id = s.id WHERE 1=1";
$params = [];
if ($status_filter) { $sql .= " AND n.status = ?"; $params[] = $status_filter; }
if ($type_filter) { $sql .= " AND n.type = ?"; $params[] = $type_filter; }
$sql .= " ORDER BY n.created_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$numbers = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM numbers")->fetchColumn();
$available = $pdo->query("SELECT COUNT(*) FROM numbers WHERE status='available'")->fetchColumn();
$assigned = $pdo->query("SELECT COUNT(*) FROM numbers WHERE status='assigned'")->fetchColumn();
$disabled = $pdo->query("SELECT COUNT(*) FROM numbers WHERE status='disabled'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Manage Numbers - <?= $theme['site_name'] ?></title>
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
          <h1 class="text-lg font-bold text-slate-900 tracking-tight">Number Inventory</h1>
          <p class="text-[11px] text-slate-500 mt-0.5">Manage all virtual and physical numbers.</p>
        </div>
        <a href="add-number.php" class="bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white text-xs font-bold px-4 py-2.5 rounded-lg shadow-md shadow-indigo-500/30 hover:shadow-lg transition-all flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-width="2.5"></path></svg>
          Add Number
        </a>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($total) ?></h3>
        </div>
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Available</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($available) ?></h3>
        </div>
        <div class="bg-[#3b82f6] p-4 rounded-xl shadow-lg shadow-blue-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Assigned</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($assigned) ?></h3>
        </div>
        <div class="bg-[#ef4444] p-4 rounded-xl shadow-lg shadow-rose-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Disabled</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($disabled) ?></h3>
        </div>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <div class="flex gap-2 flex-wrap">
        <a href="manage-numbers.php" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='' && $type_filter==='' ? 'bg-[#6366f1] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">All</a>
        <a href="?status=available" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='available' ? 'bg-[#10b981] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Available</a>
        <a href="?status=assigned" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='assigned' ? 'bg-[#3b82f6] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Assigned</a>
        <a href="?status=disabled" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='disabled' ? 'bg-[#ef4444] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Disabled</a>
        <a href="?type=virtual" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='virtual' ? 'bg-[#8b5cf6] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Virtual</a>
        <a href="?type=physical" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $type_filter==='physical' ? 'bg-[#ec4899] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Physical</a>
      </div>

      <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead><tr class="bg-gradient-to-r from-indigo-50 to-violet-50">
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">ID</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Number</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Type</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Country</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Service</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Price</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase">Status</th>
              <th class="py-3 px-4 font-semibold text-[10px] text-indigo-700 uppercase text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
              <?php if(empty($numbers)): ?>
                <tr><td colspan="8" class="py-10 text-center text-slate-400 text-[11px]">No numbers found</td></tr>
              <?php else: foreach($numbers as $n):
                $stCls = $n['status']=='available'?'bg-emerald-500':($n['status']=='assigned'?'bg-blue-500':'bg-rose-500');
              ?>
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3 px-4 text-slate-500 text-[11px]">#<?= $n['id'] ?></td>
                <td class="py-3 px-4 font-bold text-slate-800 font-mono text-[11px]"><?= $n['number'] ?></td>
                <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $n['type']=='virtual'?'bg-violet-100 text-violet-700':'bg-pink-100 text-pink-700' ?> uppercase"><?= $n['type'] ?></span></td>
                <td class="py-3 px-4 text-slate-600 text-[11px]"><?= $n['flag_icon'] ?> <?= $n['country'] ?></td>
                <td class="py-3 px-4 text-slate-600 text-[11px]"><?= $n['service'] ?></td>
                <td class="py-3 px-4 font-bold text-emerald-600 text-[11px]">PKR <?= number_format($n['price'], 2) ?></td>
                <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold text-white uppercase <?= $stCls ?>"><?= $n['status'] ?></span></td>
                <td class="py-3 px-4 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <?php if($n['status'] != 'assigned'): ?>
                      <a href="?toggle=<?= $n['id'] ?>" class="bg-[#f59e0b] text-white text-[10px] font-bold px-2 py-1 rounded-md">Toggle</a>
                    <?php endif; ?>
                    <a href="?delete=<?= $n['id'] ?>" onclick="return confirm('Delete this number?')" class="bg-[#ef4444] text-white text-[10px] font-bold px-2 py-1 rounded-md">Delete</a>
                  </div>
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