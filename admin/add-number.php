<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number = sanitize($_POST['number']);
    $type = sanitize($_POST['type']);
    $country_id = (int)$_POST['country_id'];
    $service_id = (int)$_POST['service_id'];
    $price = (float)$_POST['price'];
    if (empty($number) || !$country_id || !$service_id || $price <= 0) $error = "All fields are required.";
    else {
        $stmt = $pdo->prepare("INSERT INTO numbers (number, type, country_id, service_id, price) VALUES (?,?,?,?,?)");
        if ($stmt->execute([$number, $type, $country_id, $service_id, $price])) $msg = "Number added successfully!";
        else $error = "Failed to add number.";
    }
}

$countries = $pdo->query("SELECT * FROM countries WHERE status='active'")->fetchAll();
$services = $pdo->query("SELECT * FROM services WHERE status='active'")->fetchAll();
$total = $pdo->query("SELECT COUNT(*) FROM numbers")->fetchColumn();
$today = $pdo->query("SELECT COUNT(*) FROM numbers WHERE DATE(created_at)=CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Add Number - <?= $theme['site_name'] ?></title>
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
    <div class="max-w-4xl mx-auto space-y-4">

      <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h1 class="text-lg font-bold text-slate-900 tracking-tight">Add New Number</h1>
          <p class="text-[11px] text-slate-500 mt-0.5">Add a new virtual or physical number to inventory.</p>
        </div>
        <a href="manage-numbers.php" class="bg-white border border-slate-200 text-slate-700 text-xs font-bold px-4 py-2 rounded-lg hover:bg-slate-100 transition flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-width="2.5"></path></svg>
          Back to Inventory
        </a>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Numbers</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($total) ?></h3>
        </div>
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Added Today</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($today) ?></h3>
        </div>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>
      <?php if($error): ?>
        <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-8 h-8 rounded-lg bg-[#6366f1] flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-width="2.5"></path></svg>
          </div>
          <h3 class="text-sm font-bold text-slate-800">Number Details</h3>
        </div>

        <form method="POST" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Number (with country code)</label>
              <input type="text" name="number" required class="w-full px-3 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono" placeholder="+923001234567">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Type</label>
              <select name="type" required class="w-full px-3 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="virtual">Virtual</option>
                <option value="physical">Physical</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Country</label>
              <select name="country_id" required class="w-full px-3 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Select country</option>
                <?php foreach($countries as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= $c['flag_icon'] ?> <?= $c['name'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Service</label>
              <select name="service_id" required class="w-full px-3 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Select service</option>
                <?php foreach($services as $s): ?>
                  <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Price (PKR)</label>
              <input type="number" name="price" step="0.01" min="1" required class="w-full px-3 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="100.00">
            </div>
          </div>

          <button type="submit" class="w-full bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white font-bold text-xs py-3 rounded-lg shadow-lg shadow-indigo-500/30 hover:shadow-xl transition-all">
            Add Number to Inventory
          </button>
        </form>
      </div>

      <div class="bg-gradient-to-br from-[#8b5cf6] to-[#6366f1] p-5 rounded-xl text-white shadow-lg shadow-violet-500/30">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
          <div>
            <h4 class="text-sm font-bold mb-1">Quick Tip</h4>
            <p class="text-[11px] opacity-90 leading-relaxed">Numbers added here become immediately available for purchase. Include full country code (+92 for Pakistan, +1 for USA) to avoid issues.</p>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

</body>
</html>