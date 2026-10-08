<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_broadcast'])) {
    $title = sanitize($_POST['title']);
    $message = sanitize($_POST['message']);
    $target = $_POST['target'] ?? 'all';
    if (empty($title) || empty($message)) $msg = "Title and message required.";
    else {
        if ($target === 'all') {
            $users = $pdo->query("SELECT id FROM users WHERE role='user' AND status='active'")->fetchAll();
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$target, $target]);
            $users = $stmt->fetchAll();
        }
        $count = 0;
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?,?,?)");
        foreach($users as $u) {
            $stmt->execute([$u['id'], $title, $message]);
            $count++;
        }
        $msg = "Notification sent to $count user(s)!";
    }
}

$stmt = $pdo->query("SELECT n.*, u.username FROM notifications n JOIN users u ON n.user_id = u.id WHERE u.role='user' ORDER BY n.created_at DESC LIMIT 50");
$recent = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
$unread = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read=0")->fetchColumn();
$today = $pdo->query("SELECT COUNT(*) FROM notifications WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$user_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user' AND status='active'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Notifications - <?= $theme['site_name'] ?></title>
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
    <div class="max-w-6xl mx-auto space-y-4">

      <div>
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Notifications</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Send broadcast or targeted notifications to users.</p>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Sent</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($total) ?></h3>
        </div>
        <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Unread</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($unread) ?></h3>
        </div>
        <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Today</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($today) ?></h3>
        </div>
        <div class="bg-[#8b5cf6] p-4 rounded-xl shadow-lg shadow-violet-500/30">
          <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Active Users</p>
          <h3 class="text-xl font-bold text-white mt-1"><?= number_format($user_count) ?></h3>
        </div>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Send Form -->
        <div class="lg:col-span-1 bg-gradient-to-br from-[#6366f1] to-[#8b5cf6] p-5 rounded-xl text-white shadow-lg shadow-indigo-500/30">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold">Send Notification</h3>
          </div>
          <form method="POST" class="space-y-3">
            <div>
              <label class="block text-[11px] font-semibold text-white/80 mb-1">Target</label>
              <select name="target" class="w-full px-3 py-2 text-xs rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-white/50">
                <option value="all" class="text-slate-800">All Active Users</option>
                <option value="custom" class="text-slate-800">Specific User (type below)</option>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-white/80 mb-1">Username / Email (if specific)</label>
              <input type="text" name="target" placeholder="username or email" class="w-full px-3 py-2 text-xs rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-white/80 mb-1">Title</label>
              <input type="text" name="title" required class="w-full px-3 py-2 text-xs rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50" placeholder="Notification title">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-white/80 mb-1">Message</label>
              <textarea name="message" rows="4" required class="w-full px-3 py-2 text-xs rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 resize-none" placeholder="Your message..."></textarea>
            </div>
            <button type="submit" name="send_broadcast" class="w-full bg-white text-[#6366f1] font-bold text-xs py-2.5 rounded-lg shadow-lg hover:shadow-xl transition-all">
              Send Notification
            </button>
          </form>
        </div>

        <!-- Recent Notifications -->
        <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#10b981] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Recent Notifications</h3>
          </div>

          <?php if(empty($recent)): ?>
            <div class="text-center py-10">
              <div class="w-14 h-14 mx-auto rounded-full bg-indigo-50 flex items-center justify-center mb-3">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" stroke-linecap="round" stroke-width="2"></path></svg>
              </div>
              <p class="text-[11px] text-slate-400">No notifications sent yet</p>
            </div>
          <?php else: ?>
            <div class="space-y-2 max-h-[500px] overflow-y-auto pr-1">
              <?php foreach($recent as $n): ?>
              <div class="p-3 rounded-lg border border-slate-200 hover:border-indigo-300 transition">
                <div class="flex items-center justify-between gap-2 mb-1">
                  <span class="text-[10px] font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded">@<?= htmlspecialchars($n['username']) ?></span>
                  <span class="text-[10px] text-slate-400"><?= date('M j, h:i A', strtotime($n['created_at'])) ?></span>
                </div>
                <p class="text-[12px] font-bold text-slate-800"><?= htmlspecialchars($n['title']) ?></p>
                <p class="text-[10px] text-slate-500 mt-0.5 line-clamp-2"><?= htmlspecialchars($n['message']) ?></p>
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