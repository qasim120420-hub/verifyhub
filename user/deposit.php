<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $user_id);

$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)$_POST['amount'];
    $method = sanitize($_POST['method']);
    $txn_id = sanitize($_POST['transaction_id']);
    if ($amount < 100) $error = "Minimum deposit is PKR 100.";
    elseif (empty($txn_id)) $error = "Transaction ID required.";
    elseif (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== 0) $error = "Please upload a payment receipt.";
    else {
        $allowed = ['jpg','jpeg','png','webp','pdf'];
        $ext = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) $error = "Only JPG, PNG, WEBP, PDF allowed.";
        elseif ($_FILES['receipt']['size'] > 5*1024*1024) $error = "Max 5MB allowed.";
        else {
            $fn = 'receipt_'.$user_id.'_'.time().'.'.$ext;
            $path = '../assets/uploads/receipts/'.$fn;
            if (!is_dir('../assets/uploads/receipts/')) mkdir('../assets/uploads/receipts/', 0777, true);
            if (move_uploaded_file($_FILES['receipt']['tmp_name'], $path)) {
                $stmt = $pdo->prepare("INSERT INTO deposits (user_id, amount, method, transaction_id, receipt_image) VALUES (?,?,?,?,?)");
                if ($stmt->execute([$user_id, $amount, $method, $txn_id, $fn])) {
                    try { addNotification($pdo, $user_id, "Deposit Submitted", "Your deposit of PKR ".number_format($amount,2)." is pending."); } catch (Exception $e) {}
                    $message = "Deposit submitted successfully!";
                } else $error = "Failed to save deposit.";
            } else $error = "Upload failed.";
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM deposits WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user_id]); $deposits = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Deposit Funds - <?= $theme['site_name'] ?></title>
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
        <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full" placeholder="Search..." type="text"/>
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-width="2"></path></svg></span>
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
      <h1 class="text-lg font-bold text-slate-900 tracking-tight">Deposit Funds</h1>
      <p class="text-[11px] text-slate-500 mt-0.5">Add money to your wallet by uploading payment proof.</p>
    </div>

    <?php if($message): ?>
      <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= $message ?></div>
    <?php endif; ?>
    <?php if($error): ?>
      <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Form -->
      <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 mb-4">Make a Deposit</h2>

        <div class="bg-gradient-to-br from-[#6366f1] to-[#8b5cf6] p-4 rounded-xl text-white mb-4">
          <p class="text-[10px] uppercase tracking-wider opacity-80 font-semibold mb-2">Payment Instructions</p>
          <div class="space-y-1 text-[11px]">
            <p><span class="opacity-70">Bank:</span> <strong>Meezan Bank</strong></p>
            <p><span class="opacity-70">Account:</span> <strong>0123-4567890123</strong></p>
            <p><span class="opacity-70">Title:</span> <strong><?= $theme['site_name'] ?> Pvt Ltd</strong></p>
          </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-3">
          <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Amount (PKR)</label>
            <input type="number" name="amount" min="100" step="50" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter amount">
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Payment Method</label>
            <select name="method" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <option value="">Select method</option>
              <option value="Bank Transfer">Bank Transfer</option>
              <option value="EasyPaisa">EasyPaisa</option>
              <option value="JazzCash">JazzCash</option>
              <option value="USDT">USDT (Crypto)</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Transaction / Reference ID</label>
            <input type="text" name="transaction_id" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g. TRX123456789">
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Payment Receipt (JPG, PNG, PDF — Max 5MB)</label>
            <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.webp,.pdf" required class="w-full text-xs">
          </div>
          <button type="submit" class="w-full bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white font-bold py-2.5 rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-xl transition-all text-xs">
            Submit Deposit
          </button>
        </form>
      </div>

      <!-- History -->
      <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 mb-4">Recent Deposits</h2>
        <?php if(empty($deposits)): ?>
          <div class="text-center py-8">
            <div class="text-3xl mb-2">📭</div>
            <p class="text-[11px] text-slate-400">No deposits yet</p>
          </div>
        <?php else: ?>
          <div class="space-y-2">
            <?php foreach($deposits as $d):
              $stCls = $d['status']=='approved'?'bg-emerald-500':($d['status']=='rejected'?'bg-rose-500':'bg-amber-500');
            ?>
            <div class="p-3 rounded-lg border border-slate-200 bg-gradient-to-br from-white to-slate-50">
              <div class="flex justify-between items-center mb-1">
                <strong class="text-slate-800 text-xs">PKR <?= number_format($d['amount'], 2) ?></strong>
                <span class="px-2 py-0.5 rounded text-[9px] font-bold text-white <?= $stCls ?> uppercase"><?= $d['status'] ?></span>
              </div>
              <p class="text-[10px] text-slate-500"><?= $d['method'] ?> · <?= $d['transaction_id'] ?></p>
              <p class="text-[9px] text-slate-400 mt-1"><?= date('M j, Y', strtotime($d['created_at'])) ?></p>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

</body>
</html>