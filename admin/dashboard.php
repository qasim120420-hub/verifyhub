<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$active_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user' AND status='active'")->fetchColumn();
$blocked_users = $pdo->query("SELECT COUNT(*) FROM users WHERE status='blocked'")->fetchColumn();
$total_numbers = $pdo->query("SELECT COUNT(*) FROM numbers")->fetchColumn();
$available_numbers = $pdo->query("SELECT COUNT(*) FROM numbers WHERE status='available'")->fetchColumn();
$assigned_numbers = $pdo->query("SELECT COUNT(*) FROM numbers WHERE status='assigned'")->fetchColumn();
$active_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending','waiting_otp','otp_received')")->fetchColumn();
$pending_deposits = $pdo->query("SELECT COUNT(*) FROM deposits WHERE status='pending'")->fetchColumn();
$total_wallet = $pdo->query("SELECT COALESCE(SUM(balance),0) FROM wallets")->fetchColumn();
$total_revenue = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE type='purchase'")->fetchColumn();
$today_revenue = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE type='purchase' AND DATE(created_at)=CURDATE()")->fetchColumn();
$support_tickets = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status='open'")->fetchColumn();
$completed_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='completed'")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$cancelled_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='cancelled'")->fetchColumn();
$expired_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='expired'")->fetchColumn();
$total_orders_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

$recent_users = $pdo->query("SELECT id, username, full_name, email, status, created_at FROM users WHERE role='user' ORDER BY created_at DESC LIMIT 4")->fetchAll();
$recent_deposits = $pdo->query("SELECT d.*, u.username, u.full_name FROM deposits d JOIN users u ON d.user_id=u.id ORDER BY d.created_at DESC LIMIT 4")->fetchAll();

$chart_values = [];
$chart_labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('M j', strtotime($date));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE type='purchase' AND DATE(created_at)=?");
    $stmt->execute([$date]);
    $chart_values[] = (float)$stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Admin Dashboard - <?= $theme['site_name'] ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'sans-serif'] },
          colors: {
            brand: { sidebar:'#0d1026', sidebarHover:'#171c3d', purple:'#5e48e8', purpleDark:'#4d37d4' }
          }
        }
      }
    }
</script>
<style>
    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .donut-chart-container { position: relative; width: 130px; height: 130px; }
</style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col">

<header class="bg-white border-b border-slate-200 h-14 sticky top-0 z-30 flex items-center justify-between px-4 lg:px-6">
    <div class="flex items-center gap-3 w-60 flex-shrink-0">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-purple to-indigo-500 flex items-center justify-center text-white shadow-md shadow-indigo-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"></path></svg>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="text-base font-bold text-slate-900 tracking-tight"><?= $theme['site_name'] ?></span>
            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-brand-purple/10 text-brand-purple">Admin</span>
        </div>
    </div>
    <div class="flex-1 max-w-xl mx-4 hidden md:block">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
            </span>
            <input class="w-full pl-10 pr-4 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs placeholder-slate-400 focus:outline-none focus:bg-white focus:border-brand-purple focus:ring-1 focus:ring-brand-purple transition-all" placeholder="Search users, orders, etc..." type="text"/>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <button class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-full transition-colors hidden sm:block">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
            <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-red-500 rounded-full ring-2 ring-white"></span>
        </button>
        <div class="flex items-center gap-2.5 cursor-pointer pl-2 border-l border-slate-200">
            <div class="w-7 h-7 rounded-full bg-slate-900 border border-slate-300 flex items-center justify-center text-white text-[11px] font-semibold">SA</div>
            <div class="hidden sm:flex flex-col text-left"><span class="text-xs font-semibold text-slate-800 leading-tight">Super Admin</span></div>
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
        </div>
    </div>
</header>

<div class="flex flex-1 overflow-hidden">
    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 p-4 lg:p-6 overflow-y-auto bg-slate-50">
        <div class="max-w-7xl mx-auto space-y-4">

            <div>
                <h1 class="text-lg font-bold text-slate-900 tracking-tight">Admin Dashboard</h1>
                <p class="text-[11px] text-slate-500 mt-0.5">System overview and key statistics.</p>
            </div>

            <!-- Row 1 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-[#f59e0b] p-4 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div><p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Total Users</p><h3 class="text-xl font-bold text-white mt-1"><?= number_format($total_users) ?></h3></div>
                        <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></div>
                    </div>
                </div>

                <div class="bg-[#10b981] p-4 rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div><p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Active Users</p><h3 class="text-xl font-bold text-white mt-1"><?= number_format($active_users) ?></h3></div>
                        <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></div>
                    </div>
                </div>

                <div class="bg-[#ef4444] p-4 rounded-xl shadow-lg shadow-rose-500/30 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div><p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Blocked Users</p><h3 class="text-xl font-bold text-white mt-1"><?= number_format($blocked_users) ?></h3></div>
                        <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></div>
                    </div>
                </div>

                <div class="bg-[#8b5cf6] p-4 rounded-xl shadow-lg shadow-violet-500/30 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div><p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Total Numbers</p><h3 class="text-xl font-bold text-white mt-1"><?= number_format($total_numbers) ?></h3></div>
                        <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></div>
                    </div>
                </div>
            </div>

            <!-- Row 2 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-[#06b6d4] p-4 rounded-xl shadow-lg shadow-cyan-500/30 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div><p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Available Numbers</p><h3 class="text-xl font-bold text-white mt-1"><?= number_format($available_numbers) ?></h3></div>
                        <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></div>
                    </div>
                </div>

                <div class="bg-[#3b82f6] p-4 rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div><p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Assigned Numbers</p><h3 class="text-xl font-bold text-white mt-1"><?= number_format($assigned_numbers) ?></h3></div>
                        <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></div>
                    </div>
                </div>

                <div class="bg-[#6366f1] p-4 rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div><p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Active Orders</p><h3 class="text-xl font-bold text-white mt-1"><?= number_format($active_orders) ?></h3></div>
                        <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></div>
                    </div>
                </div>

                <div class="bg-[#d97706] p-4 rounded-xl shadow-lg shadow-orange-500/30 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div><p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Pending Deposits</p><h3 class="text-xl font-bold text-white mt-1"><?= number_format($pending_deposits) ?></h3></div>
                        <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></div>
                    </div>
                </div>
            </div>

            <!-- Row 3 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-[#14b8a6] p-4 rounded-xl shadow-lg shadow-teal-500/30 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div><p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Total Wallet</p><h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($total_wallet, 2) ?></h3></div>
                        <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></div>
                    </div>
                </div>

                <div class="bg-[#059669] p-4 rounded-xl shadow-lg shadow-emerald-600/30 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div><p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Today's Revenue</p><h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($today_revenue, 2) ?></h3></div>
                        <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></div>
                    </div>
                </div>

                <div class="bg-[#7c3aed] p-4 rounded-xl shadow-lg shadow-purple-500/30 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div><p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Total Revenue</p><h3 class="text-base font-bold text-white mt-1">PKR <?= number_format($total_revenue, 2) ?></h3></div>
                        <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></div>
                    </div>
                </div>

                <div class="bg-[#0ea5e9] p-4 rounded-xl shadow-lg shadow-sky-500/30 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div><p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Support Tickets</p><h3 class="text-xl font-bold text-white mt-1"><?= number_format($support_tickets) ?></h3></div>
                        <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 bg-white p-4 rounded-xl border border-slate-200/90 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-[#5e48e8] flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 8-8" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path></svg>
                            </div>
                            <h3 class="text-xs font-bold text-slate-800">Revenue Overview</h3>
                        </div>
                        <button class="inline-flex items-center gap-1.5 text-[11px] font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-md px-2 py-1">Last 7 Days <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></button>
                    </div>
                    <div class="relative w-full h-48">
                        <canvas class="w-full h-full" id="revenueChart"></canvas>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-[#8b5cf6] to-[#6366f1] p-4 rounded-xl shadow-lg shadow-violet-500/30 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-bold text-white">Order Status</h3>
                        <span class="text-[10px] font-semibold text-white/90 bg-white/20 px-2 py-0.5 rounded-full"><?= $total_orders_count ?> Total</span>
                    </div>
                    <div class="flex flex-col items-center justify-center gap-3 my-auto">
                        <div class="donut-chart-container flex items-center justify-center">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-white/20" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4.2"></path>
                                <?php
                                $t = max($total_orders_count, 1);
                                $p1 = round(($completed_orders/$t)*100, 1);
                                $p2 = round(($pending_orders/$t)*100, 1);
                                $p3 = round(($cancelled_orders/$t)*100, 1);
                                $p4 = round(($expired_orders/$t)*100, 1);
                                ?>
                                <path class="text-emerald-300" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="<?= $p1 ?>, 100" stroke-dashoffset="0" stroke-linecap="round" stroke-width="4.2"></path>
                                <path class="text-amber-300" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="<?= $p2 ?>, 100" stroke-dashoffset="-<?= $p1 ?>" stroke-linecap="round" stroke-width="4.2"></path>
                                <path class="text-rose-300" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="<?= $p3 ?>, 100" stroke-dashoffset="-<?= $p1+$p2 ?>" stroke-linecap="round" stroke-width="4.2"></path>
                                <path class="text-white/40" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="<?= $p4 ?>, 100" stroke-dashoffset="-<?= $p1+$p2+$p3 ?>" stroke-linecap="round" stroke-width="4.2"></path>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="text-xl font-bold text-white leading-none"><?= $total_orders_count ?></span>
                                <span class="text-[9px] text-white/70 font-medium tracking-wide mt-0.5">Total</span>
                            </div>
                        </div>
                        <div class="w-full space-y-1.5 text-[11px] px-2">
                            <div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-white/90"><span class="w-2 h-2 rounded-full bg-emerald-300"></span> Completed</span><span class="font-bold text-white"><?= $completed_orders ?></span></div>
                            <div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-white/90"><span class="w-2 h-2 rounded-full bg-amber-300"></span> Pending</span><span class="font-bold text-white"><?= $pending_orders ?></span></div>
                            <div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-white/90"><span class="w-2 h-2 rounded-full bg-rose-300"></span> Cancelled</span><span class="font-bold text-white"><?= $cancelled_orders ?></span></div>
                            <div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-white/90"><span class="w-2 h-2 rounded-full bg-white/40"></span> Expired</span><span class="font-bold text-white"><?= $expired_orders ?></span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 pb-4">

                <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm overflow-hidden">
                    <div class="p-3.5 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-[#3b82f6] flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                            </div>
                            <h3 class="text-xs font-bold text-slate-900">Recent Users</h3>
                        </div>
                        <a class="text-[11px] font-semibold text-brand-purple hover:text-brand-purpleDark" href="users.php">View All →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead><tr class="text-slate-500 font-semibold border-b border-slate-100 bg-slate-50/50">
                                <th class="py-2 px-4 font-medium text-[10px]">User</th>
                                <th class="py-2 px-4 font-medium text-[10px]">Email</th>
                                <th class="py-2 px-4 font-medium text-[10px]">Status</th>
                                <th class="py-2 px-4 font-medium text-[10px] text-right">Joined</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-100">
                            <?php if(empty($recent_users)): ?>
                                <tr><td colspan="4" class="py-5 text-center text-slate-400 text-[11px]">No users yet</td></tr>
                            <?php else: foreach($recent_users as $u):
                                $scls = $u['status']=='active' ? 'bg-emerald-500 text-white' : ($u['status']=='blocked'?'bg-rose-500 text-white':'bg-amber-500 text-white');
                            ?>
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-2.5 px-4 font-medium text-slate-800 text-[11px]"><?= htmlspecialchars($u['full_name'] ?: $u['username']) ?></td>
                                    <td class="py-2.5 px-4 text-slate-500 text-[11px]"><?= htmlspecialchars($u['email']) ?></td>
                                    <td class="py-2.5 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= $scls ?>"><?= ucfirst($u['status']) ?></span></td>
                                    <td class="py-2.5 px-4 text-right text-slate-400 text-[10px]"><?= date('M j', strtotime($u['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm overflow-hidden">
                    <div class="p-3.5 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-[#10b981] flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                            </div>
                            <h3 class="text-xs font-bold text-slate-900">Recent Deposits</h3>
                        </div>
                        <a class="text-[11px] font-semibold text-brand-purple hover:text-brand-purpleDark" href="deposits.php">View All →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead><tr class="text-slate-500 font-semibold border-b border-slate-100 bg-slate-50/50">
                                <th class="py-2 px-4 font-medium text-[10px]">User</th>
                                <th class="py-2 px-4 font-medium text-[10px]">Amount</th>
                                <th class="py-2 px-4 font-medium text-[10px]">Status</th>
                                <th class="py-2 px-4 font-medium text-[10px] text-right">Date</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-100">
                            <?php if(empty($recent_deposits)): ?>
                                <tr><td colspan="4" class="py-5 text-center text-slate-400 text-[11px]">No deposits yet</td></tr>
                            <?php else: foreach($recent_deposits as $d):
                                $scls = $d['status']=='approved' ? 'bg-emerald-500 text-white' : ($d['status']=='rejected'?'bg-rose-500 text-white':'bg-amber-500 text-white');
                            ?>
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-2.5 px-4 font-medium text-slate-800 text-[11px]"><?= htmlspecialchars($d['full_name'] ?: $d['username']) ?></td>
                                    <td class="py-2.5 px-4 font-medium text-slate-700 text-[11px]">PKR <?= number_format($d['amount'], 0) ?></td>
                                    <td class="py-2.5 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= $scls ?>"><?= ucfirst($d['status']) ?></span></td>
                                    <td class="py-2.5 px-4 text-right text-slate-400 text-[10px]"><?= date('M j', strtotime($d['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Revenue Chart -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById("revenueChart");
    if (!canvas) return;
    const ctx = canvas.getContext("2d");
    function drawChart() {
        const rect = canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        ctx.resetTransform?.();
        ctx.scale(dpr, dpr);
        const width = rect.width, height = rect.height;
        const padL = 36, padR = 20, padT = 15, padB = 30;
        const cW = width - padL - padR, cH = height - padT - padB;
        const days = <?= json_encode($chart_labels) ?>;
        const values = <?= json_encode($chart_values) ?>;
        const maxVal = Math.max(...values, 80000);
        const yTicks = [0, maxVal * 0.25, maxVal * 0.5, maxVal * 0.75, maxVal];
        ctx.strokeStyle = 'rgba(139,92,246,0.15)'; ctx.lineWidth = 1;
        ctx.fillStyle = '#6366f1'; ctx.font = '10px Inter, sans-serif';
        ctx.textAlign = 'right'; ctx.textBaseline = 'middle';
        yTicks.forEach(tick => {
            const y = padT + cH - (tick / maxVal) * cH;
            ctx.beginPath(); ctx.moveTo(padL, y); ctx.lineTo(width - padR, y); ctx.stroke();
            const label = tick === 0 ? '0' : (tick / 1000).toFixed(0) + 'k';
            ctx.fillText(label, padL - 8, y);
        });
        ctx.textAlign = 'center'; ctx.textBaseline = 'top';
        const points = [];
        days.forEach((day, i) => {
            const x = padL + (i / (days.length - 1)) * cW;
            const y = padT + cH - (values[i] / maxVal) * cH;
            points.push({ x, y });
            ctx.fillText(day, x, height - padB + 8);
        });
        const grad = ctx.createLinearGradient(0, padT, 0, height - padB);
        grad.addColorStop(0, 'rgba(94, 72, 232, 0.35)');
        grad.addColorStop(1, 'rgba(94, 72, 232, 0.02)');
        ctx.beginPath(); ctx.moveTo(points[0].x, points[0].y);
        for (let i = 0; i < points.length - 1; i++) {
            const p0 = points[i], p1 = points[i+1];
            const midX = (p0.x + p1.x) / 2;
            ctx.bezierCurveTo(midX, p0.y, midX, p1.y, p1.x, p1.y);
        }
        ctx.lineTo(points[points.length-1].x, height - padB);
        ctx.lineTo(points[0].x, height - padB);
        ctx.closePath(); ctx.fillStyle = grad; ctx.fill();
        ctx.beginPath(); ctx.moveTo(points[0].x, points[0].y);
        for (let i = 0; i < points.length - 1; i++) {
            const p0 = points[i], p1 = points[i+1];
            const midX = (p0.x + p1.x) / 2;
            ctx.bezierCurveTo(midX, p0.y, midX, p1.y, p1.x, p1.y);
        }
        ctx.strokeStyle = '#5e48e8'; ctx.lineWidth = 2.5; ctx.stroke();
        points.forEach(pt => {
            ctx.beginPath(); ctx.arc(pt.x, pt.y, 3.5, 0, Math.PI * 2);
            ctx.fillStyle = '#ffffff'; ctx.fill();
            ctx.lineWidth = 2; ctx.strokeStyle = '#5e48e8'; ctx.stroke();
        });
    }
    drawChart();
    window.addEventListener('resize', drawChart);
});
</script>

</body>
</html>