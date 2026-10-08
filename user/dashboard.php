<?php
require_once '../config/session.php';
if (!isLoggedIn()) redirect('../public/login.php');

$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_initial = strtoupper(substr($user_full_name, 0, 1));

$balance = getWalletBalance($pdo, $user_id);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status IN ('pending','waiting_otp','otp_received')");
$stmt->execute([$user_id]); $active_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM numbers WHERE assigned_to = ? AND type = 'virtual'");
$stmt->execute([$user_id]); $virtual_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM numbers WHERE assigned_to = ? AND type = 'physical'");
$stmt->execute([$user_id]); $physical_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM otp_messages om JOIN orders o ON om.order_id = o.id WHERE o.user_id = ?");
$stmt->execute([$user_id]); $otp_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT om.otp_code FROM otp_messages om JOIN orders o ON om.order_id = o.id WHERE o.user_id = ? ORDER BY om.received_at DESC LIMIT 1");
$stmt->execute([$user_id]); $latest_otp = $stmt->fetchColumn() ?: '—';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?"); $stmt->execute([$user_id]); $total_orders = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'completed'"); $stmt->execute([$user_id]); $completed_orders = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'pending'"); $stmt->execute([$user_id]); $pending_orders = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'cancelled'"); $stmt->execute([$user_id]); $cancelled_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT o.*, n.number, n.type FROM orders o JOIN numbers n ON o.number_id = n.id WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 3");
$stmt->execute([$user_id]); $recent_orders = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$user_id]); $recent_transactions = $stmt->fetchAll();

$chart_values = [];
$chart_labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('M j', strtotime($date));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE user_id=? AND type='deposit' AND DATE(created_at)=?");
    $stmt->execute([$user_id, $date]);
    $chart_values[] = (float)$stmt->fetchColumn();
}
$chart_max = max(array_merge($chart_values, [1]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Dashboard - <?= $theme['site_name'] ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
          colors: {
            brand: { 50:'#f5f3ff',100:'#ede9fe',500:'#6366f1',600:'#4f46e5',700:'#4338ca' }
          }
        }
      }
    }
</script>
<style>
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: rgba(15, 16, 38, 0.05); }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    .sidebar-scroll::-webkit-scrollbar-thumb { background: #2d3056; }
</style>
</head>
<body class="bg-[#f4f6fc] text-slate-800 antialiased font-sans flex h-screen overflow-hidden text-sm">

<?php include '../includes/user_sidebar.php'; ?>

<div class="flex-1 flex flex-col h-screen overflow-hidden bg-[#f5f7fc]">

    <!-- Top Bar -->
    <header class="h-14 bg-white border-b border-slate-200/80 px-5 flex items-center justify-between flex-shrink-0 z-10">
        <div class="flex items-center gap-3 w-80">
            <button class="text-slate-400 hover:text-slate-600" type="button">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
            </button>
            <div class="relative w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                </span>
                <input class="w-full pl-9 pr-4 py-1.5 bg-[#f8fafc] border border-slate-200 text-xs rounded-full focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:bg-white text-slate-700 placeholder-slate-400" placeholder="Search numbers, orders, etc..." type="text"/>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center bg-[#f1f5f9] border border-slate-200 rounded-full px-3 py-1 gap-2 text-xs font-semibold text-slate-700">
                <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-600 flex items-center justify-center text-[10px]">₨</span>
                <span>PKR <?= number_format($balance, 2) ?></span>
            </div>
            <button class="p-2 text-slate-500 hover:text-slate-700 rounded-full hover:bg-slate-100 relative" type="button">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
            </button>
            <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-[11px] ring-2 ring-indigo-100"><?= $user_initial ?></div>
                <span class="text-xs font-semibold text-slate-800"><?= htmlspecialchars($user_full_name) ?></span>
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
            </div>
        </div>
    </header>

    <!-- Body -->
    <main class="flex-1 overflow-y-auto p-5 space-y-4">

        <div>
            <h1 class="text-lg font-bold text-slate-900 tracking-tight">Dashboard</h1>
            <p class="text-[11px] text-slate-500 mt-0.5">Welcome back, <span class="font-medium text-slate-700"><?= htmlspecialchars($user_full_name) ?></span>! Here's what's happening with your account today.</p>
        </div>

        <!-- Row 1: Solid Colorful Cards -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

            <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Wallet Balance</span>
                    <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    </div>
                </div>
                <div class="mt-3"><span class="text-xl font-bold text-white tracking-tight">PKR <?= number_format($balance, 2) ?></span></div>
            </div>

            <div class="bg-[#ec4899] p-4 rounded-xl shadow-lg shadow-pink-500/30 hover:shadow-xl hover:shadow-pink-500/40 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Active Orders</span>
                    <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-xl font-bold text-white tracking-tight"><?= $active_orders ?></span>
                    <a class="text-[10px] font-semibold text-white hover:text-white flex items-center gap-0.5 bg-white/20 hover:bg-white/30 px-2 py-0.5 rounded-full transition" href="active-orders.php">View →</a>
                </div>
            </div>

            <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/40 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Available Numbers</span>
                    <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-3 text-xs">
                    <span class="font-bold text-white">V: <?= $virtual_count ?></span>
                    <span class="text-white/40">|</span>
                    <span class="font-bold text-white">P: <?= $physical_count ?></span>
                </div>
            </div>

            <div class="bg-[#8b5cf6] p-4 rounded-xl shadow-lg shadow-violet-500/30 hover:shadow-xl hover:shadow-violet-500/40 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">OTP Messages</span>
                    <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-xl font-bold text-white tracking-tight"><?= $otp_count ?></span>
                    <span class="text-[10px] font-medium text-white/90 flex items-center gap-1">Latest: <strong class="font-mono"><?= $latest_otp ?></strong></span>
                </div>
            </div>
        </section>

        <!-- Row 2 -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-3">

            <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Total Orders</p>
                        <h3 class="text-lg font-bold text-white"><?= $total_orders ?></h3>
                    </div>
                </div>
                <div class="text-[10px] text-white/95 space-x-1.5 font-semibold">
                    <span>C: <?= $completed_orders ?></span>
                    <span class="text-white/40">•</span>
                    <span>P: <?= $pending_orders ?></span>
                    <span class="text-white/40">•</span>
                    <span>X: <?= $cancelled_orders ?></span>
                </div>
            </div>

            <div class="bg-[#059669] p-4 rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Account Status</p>
                        <h3 class="text-base font-bold text-white">Active</h3>
                    </div>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/25 text-white backdrop-blur-sm">✓ Verified</span>
            </div>

        </section>

        <!-- Charts -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-3">

            <!-- Wallet Activity Chart -->
            <div class="bg-white p-4 rounded-xl border border-slate-200/70 shadow-sm lg:col-span-2 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-[#6366f1] flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 8-8" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path></svg>
                        </div>
                        <h3 class="text-xs font-bold text-slate-800">Wallet Activity</h3>
                    </div>
                    <button class="text-[11px] bg-indigo-50 border border-indigo-200 px-2 py-1 rounded-md font-medium text-indigo-700 flex items-center gap-1" type="button">Last 7 Days <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></button>
                </div>
                <div class="w-full h-40 relative flex flex-col justify-end">
                    <div class="absolute inset-0 flex flex-col justify-between pointer-events-none text-[9px] text-slate-300">
                        <div class="border-b border-slate-100 w-full flex justify-between pr-2"><span><?= number_format($chart_max/1000, 0) ?>k</span></div>
                        <div class="border-b border-slate-100 w-full flex justify-between pr-2"><span><?= number_format($chart_max*0.75/1000, 0) ?>k</span></div>
                        <div class="border-b border-slate-100 w-full flex justify-between pr-2"><span><?= number_format($chart_max*0.5/1000, 0) ?>k</span></div>
                        <div class="border-b border-slate-100 w-full flex justify-between pr-2"><span><?= number_format($chart_max*0.25/1000, 0) ?>k</span></div>
                        <div class="border-b border-slate-100 w-full flex justify-between pr-2"><span>0</span></div>
                    </div>
                    <svg class="w-full h-28 overflow-visible relative z-10" preserveAspectRatio="none" viewBox="0 0 500 120">
                        <defs><linearGradient id="purpleGrad" x1="0%" x2="0%" y1="0%" y2="100%"><stop offset="0%" stop-color="#6366f1" stop-opacity="0.35"></stop><stop offset="100%" stop-color="#6366f1" stop-opacity="0.0"></stop></linearGradient></defs>
                        <path d="M 0,95 Q 60,110 100,70 T 200,60 T 300,90 T 400,20 T 500,45 L 500,120 L 0,120 Z" fill="url(#purpleGrad)"></path>
                        <path d="M 0,95 Q 60,110 100,70 T 200,60 T 300,90 T 400,20 T 500,45" fill="none" stroke="#6366f1" stroke-linecap="round" stroke-width="2.5"></path>
                        <circle cx="100" cy="70" fill="#ffffff" r="3.5" stroke="#6366f1" stroke-width="2"></circle>
                        <circle cx="200" cy="60" fill="#ffffff" r="3.5" stroke="#6366f1" stroke-width="2"></circle>
                        <circle cx="300" cy="90" fill="#ffffff" r="3.5" stroke="#6366f1" stroke-width="2"></circle>
                        <circle cx="400" cy="20" fill="#6366f1" r="4.5" stroke="#ffffff" stroke-width="2"></circle>
                        <circle cx="500" cy="45" fill="#ffffff" r="3.5" stroke="#6366f1" stroke-width="2"></circle>
                    </svg>
                    <div class="flex justify-between text-[10px] text-slate-400 mt-2 px-2">
                        <?php foreach($chart_labels as $lbl): ?><span><?= $lbl ?></span><?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Orders by Status - Solid Purple -->
            <div class="bg-gradient-to-br from-[#8b5cf6] to-[#6366f1] p-4 rounded-xl shadow-lg shadow-violet-500/30 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-bold text-white">Orders by Status</h3>
                    <span class="text-[10px] font-semibold text-white/90 bg-white/20 px-2 py-0.5 rounded-full"><?= $total_orders ?> Total</span>
                </div>
                <div class="flex flex-col items-center justify-center my-auto py-2">
                    <div class="relative w-32 h-32 flex items-center justify-center">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-white/20" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4.5"></path>
                            <?php
                            $t = max($total_orders, 1);
                            $p1 = round(($completed_orders/$t)*100, 1);
                            $p2 = round(($pending_orders/$t)*100, 1);
                            $p3 = round(($cancelled_orders/$t)*100, 1);
                            ?>
                            <path class="text-emerald-300" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="<?= $p1 ?>, 100" stroke-linecap="round" stroke-width="4.5"></path>
                            <path class="text-amber-300" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="<?= $p2 ?>, 100" stroke-dashoffset="-<?= $p1 ?>" stroke-linecap="round" stroke-width="4.5"></path>
                            <path class="text-rose-300" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="<?= $p3 ?>, 100" stroke-dashoffset="-<?= $p1+$p2 ?>" stroke-linecap="round" stroke-width="4.5"></path>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                            <span class="text-xl font-bold text-white leading-none"><?= $total_orders ?></span>
                            <span class="text-[9px] uppercase tracking-wider text-white/70 font-medium mt-0.5">Total</span>
                        </div>
                    </div>
                    <div class="mt-3 w-full space-y-1.5 text-[11px] px-2">
                        <div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-white/90"><span class="w-2 h-2 rounded-full bg-emerald-300"></span> Completed</span><span class="font-bold text-white"><?= $completed_orders ?></span></div>
                        <div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-white/90"><span class="w-2 h-2 rounded-full bg-amber-300"></span> Pending</span><span class="font-bold text-white"><?= $pending_orders ?></span></div>
                        <div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-white/90"><span class="w-2 h-2 rounded-full bg-rose-300"></span> Canceled</span><span class="font-bold text-white"><?= $cancelled_orders ?></span></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tables -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-3">

            <div class="bg-white p-4 rounded-xl border border-slate-200/70 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-md bg-[#6366f1] flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        </div>
                        <h3 class="text-xs font-bold text-slate-800">Recent Orders</h3>
                    </div>
                    <a class="text-[11px] text-indigo-600 font-semibold hover:underline" href="active-orders.php">View All →</a>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    <?php if(empty($recent_orders)): ?>
                        <p class="py-6 text-center text-slate-400 text-[11px]">No orders yet</p>
                    <?php else: foreach($recent_orders as $o):
                        $st = $o['status'];
                        $cls = $st=='completed'?'bg-emerald-100 text-emerald-700':
                              ($st=='cancelled'?'bg-rose-100 text-rose-700':
                              ($st=='waiting_otp'?'bg-cyan-100 text-cyan-700':'bg-amber-100 text-amber-700'));
                        $sl = ucwords(str_replace('_',' ',$st));
                    ?>
                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-[10px]">#</div>
                            <div>
                                <p class="font-bold text-slate-800">#ORD-<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?></p>
                                <p class="text-[10px] text-slate-400"><?= ucfirst($o['type']) ?> · <?= substr($o['number'],0,8) ?>...</p>
                            </div>
                        </div>
                        <div class="text-right flex items-center gap-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= $cls ?>"><?= $sl ?></span>
                            <span class="text-[10px] text-slate-400"><?= date('M j, Y', strtotime($o['created_at'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200/70 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-md bg-[#10b981] flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        </div>
                        <h3 class="text-xs font-bold text-slate-800">Recent Transactions</h3>
                    </div>
                    <a class="text-[11px] text-indigo-600 font-semibold hover:underline" href="transactions.php">View All →</a>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    <?php if(empty($recent_transactions)): ?>
                        <p class="py-6 text-center text-slate-400 text-[11px]">No transactions yet</p>
                    <?php else: foreach($recent_transactions as $t):
                        $income = in_array($t['type'], ['deposit','refund','admin_credit']);
                        $bgcls = $income ? 'bg-emerald-500 text-white' : 'bg-indigo-500 text-white';
                        $stcls = $t['status']=='completed'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700';
                    ?>
                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full <?= $bgcls ?> flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 14l-7 7m0 0l-7-7m7 7V3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                            </div>
                            <div>
                                <p class="font-bold text-slate-800"><?= ucfirst($t['type']) ?></p>
                                <p class="text-[10px] text-slate-400">PKR <?= number_format($t['amount'], 2) ?></p>
                            </div>
                        </div>
                        <div class="text-right flex items-center gap-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= $stcls ?>"><?= ucfirst($t['status']) ?></span>
                            <span class="text-[10px] text-slate-400"><?= date('M j, Y', strtotime($t['created_at'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

        </section>

    </main>

</div>

</body>
</html>