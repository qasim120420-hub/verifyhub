<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $user_id);

$filter = sanitize($_GET['type'] ?? '');
$sql = "SELECT n.*, c.name as country, c.flag_icon, s.name as service FROM numbers n LEFT JOIN countries c ON n.country_id = c.id LEFT JOIN services s ON n.service_id = s.id WHERE n.assigned_to = ?";
$params = [$user_id];
if ($filter === 'virtual' || $filter === 'physical') { $sql .= " AND n.type = ?"; $params[] = $filter; }
$sql .= " ORDER BY n.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$numbers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>My Numbers - <?= $theme['site_name'] ?></title>
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
        <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full focus:outline-none focus:ring-1 focus:ring-indigo-500" placeholder="Search numbers..." type="text"/>
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
    <div class="flex items-center justify-between flex-wrap gap-3">
      <div>
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">My Numbers</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">All numbers assigned to your account.</p>
      </div>
      <div class="flex gap-2">
        <a href="my-numbers.php" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition <?= $filter==='' ? 'bg-[#6366f1] text-white shadow-md shadow-indigo-500/30' : 'bg-white border border-slate-200 text-slate-600 hover:border-indigo-300' ?>">All</a>
        <a href="?type=virtual" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition <?= $filter==='virtual' ? 'bg-[#8b5cf6] text-white shadow-md shadow-violet-500/30' : 'bg-white border border-slate-200 text-slate-600 hover:border-violet-300' ?>">Virtual</a>
        <a href="?type=physical" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition <?= $filter==='physical' ? 'bg-[#ec4899] text-white shadow-md shadow-pink-500/30' : 'bg-white border border-slate-200 text-slate-600 hover:border-pink-300' ?>">Physical</a>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Numbers</p>
        <h3 class="text-2xl font-bold text-white mt-1"><?= count($numbers) ?></h3>
      </div>
      <div class="bg-[#8b5cf6] p-4 rounded-xl shadow-lg shadow-violet-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Virtual</p>
        <h3 class="text-2xl font-bold text-white mt-1"><?= count(array_filter($numbers, fn($n) => $n['type'] === 'virtual')) ?></h3>
      </div>
      <div class="bg-[#ec4899] p-4 rounded-xl shadow-lg shadow-pink-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Physical</p>
        <h3 class="text-2xl font-bold text-white mt-1"><?= count(array_filter($numbers, fn($n) => $n['type'] === 'physical')) ?></h3>
      </div>
    </div>

    <!-- Numbers Grid -->
    <?php if(empty($numbers)): ?>
      <div class="bg-white p-10 rounded-xl border border-slate-200/70 shadow-sm text-center">
        <div class="text-5xl mb-3">📭</div>
        <h3 class="text-sm font-bold text-slate-500">No numbers purchased yet</h3>
        <a href="buy-number.php" class="inline-block mt-4 bg-[#6366f1] hover:bg-indigo-600 text-white text-xs font-bold px-5 py-2 rounded-lg shadow-md shadow-indigo-500/30">Buy a Number</a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php foreach($numbers as $n):
          $statusCls = $n['status']=='assigned' ? 'bg-emerald-500' : ($n['status']=='available'?'bg-amber-500':'bg-slate-400');
          $typeGrad = $n['type']=='physical' ? 'from-[#ec4899] to-[#f472b6]' : 'from-[#6366f1] to-[#8b5cf6]';
        ?>
        <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden hover:shadow-md transition">
          <div class="bg-gradient-to-r <?= $typeGrad ?> p-3 flex items-center justify-between">
            <span class="text-[10px] font-bold text-white uppercase tracking-wider"><?= ucfirst($n['type']) ?></span>
            <span class="text-[9px] font-bold text-white bg-white/25 px-2 py-0.5 rounded-full capitalize"><?= $n['status'] ?></span>
          </div>
          <div class="p-4">
            <p class="text-lg font-bold text-slate-800 font-mono"><?= $n['number'] ?></p>
            <p class="text-[11px] text-slate-500 mt-1"><?= $n['flag_icon'] ?> <?= $n['country'] ?> · <?= $n['service'] ?></p>
            <div class="mt-3 pt-3 border-t border-slate-100 flex justify-between text-[10px]">
              <span class="text-slate-400">Price: <strong class="text-slate-700">PKR <?= number_format($n['price'], 2) ?></strong></span>
              <span class="text-slate-400"><?= date('M j, Y', strtotime($n['created_at'])) ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

</body>
</html>