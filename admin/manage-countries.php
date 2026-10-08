<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_country'])) {
    $name = sanitize($_POST['name']);
    $code = sanitize($_POST['code']);
    $flag = sanitize($_POST['flag_icon']);
    if (empty($name) || empty($code)) $error = "Name and code are required.";
    else {
        $stmt = $pdo->prepare("INSERT INTO countries (name, code, flag_icon) VALUES (?,?,?)");
        if ($stmt->execute([$name, $code, $flag])) $msg = "Country added successfully!";
        else $error = "Failed to add country.";
    }
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE countries SET status = IF(status='active','inactive','active') WHERE id = ?")->execute([$id]);
    $msg = "Country status updated.";
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM countries WHERE id = ?")->execute([(int)$_GET['delete']]);
    $msg = "Country deleted.";
}

$countries = $pdo->query("SELECT * FROM countries ORDER BY created_at DESC")->fetchAll();
$total = $pdo->query("SELECT COUNT(*) FROM countries")->fetchColumn();
$active = $pdo->query("SELECT COUNT(*) FROM countries WHERE status='active'")->fetchColumn();
$inactive = $pdo->query("SELECT COUNT(*) FROM countries WHERE status='inactive'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Manage Countries - <?= $theme['site_name'] ?></title>
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
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Manage Countries</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Add and manage countries for number inventory.</p>
      </div>

      <div class="grid grid-cols-3 gap-3">
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($total) ?></h3>
        </div>
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Active</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($active) ?></h3>
        </div>
        <div class="bg-[#ef4444] p-4 rounded-xl shadow-lg shadow-rose-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Inactive</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($inactive) ?></h3>
        </div>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>
      <?php if($error): ?>
        <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Add Country Form -->
        <div class="bg-gradient-to-br from-[#6366f1] to-[#8b5cf6] p-5 rounded-xl text-white shadow-lg shadow-indigo-500/30">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-width="2.5"></path></svg>
            </div>
            <h3 class="text-sm font-bold">Add Country</h3>
          </div>
          <form method="POST" class="space-y-3">
            <div>
              <label class="block text-[11px] font-semibold text-white/80 mb-1">Country Name</label>
              <input type="text" name="name" required class="w-full px-3 py-2 text-xs rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50" placeholder="e.g. Pakistan">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-white/80 mb-1">Country Code</label>
              <input type="text" name="code" required maxlength="3" class="w-full px-3 py-2 text-xs rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 uppercase" placeholder="PK">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-white/80 mb-1">Flag Emoji</label>
              <input type="text" name="flag_icon" class="w-full px-3 py-2 text-xs rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50" placeholder="🇵🇰">
            </div>
            <button type="submit" name="add_country" class="w-full bg-white text-[#6366f1] font-bold text-xs py-2.5 rounded-lg shadow-lg">Add Country</button>
          </form>
        </div>

        <!-- Countries List -->
        <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#10b981] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"></circle><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">All Countries</h3>
          </div>

          <?php if(empty($countries)): ?>
            <p class="text-center text-[11px] text-slate-400 py-8">No countries yet</p>
          <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[600px] overflow-y-auto pr-1">
              <?php foreach($countries as $c):
                $stCls = $c['status']=='active' ? 'bg-emerald-500' : 'bg-rose-500';
              ?>
              <div class="p-3 rounded-lg border border-slate-200 hover:border-indigo-300 transition">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <span class="text-xl"><?= htmlspecialchars($c['flag_icon']) ?></span>
                    <div>
                      <p class="text-[12px] font-bold text-slate-800"><?= htmlspecialchars($c['name']) ?></p>
                      <p class="text-[10px] font-mono text-slate-500"><?= htmlspecialchars($c['code']) ?></p>
                    </div>
                  </div>
                  <span class="px-2 py-0.5 rounded text-[9px] font-bold text-white uppercase <?= $stCls ?>"><?= $c['status'] ?></span>
                </div>
                <div class="flex gap-1.5">
                  <a href="?toggle=<?= $c['id'] ?>" class="flex-1 bg-[#f59e0b] text-white text-[10px] font-bold py-1 rounded-md text-center">Toggle</a>
                  <a href="?delete=<?= $c['id'] ?>" onclick="return confirm('Delete this country?')" class="flex-1 bg-[#ef4444] text-white text-[10px] font-bold py-1 rounded-md text-center">Delete</a>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </main>
</div>

</body>
</html>