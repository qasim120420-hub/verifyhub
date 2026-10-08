<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $name = sanitize($_POST['name']);
    $icon = sanitize($_POST['icon']);
    $price = (float)$_POST['price'];
    if (empty($name) || $price <= 0) $error = "Name and valid price required.";
    else {
        $stmt = $pdo->prepare("INSERT INTO services (name, icon, price) VALUES (?,?,?)");
        if ($stmt->execute([$name, $icon, $price])) $msg = "Service added successfully!";
        else $error = "Failed to add service.";
    }
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE services SET status = IF(status='active','inactive','active') WHERE id = ?")->execute([$id]);
    $msg = "Service status updated.";
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([(int)$_GET['delete']]);
    $msg = "Service deleted.";
}

$services = $pdo->query("SELECT * FROM services ORDER BY created_at DESC")->fetchAll();
$total = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$active = $pdo->query("SELECT COUNT(*) FROM services WHERE status='active'")->fetchColumn();
$inactive = $pdo->query("SELECT COUNT(*) FROM services WHERE status='inactive'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Manage Services - <?= $theme['site_name'] ?></title>
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
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Manage Services</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Configure available services and pricing.</p>
      </div>

      <div class="grid grid-cols-3 gap-3">
        <div class="bg-[#ec4899] p-4 rounded-xl shadow-lg shadow-pink-500/30">
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

        <!-- Add Service -->
        <div class="bg-gradient-to-br from-[#ec4899] to-[#f472b6] p-5 rounded-xl text-white shadow-lg shadow-pink-500/30">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-width="2.5"></path></svg>
            </div>
            <h3 class="text-sm font-bold">Add Service</h3>
          </div>
          <form method="POST" class="space-y-3">
            <div>
              <label class="block text-[11px] font-semibold text-white/80 mb-1">Service Name</label>
              <input type="text" name="name" required class="w-full px-3 py-2 text-xs rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50" placeholder="e.g. WhatsApp">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-white/80 mb-1">Icon (emoji or short)</label>
              <input type="text" name="icon" class="w-full px-3 py-2 text-xs rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50" placeholder="💬">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-white/80 mb-1">Price (PKR)</label>
              <input type="number" name="price" step="0.01" min="1" required class="w-full px-3 py-2 text-xs rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50" placeholder="150.00">
            </div>
            <button type="submit" name="add_service" class="w-full bg-white text-[#ec4899] font-bold text-xs py-2.5 rounded-lg shadow-lg">Add Service</button>
          </form>
        </div>

        <!-- Services List -->
        <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#8b5cf6] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7l3-7z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">All Services</h3>
          </div>

          <?php if(empty($services)): ?>
            <p class="text-center text-[11px] text-slate-400 py-8">No services yet</p>
          <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[600px] overflow-y-auto pr-1">
              <?php foreach($services as $s):
                $stCls = $s['status']=='active' ? 'bg-emerald-500' : 'bg-rose-500';
              ?>
              <div class="p-3 rounded-lg border border-slate-200 hover:border-pink-300 transition">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-pink-500 to-violet-600 flex items-center justify-center text-white text-base"><?= $s['icon'] ?? '•' ?></div>
                    <div>
                      <p class="text-[12px] font-bold text-slate-800"><?= htmlspecialchars($s['name']) ?></p>
                      <p class="text-[10px] font-bold text-emerald-600">PKR <?= number_format($s['price'], 2) ?></p>
                    </div>
                  </div>
                  <span class="px-2 py-0.5 rounded text-[9px] font-bold text-white uppercase <?= $stCls ?>"><?= $s['status'] ?></span>
                </div>
                <div class="flex gap-1.5">
                  <a href="?toggle=<?= $s['id'] ?>" class="flex-1 bg-[#f59e0b] text-white text-[10px] font-bold py-1 rounded-md text-center">Toggle</a>
                  <a href="?delete=<?= $s['id'] ?>" onclick="return confirm('Delete this service?')" class="flex-1 bg-[#ef4444] text-white text-[10px] font-bold py-1 rounded-md text-center">Delete</a>
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