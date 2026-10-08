<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $tid = (int)$_POST['ticket_id'];
    $message = sanitize($_POST['message']);
    $status = sanitize($_POST['status']);
    $pdo->prepare("INSERT INTO support_messages (ticket_id, sender_id, message) VALUES (?,?,?)")->execute([$tid, $_SESSION['user_id'], $message]);
    $pdo->prepare("UPDATE support_tickets SET status = ? WHERE id = ?")->execute([$status, $tid]);
    $stmt = $pdo->prepare("SELECT user_id FROM support_tickets WHERE id = ?");
    $stmt->execute([$tid]);
    $uid = $stmt->fetchColumn();
    try { addNotification($pdo, $uid, "Support Reply", "Admin replied to ticket #$tid."); } catch (Exception $e) {}
    $msg = "Reply sent!";
}

$status_filter = sanitize($_GET['status'] ?? '');
$sql = "SELECT t.*, u.username, u.full_name FROM support_tickets t JOIN users u ON t.user_id = u.id WHERE 1=1";
$params = [];
if ($status_filter) { $sql .= " AND t.status = ?"; $params[] = $status_filter; }
$sql .= " ORDER BY t.created_at DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM support_tickets")->fetchColumn();
$open = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status='open'")->fetchColumn();
$progress = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status='in_progress'")->fetchColumn();
$resolved = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status='resolved'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Support Tickets - <?= $theme['site_name'] ?></title>
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
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Support Tickets</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Respond to user inquiries and manage support workflow.</p>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($total) ?></h3>
        </div>
        <div class="bg-[#3b82f6] p-4 rounded-xl shadow-lg shadow-blue-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Open</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($open) ?></h3>
        </div>
        <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">In Progress</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($progress) ?></h3>
        </div>
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Resolved</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($resolved) ?></h3>
        </div>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <div class="flex gap-2 flex-wrap">
        <a href="support-tickets.php" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='' ? 'bg-[#6366f1] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">All</a>
        <a href="?status=open" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='open' ? 'bg-[#3b82f6] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Open</a>
        <a href="?status=in_progress" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='in_progress' ? 'bg-[#f59e0b] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">In Progress</a>
        <a href="?status=resolved" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='resolved' ? 'bg-[#10b981] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Resolved</a>
        <a href="?status=closed" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold <?= $status_filter==='closed' ? 'bg-slate-500 text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600' ?>">Closed</a>
      </div>

      <?php if(empty($tickets)): ?>
        <div class="bg-white p-10 rounded-xl border border-slate-200/70 shadow-sm text-center">
          <div class="w-14 h-14 mx-auto rounded-full bg-indigo-50 flex items-center justify-center mb-3">
            <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536" stroke-linecap="round" stroke-width="2"></path><circle cx="12" cy="12" r="9" stroke-width="2"></circle></svg>
          </div>
          <p class="text-[12px] font-bold text-slate-500">No tickets found</p>
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach($tickets as $t):
            $stCls = $t['status']=='open'?'bg-blue-500':($t['status']=='in_progress'?'bg-amber-500':($t['status']=='resolved'?'bg-emerald-500':'bg-slate-500'));
            $prCls = $t['priority']=='high'?'bg-rose-100 text-rose-700':($t['priority']=='low'?'bg-slate-100 text-slate-600':'bg-amber-100 text-amber-700');
          ?>
          <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100">
              <div class="flex items-start justify-between gap-3 flex-wrap">
                <div class="flex items-start gap-3">
                  <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#6366f1] to-[#8b5cf6] flex items-center justify-center text-white font-bold text-sm">
                    <?= strtoupper(substr($t['full_name'] ?: $t['username'], 0, 1)) ?>
                  </div>
                  <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                      <span class="text-[10px] font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded">#<?= $t['id'] ?></span>
                      <span class="px-2 py-0.5 rounded text-[10px] font-semibold text-white uppercase <?= $stCls ?>"><?= str_replace('_',' ',$t['status']) ?></span>
                      <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= $prCls ?> capitalize"><?= $t['priority'] ?></span>
                    </div>
                    <p class="text-[12px] font-bold text-slate-800"><?= htmlspecialchars($t['subject']) ?></p>
                    <p class="text-[10px] text-slate-500 mt-0.5">From: <?= htmlspecialchars($t['full_name'] ?: $t['username']) ?> · <?= htmlspecialchars($t['category']) ?> · <?= date('M j, Y h:i A', strtotime($t['created_at'])) ?></p>
                  </div>
                </div>
              </div>
            </div>

            <form method="POST" class="p-4 flex flex-col sm:flex-row gap-2">
              <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
              <input type="text" name="message" placeholder="Type your reply..." required class="flex-1 px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <select name="status" class="px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
              </select>
              <button type="submit" name="reply" class="bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white font-bold text-xs px-4 py-2 rounded-lg shadow-md shadow-indigo-500/30">Reply</button>
            </form>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>

</body>
</html>