<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));
$balance = getWalletBalance($pdo, $user_id);

if (isset($_GET['mark_all'])) {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
    redirect('notifications.php');
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?")->execute([(int)$_GET['delete'], $user_id]);
    redirect('notifications.php');
}

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 100");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]); $unread = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Notifications - <?= $theme['site_name'] ?></title>
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
        <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full" placeholder="Search notifications..." type="text"/>
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
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Notifications</h1>
        <p class="text-[11px] text-slate-500 mt-0.5"><?= $unread ?> unread notification<?= $unread != 1 ? 's' : '' ?></p>
      </div>
      <?php if($unread > 0): ?>
      <a href="?mark_all=1" class="bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white text-[11px] font-bold px-4 py-2 rounded-lg shadow-md shadow-indigo-500/30 hover:shadow-lg transition-all flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-width="2"></path></svg>
        Mark All Read
      </a>
      <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div class="bg-[#6366f1] p-3.5 rounded-xl shadow-lg shadow-indigo-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total</p>
        <h3 class="text-xl font-bold text-white mt-1"><?= count($notifications) ?></h3>
      </div>
      <div class="bg-[#ef4444] p-3.5 rounded-xl shadow-lg shadow-rose-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Unread</p>
        <h3 class="text-xl font-bold text-white mt-1"><?= $unread ?></h3>
      </div>
      <div class="bg-[#10b981] p-3.5 rounded-xl shadow-lg shadow-emerald-500/30">
        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Read</p>
        <h3 class="text-xl font-bold text-white mt-1"><?= count($notifications) - $unread ?></h3>
      </div>
    </div>

    <?php if(empty($notifications)): ?>
      <div class="bg-white p-10 rounded-xl border border-slate-200/70 shadow-sm text-center">
        <div class="w-16 h-16 mx-auto rounded-full bg-indigo-50 flex items-center justify-center mb-3">
          <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke-linecap="round" stroke-width="2"></path></svg>
        </div>
        <h3 class="text-sm font-bold text-slate-500">No notifications yet</h3>
      </div>
    <?php else: ?>
      <div class="space-y-2">
        <?php foreach($notifications as $n): ?>
        <div class="bg-white rounded-xl border <?= $n['is_read'] ? 'border-slate-200/70' : 'border-indigo-300 bg-gradient-to-r from-indigo-50/50 to-white' ?> shadow-sm p-4 hover:shadow-md transition-all">
          <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg <?= $n['is_read'] ? 'bg-slate-100' : 'bg-gradient-to-br from-[#6366f1] to-[#8b5cf6]' ?> flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 <?= $n['is_read'] ? 'text-slate-400' : 'text-white' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h4 class="font-bold text-slate-800 text-[12px]"><?= htmlspecialchars($n['title']) ?></h4>
                  <p class="text-[11px] text-slate-600 mt-0.5"><?= htmlspecialchars($n['message']) ?></p>
                  <p class="text-[10px] text-slate-400 mt-1"><?= date('M j, Y h:i A', strtotime($n['created_at'])) ?></p>
                </div>
                <a href="?delete=<?= $n['id'] ?>" onclick="return confirm('Delete this notification?')" class="text-rose-500 hover:text-rose-700 flex-shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" stroke-linecap="round" stroke-width="2"></path></svg>
                </a>
              </div>
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