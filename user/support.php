<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $user_id);

$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    $subject = sanitize($_POST['subject']);
    $category = sanitize($_POST['category']);
    $priority = sanitize($_POST['priority']);
    $message = sanitize($_POST['message']);
    if (empty($subject) || empty($message)) $error = "Subject and message are required.";
    else {
        $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, category, priority) VALUES (?,?,?,?)");
        if ($stmt->execute([$user_id, $subject, $category, $priority])) {
            $tid = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO support_messages (ticket_id, sender_id, message) VALUES (?,?,?)")->execute([$tid, $user_id, $message]);
            try { addNotification($pdo, $user_id, "Ticket Created", "Support ticket #$tid created."); } catch (Exception $e) {}
            $msg = "Ticket #$tid submitted successfully!";
        } else $error = "Failed to create ticket.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]); $tickets = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE user_id=?"); $stmt->execute([$user_id]); $total = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE user_id=? AND status='open'"); $stmt->execute([$user_id]); $open = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE user_id=? AND status='resolved'"); $stmt->execute([$user_id]); $resolved = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Support - <?= $theme['site_name'] ?></title>
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
        <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full" placeholder="Search tickets..." type="text"/>
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
      <h1 class="text-lg font-bold text-slate-900 tracking-tight">Support Tickets</h1>
      <p class="text-[11px] text-slate-500 mt-0.5">Get help from our support team.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Tickets</p>
        <h3 class="text-2xl font-bold text-white mt-1"><?= $total ?></h3>
      </div>
      <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Open</p>
        <h3 class="text-2xl font-bold text-white mt-1"><?= $open ?></h3>
      </div>
      <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Resolved</p>
        <h3 class="text-2xl font-bold text-white mt-1"><?= $resolved ?></h3>
      </div>
    </div>

    <?php if($msg): ?>
      <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if($error): ?>
      <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

      <!-- Create Ticket -->
      <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-7 h-7 rounded-lg bg-[#6366f1] flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-width="2.5"></path></svg>
          </div>
          <h3 class="text-sm font-bold text-slate-800">Create New Ticket</h3>
        </div>
        <form method="POST" class="space-y-3">
          <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Subject</label>
            <input type="text" name="subject" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Brief summary">
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Category</label>
              <select name="category" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="Account">Account</option>
                <option value="Wallet">Wallet</option>
                <option value="Deposit">Deposit</option>
                <option value="Order">Order</option>
                <option value="OTP">OTP</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Priority</label>
              <select name="priority" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Message</label>
            <textarea name="message" rows="4" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Describe your issue..."></textarea>
          </div>
          <button type="submit" name="create_ticket" class="w-full bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white font-bold text-xs py-2.5 rounded-lg shadow-md shadow-indigo-500/30 hover:shadow-lg transition-all">Submit Ticket</button>
        </form>
      </div>

      <!-- Tickets List -->
      <div class="lg:col-span-3 bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-[#10b981] flex items-center justify-center">
              <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">My Tickets</h3>
          </div>
          <span class="text-[10px] font-semibold bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full"><?= count($tickets) ?> total</span>
        </div>

        <?php if(empty($tickets)): ?>
          <div class="text-center py-8">
            <div class="w-14 h-14 mx-auto rounded-full bg-indigo-50 flex items-center justify-center mb-3">
              <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636" stroke-linecap="round" stroke-width="2"></path><circle cx="12" cy="12" r="9" stroke-width="2"></circle></svg>
            </div>
            <p class="text-[11px] text-slate-400">No tickets yet</p>
          </div>
        <?php else: ?>
          <div class="space-y-2 max-h-[500px] overflow-y-auto pr-1">
            <?php foreach($tickets as $t):
              $stCls = $t['status']=='open'?'bg-blue-500':($t['status']=='resolved'?'bg-emerald-500':($t['status']=='closed'?'bg-slate-400':'bg-amber-500'));
              $prCls = $t['priority']=='high'?'bg-rose-100 text-rose-700':($t['priority']=='low'?'bg-slate-100 text-slate-600':'bg-amber-100 text-amber-700');
            ?>
            <div class="p-3 rounded-lg border border-slate-200 hover:border-indigo-300 hover:shadow-sm transition-all">
              <div class="flex items-center justify-between gap-2 mb-1.5">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="text-[10px] font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded">#<?= $t['id'] ?></span>
                  <span class="px-2 py-0.5 rounded text-[10px] font-semibold text-white uppercase <?= $stCls ?>"><?= str_replace('_',' ',$t['status']) ?></span>
                  <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= $prCls ?> capitalize"><?= $t['priority'] ?></span>
                </div>
                <span class="text-[10px] text-slate-400"><?= date('M j, Y', strtotime($t['created_at'])) ?></span>
              </div>
              <p class="text-[12px] font-bold text-slate-800"><?= htmlspecialchars($t['subject']) ?></p>
              <p class="text-[10px] text-slate-500 mt-0.5">Category: <?= htmlspecialchars($t['category']) ?></p>
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