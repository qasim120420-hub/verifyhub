<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $user_id);

$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver = sanitize($_POST['receiver']);
    $amount = (float)$_POST['amount'];
    $note = sanitize($_POST['note'] ?? '');
    if ($amount < 1) $error = "Minimum transfer is PKR 1.";
    elseif ($amount > $balance) $error = "Insufficient balance.";
    else {
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE (username = ? OR email = ?) AND id != ? AND role='user'");
        $stmt->execute([$receiver, $receiver, $user_id]);
        $recv = $stmt->fetch();
        if (!$recv) $error = "User not found.";
        else {
            try {
                $pdo->beginTransaction();
                $newSender = $balance - $amount;
                $pdo->prepare("UPDATE wallets SET balance = ? WHERE user_id = ?")->execute([$newSender, $user_id]);
                $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?")->execute([$amount, $recv['id']]);

                $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
                $stmt->execute([$recv['id']]);
                $newRecv = $stmt->fetchColumn();

                $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, previous_balance, new_balance, description, status) VALUES (?, 'transfer', ?, ?, ?, ?, 'completed')")
                    ->execute([$user_id, $amount, $balance, $newSender, "Transfer to @{$recv['username']} — $note"]);
                $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, previous_balance, new_balance, description, status) VALUES (?, 'deposit', ?, ?, ?, ?, 'completed')")
                    ->execute([$recv['id'], $amount, $newRecv - $amount, $newRecv, "Transfer from @{$user_full_name} — $note"]);

                try {
                    addNotification($pdo, $user_id, "Transfer Sent", "You sent PKR ".number_format($amount,2)." to @{$recv['username']}.");
                    addNotification($pdo, $recv['id'], "Transfer Received", "You received PKR ".number_format($amount,2)." from @{$user_full_name}.");
                } catch (Exception $e) {}

                $pdo->commit();
                $msg = "PKR ".number_format($amount, 2)." transferred to @{$recv['username']}!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Transfer failed.";
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? AND type='transfer' ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user_id]); $transfers = $stmt->fetchAll();

$balance = getWalletBalance($pdo, $user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Transfer - <?= $theme['site_name'] ?></title>
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
        <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full" placeholder="Search..." type="text"/>
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
      <h1 class="text-lg font-bold text-slate-900 tracking-tight">Transfer Funds</h1>
      <p class="text-[11px] text-slate-500 mt-0.5">Send money to another user instantly.</p>
    </div>

    <?php if($msg): ?>
      <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if($error): ?>
      <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

      <div class="lg:col-span-2 bg-gradient-to-br from-[#8b5cf6] to-[#6366f1] p-6 rounded-xl text-white shadow-lg shadow-violet-500/30">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-9 h-9 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" stroke-linecap="round" stroke-width="2.5"></path></svg>
          </div>
          <h3 class="text-base font-bold">Send Money</h3>
        </div>

        <form method="POST" class="space-y-3">
          <div>
            <label class="block text-[11px] font-semibold text-white/80 mb-1">Receiver Username or Email</label>
            <input type="text" name="receiver" required class="w-full px-3 py-2.5 text-xs rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50" placeholder="Enter username or email">
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-white/80 mb-1">Amount (PKR)</label>
            <input type="number" name="amount" min="1" step="0.01" required class="w-full px-3 py-2.5 text-xs rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50" placeholder="Enter amount">
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-white/80 mb-1">Note (optional)</label>
            <input type="text" name="note" class="w-full px-3 py-2.5 text-xs rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50" placeholder="Reason for transfer">
          </div>
          <button type="submit" class="w-full bg-white text-[#6366f1] font-bold text-xs py-3 rounded-lg shadow-lg hover:shadow-xl transition-all">
            Send PKR
          </button>
        </form>
      </div>

      <!-- Recent Transfers -->
      <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-7 h-7 rounded-lg bg-[#10b981] flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" stroke-linecap="round" stroke-width="2"></path></svg>
          </div>
          <h3 class="text-sm font-bold text-slate-800">Recent Transfers</h3>
        </div>
        <?php if(empty($transfers)): ?>
          <div class="text-center py-8">
            <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-2">
              <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <p class="text-[11px] text-slate-400">No transfers yet</p>
          </div>
        <?php else: ?>
          <div class="space-y-2">
            <?php foreach($transfers as $t): ?>
            <div class="p-3 rounded-lg border border-slate-200 bg-gradient-to-br from-violet-50 to-white">
              <div class="flex justify-between items-center mb-1">
                <span class="text-[11px] font-bold text-slate-800">PKR <?= number_format($t['amount'], 2) ?></span>
                <span class="px-2 py-0.5 rounded text-[9px] font-bold text-white bg-emerald-500 uppercase"><?= $t['status'] ?></span>
              </div>
              <p class="text-[10px] text-slate-500 line-clamp-2"><?= htmlspecialchars($t['description']) ?></p>
              <p class="text-[9px] text-slate-400 mt-1"><?= date('M j, Y', strtotime($t['created_at'])) ?></p>
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