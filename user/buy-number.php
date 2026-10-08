<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');

$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $user_id);

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_purchase'])) {
    $number_id = (int)$_POST['number_id'];
    $stmt = $pdo->prepare("SELECT n.*, c.name as country, s.name as service FROM numbers n LEFT JOIN countries c ON n.country_id = c.id LEFT JOIN services s ON n.service_id = s.id WHERE n.id = ? AND n.status = 'available'");
    $stmt->execute([$number_id]);
    $number = $stmt->fetch();
    if (!$number) { $error = "This number is no longer available."; }
    else {
        $bal = getWalletBalance($pdo, $user_id);
        if ($bal < $number['price']) { $error = "Insufficient wallet balance."; }
        else {
            try {
                $pdo->beginTransaction();
                $new_bal = $bal - $number['price'];
                $pdo->prepare("UPDATE wallets SET balance = ?, total_spent = total_spent + ? WHERE user_id = ?")->execute([$new_bal, $number['price'], $user_id]);
                $pdo->prepare("UPDATE numbers SET status = 'assigned', assigned_to = ? WHERE id = ?")->execute([$user_id, $number_id]);
                $pdo->prepare("INSERT INTO orders (user_id, number_id, amount, status, expires_at) VALUES (?, ?, ?, 'waiting_otp', DATE_ADD(NOW(), INTERVAL 20 MINUTE))")->execute([$user_id, $number_id, $number['price']]);
                $order_id = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, previous_balance, new_balance, description, status) VALUES (?, 'purchase', ?, ?, ?, ?, 'completed')")->execute([$user_id, $number['price'], $bal, $new_bal, "Purchase of number {$number['number']}"]);
                try { addNotification($pdo, $user_id, "Order Placed", "Order #$order_id placed."); } catch (Exception $e) {}
                $pdo->commit();
                redirect('active-orders.php?success=1');
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Something went wrong.";
            }
        }
    }
}

$countries = $pdo->query("SELECT * FROM countries WHERE status='active'")->fetchAll();
$services = $pdo->query("SELECT * FROM services WHERE status='active'")->fetchAll();
$selected_country = isset($_GET['country']) ? (int)$_GET['country'] : 0;
$selected_service = isset($_GET['service']) ? (int)$_GET['service'] : 0;
$selected_type = isset($_GET['type']) ? sanitize($_GET['type']) : '';

$numbers = [];
if ($selected_country && $selected_service && $selected_type) {
    $stmt = $pdo->prepare("SELECT n.*, c.name as country, c.flag_icon, s.name as service FROM numbers n JOIN countries c ON n.country_id = c.id JOIN services s ON n.service_id = s.id WHERE n.status='available' AND n.country_id=? AND n.service_id=? AND n.type=?");
    $stmt->execute([$selected_country, $selected_service, $selected_type]);
    $numbers = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Buy Number - <?= $theme['site_name'] ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<script>
tailwind.config = { theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"','sans-serif'] } } } }
</script>
<style>
  ::-webkit-scrollbar { width: 5px; height: 5px; }
  ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
  .sidebar-scroll::-webkit-scrollbar-thumb { background: #2d3056; }
</style>
</head>
<body class="bg-[#f4f6fc] text-slate-800 antialiased font-sans flex h-screen overflow-hidden text-sm">

<?php include '../includes/user_sidebar.php'; ?>

<div class="flex-1 flex flex-col h-screen overflow-hidden bg-[#f5f7fc]">
  <header class="h-14 bg-white border-b border-slate-200/80 px-5 flex items-center justify-between flex-shrink-0 z-10">
    <div class="flex items-center gap-3 w-80">
      <button class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-width="2"></path></svg></button>
      <div class="relative w-full">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-width="2"></path></svg></span>
        <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full focus:outline-none focus:ring-1 focus:ring-indigo-500 text-slate-700 placeholder-slate-400" placeholder="Search..." type="text"/>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <div class="flex items-center bg-[#f1f5f9] border border-slate-200 rounded-full px-3 py-1 gap-2 text-xs font-semibold text-slate-700">
        <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-600 flex items-center justify-center text-[10px]">₨</span>
        <span>PKR <?= number_format($balance, 2) ?></span>
      </div>
      <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
        <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-[11px]"><?= $user_initial ?></div>
        <span class="text-xs font-semibold text-slate-800"><?= htmlspecialchars($user_full_name) ?></span>
      </div>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-5 space-y-4">
    <div>
      <h1 class="text-lg font-bold text-slate-900 tracking-tight">Buy Number</h1>
      <p class="text-[11px] text-slate-500 mt-0.5">Follow the steps to purchase your verification number.</p>
    </div>

    <!-- Colorful Step Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/70 shadow-sm">
      <div class="flex items-center justify-between gap-2">
        <?php
        $steps = [
          1 => ['type' => 'Type', 'color' => 'indigo'],
          2 => ['type' => 'Country', 'color' => 'pink'],
          3 => ['type' => 'Service', 'color' => 'cyan'],
          4 => ['type' => 'Number', 'color' => 'emerald'],
          5 => ['type' => 'Confirm', 'color' => 'amber']
        ];
        foreach ($steps as $i => $s):
          $isActive = ($step == $i);
          $isDone = ($step > $i);
        ?>
        <div class="flex-1 flex items-center gap-2">
          <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-bold flex-shrink-0 transition-all
            <?= $isActive ? 'bg-'.$s['color'].'-500 text-white shadow-lg scale-110' : ($isDone ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400') ?>">
            <?= $isDone ? '✓' : $i ?>
          </div>
          <span class="text-[11px] font-semibold <?= $isActive ? 'text-'.$s['color'].'-700' : 'text-slate-400' ?> hidden sm:block"><?= $s['type'] ?></span>
          <?php if ($i < 5): ?>
            <div class="flex-1 h-0.5 <?= $isDone ? 'bg-emerald-500' : 'bg-slate-200' ?> rounded"></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if($error): ?>
      <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- STEP 1: TYPE -->
    <?php if($step == 1): ?>
      <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 mb-4">Select Number Type</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <a href="?step=2&type=virtual" class="group bg-gradient-to-br from-[#6366f1] to-[#8b5cf6] p-6 rounded-xl text-white shadow-lg shadow-indigo-500/30 hover:shadow-xl transition-all text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mb-3">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/></svg>
            </div>
            <h3 class="text-base font-bold">Virtual Number</h3>
            <p class="text-[11px] text-white/80 mt-1">Instant online verification</p>
          </a>
          <a href="?step=2&type=physical" class="group bg-gradient-to-br from-[#ec4899] to-[#f472b6] p-6 rounded-xl text-white shadow-lg shadow-pink-500/30 hover:shadow-xl transition-all text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mb-3">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M12 12h.01"/></svg>
            </div>
            <h3 class="text-base font-bold">Physical SIM</h3>
            <p class="text-[11px] text-white/80 mt-1">Real SIM delivered to you</p>
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- STEP 2: COUNTRY -->
    <?php if($step == 2): ?>
      <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 mb-4">Select Country</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
          <?php foreach($countries as $c): ?>
            <a href="?step=3&type=<?= $selected_type ?>&country=<?= $c['id'] ?>" class="bg-gradient-to-br from-white to-slate-50 border-2 border-slate-200 hover:border-[#6366f1] p-4 rounded-xl text-center transition-all hover:shadow-lg">
              <div class="text-2xl mb-2"><?= $c['flag_icon'] ?></div>
              <p class="text-[12px] font-bold text-slate-800"><?= $c['name'] ?></p>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- STEP 3: SERVICE -->
    <?php if($step == 3): ?>
      <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 mb-4">Select Service</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
          <?php foreach($services as $s): ?>
            <a href="?step=4&type=<?= $selected_type ?>&country=<?= $selected_country ?>&service=<?= $s['id'] ?>" class="bg-gradient-to-br from-[#06b6d4] to-[#22d3ee] p-4 rounded-xl text-center text-white shadow-lg shadow-cyan-500/30 hover:shadow-xl transition-all">
              <div class="text-2xl mb-1"><?= $s['icon'] ?? '🔧' ?></div>
              <p class="text-[12px] font-bold"><?= $s['name'] ?></p>
              <p class="text-[10px] opacity-90 mt-1">PKR <?= number_format($s['price'], 2) ?></p>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- STEP 4: NUMBER -->
    <?php if($step == 4): ?>
      <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 mb-4">Available Numbers</h2>
        <?php if(empty($numbers)): ?>
          <div class="text-center py-10">
            <div class="text-4xl mb-3">📭</div>
            <h3 class="text-sm font-bold text-slate-500">No numbers available</h3>
            <a href="buy-number.php" class="inline-block mt-3 bg-[#6366f1] text-white text-xs font-semibold px-4 py-2 rounded-lg">Start Over</a>
          </div>
        <?php else: ?>
          <div class="space-y-2">
            <?php foreach($numbers as $n): ?>
              <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/30 transition-all">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-lg bg-emerald-500 text-white flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-linecap="round" stroke-width="2"></path></svg>
                  </div>
                  <div>
                    <p class="font-bold text-slate-800 text-sm"><?= $n['number'] ?></p>
                    <p class="text-[10px] text-slate-500"><?= $n['flag_icon'] ?> <?= $n['country'] ?> · <?= $n['service'] ?></p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-bold text-[#10b981] text-sm mb-1">PKR <?= number_format($n['price'], 2) ?></p>
                  <a href="?step=5&type=<?= $selected_type ?>&country=<?= $selected_country ?>&service=<?= $selected_service ?>&number_id=<?= $n['id'] ?>" class="inline-block bg-[#10b981] hover:bg-emerald-600 text-white text-[11px] font-semibold px-3 py-1 rounded-md transition">Select</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- STEP 5: CONFIRM -->
    <?php if($step == 5 && isset($_GET['number_id'])):
      $nid = (int)$_GET['number_id'];
      $stmt = $pdo->prepare("SELECT n.*, c.name as country, c.flag_icon, s.name as service FROM numbers n JOIN countries c ON n.country_id = c.id JOIN services s ON n.service_id = s.id WHERE n.id = ?");
      $stmt->execute([$nid]);
      $n = $stmt->fetch();
    ?>
      <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 mb-4">Confirm Purchase</h2>
        <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-4 rounded-xl border border-amber-100 mb-4">
          <div class="grid grid-cols-2 gap-3 text-xs">
            <div><p class="text-slate-500 text-[10px] mb-0.5">Number</p><p class="font-bold text-slate-800"><?= $n['number'] ?></p></div>
            <div><p class="text-slate-500 text-[10px] mb-0.5">Type</p><p class="font-bold text-slate-800 capitalize"><?= $n['type'] ?></p></div>
            <div><p class="text-slate-500 text-[10px] mb-0.5">Country</p><p class="font-bold text-slate-800"><?= $n['flag_icon'] ?> <?= $n['country'] ?></p></div>
            <div><p class="text-slate-500 text-[10px] mb-0.5">Service</p><p class="font-bold text-slate-800"><?= $n['service'] ?></p></div>
          </div>
        </div>
        <div class="space-y-2 mb-4">
          <div class="flex justify-between text-xs"><span class="text-slate-500">Price</span><span class="font-bold">PKR <?= number_format($n['price'], 2) ?></span></div>
          <div class="flex justify-between text-xs"><span class="text-slate-500">Your Balance</span><span class="font-bold">PKR <?= number_format($balance, 2) ?></span></div>
          <div class="flex justify-between pt-2 border-t border-slate-200">
            <span class="font-bold text-slate-700">Remaining</span>
            <span class="font-bold <?= ($balance - $n['price']) >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">PKR <?= number_format($balance - $n['price'], 2) ?></span>
          </div>
        </div>
        <form method="POST">
          <input type="hidden" name="number_id" value="<?= $n['id'] ?>">
          <button type="submit" name="confirm_purchase" class="w-full bg-gradient-to-r from-[#10b981] to-[#059669] text-white font-bold py-3 rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-xl transition-all disabled:opacity-50" <?= $balance < $n['price'] ? 'disabled' : '' ?>>
            <?= $balance < $n['price'] ? 'Insufficient Balance' : 'Confirm Purchase' ?>
          </button>
        </form>
      </div>
    <?php endif; ?>

  </main>
</div>

</body>
</html>