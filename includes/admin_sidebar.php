<?php
$cp = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-60 bg-[#0d1026] flex-shrink-0 text-slate-300 flex flex-col hidden lg:flex" style="height: 100vh; position: sticky; top: 0;">
    <!-- Brand -->
    <div class="h-14 flex items-center px-5 gap-2.5 border-b border-[#1a1c38] flex-shrink-0">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-[#5e48e8] to-indigo-500 flex items-center justify-center text-white shadow-md shadow-indigo-900/50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"></path></svg>
        </div>
        <span class="text-white font-bold text-base tracking-wide"><?= $theme['site_name'] ?></span>
        <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded bg-[#5e48e8]/30 text-[#a79bff]">Admin</span>
    </div>

    <!-- Nav -->
    <div class="flex-1 overflow-y-auto py-3 px-2.5 space-y-0.5 text-xs bg-[#0d1026]">

        <!-- Dashboard -->
        <a class="flex items-center justify-between px-3 py-2 rounded-lg <?= $cp=='dashboard.php' ? 'bg-[#5e48e8] text-white font-medium shadow-sm' : 'text-slate-300 hover:bg-[#171c3d] hover:text-white' ?> transition-all" href="dashboard.php">
            <div class="flex items-center gap-2.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" stroke-linecap="round" stroke-width="2"></path></svg>
                <span class="text-xs">Dashboard</span>
            </div>
        </a>

        <!-- Users -->
        <?php $usersOpen = in_array($cp, ['users.php','user-details.php','create-user.php']); ?>
        <div>
            <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-300 hover:bg-[#171c3d] hover:text-white rounded-lg transition">
                <div class="flex items-center gap-2.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" stroke-linecap="round" stroke-width="2"></path></svg>
                    <span>Users</span>
                </div>
                <svg class="w-3 h-3 text-slate-500 chev transition-transform <?= $usersOpen?'rotate-90':'' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-width="2"></path></svg>
            </button>
            <div class="accordion-content pl-6 space-y-0.5 mt-0.5 <?= $usersOpen?'':'hidden' ?>">
                <a class="block py-1.5 px-2 <?= $cp=='users.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="users.php">All Users</a>
                <a class="block py-1.5 px-2 <?= $cp=='create-user.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="create-user.php">Create User</a>
            </div>
        </div>

        <!-- Numbers -->
        <?php $numOpen = in_array($cp, ['manage-numbers.php','add-number.php','manage-countries.php','manage-services.php']); ?>
        <div>
            <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-300 hover:bg-[#171c3d] hover:text-white rounded-lg transition">
                <div class="flex items-center gap-2.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-linecap="round" stroke-width="2"></path></svg>
                    <span>Numbers</span>
                </div>
                <svg class="w-3 h-3 text-slate-500 chev transition-transform <?= $numOpen?'rotate-90':'' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-width="2"></path></svg>
            </button>
            <div class="accordion-content pl-6 space-y-0.5 mt-0.5 <?= $numOpen?'':'hidden' ?>">
                <a class="block py-1.5 px-2 <?= $cp=='manage-numbers.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="manage-numbers.php">All Numbers</a>
                <a class="block py-1.5 px-2 <?= $cp=='add-number.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="add-number.php">Add Number</a>
                <a class="block py-1.5 px-2 <?= $cp=='manage-countries.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="manage-countries.php">Countries</a>
                <a class="block py-1.5 px-2 <?= $cp=='manage-services.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="manage-services.php">Services</a>
            </div>
        </div>

        <!-- Orders -->
        <?php $ordOpen = in_array($cp, ['orders.php']); ?>
        <div>
            <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-300 hover:bg-[#171c3d] hover:text-white rounded-lg transition">
                <div class="flex items-center gap-2.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-width="2"></path></svg>
                    <span>Orders</span>
                </div>
                <svg class="w-3 h-3 text-slate-500 chev transition-transform <?= $ordOpen?'rotate-90':'' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-width="2"></path></svg>
            </button>
            <div class="accordion-content pl-6 space-y-0.5 mt-0.5 <?= $ordOpen?'':'hidden' ?>">
                <a class="block py-1.5 px-2 <?= $cp=='orders.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="orders.php">All Orders</a>
                <a class="block py-1.5 px-2 rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="orders.php?status=active">Active Orders</a>
                <a class="block py-1.5 px-2 rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="orders.php?status=completed">Completed</a>
                <a class="block py-1.5 px-2 rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="orders.php?status=cancelled">Cancelled</a>
            </div>
        </div>

        <!-- Wallet -->
        <?php $walOpen = in_array($cp, ['wallet-control.php','transactions.php','transfers.php']); ?>
        <div>
            <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-300 hover:bg-[#171c3d] hover:text-white rounded-lg transition">
                <div class="flex items-center gap-2.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke-linecap="round" stroke-width="2"></path></svg>
                    <span>Wallet</span>
                </div>
                <svg class="w-3 h-3 text-slate-500 chev transition-transform <?= $walOpen?'rotate-90':'' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-width="2"></path></svg>
            </button>
            <div class="accordion-content pl-6 space-y-0.5 mt-0.5 <?= $walOpen?'':'hidden' ?>">
                <a class="block py-1.5 px-2 <?= $cp=='wallet-control.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="wallet-control.php">Wallet Control</a>
                <a class="block py-1.5 px-2 <?= $cp=='transactions.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="transactions.php">All Transactions</a>
                <a class="block py-1.5 px-2 <?= $cp=='transfers.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="transfers.php">User Transfers</a>
            </div>
        </div>

        <!-- Payments -->
        <?php $payOpen = in_array($cp, ['deposits.php','payment-methods.php']); ?>
        <div>
            <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-300 hover:bg-[#171c3d] hover:text-white rounded-lg transition">
                <div class="flex items-center gap-2.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-width="2"></path></svg>
                    <span>Payments</span>
                </div>
                <svg class="w-3 h-3 text-slate-500 chev transition-transform <?= $payOpen?'rotate-90':'' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-width="2"></path></svg>
            </button>
            <div class="accordion-content pl-6 space-y-0.5 mt-0.5 <?= $payOpen?'':'hidden' ?>">
                <a class="block py-1.5 px-2 <?= $cp=='deposits.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="deposits.php">Deposits</a>
                <a class="block py-1.5 px-2 <?= $cp=='payment-methods.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="payment-methods.php">Payment Methods</a>
            </div>
        </div>

        <!-- OTP -->
        <?php $otpOpen = in_array($cp, ['otp-logs.php','otp-settings.php']); ?>
        <div>
            <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-300 hover:bg-[#171c3d] hover:text-white rounded-lg transition">
                <div class="flex items-center gap-2.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" stroke-linecap="round" stroke-width="2"></path></svg>
                    <span>OTP</span>
                </div>
                <svg class="w-3 h-3 text-slate-500 chev transition-transform <?= $otpOpen?'rotate-90':'' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-width="2"></path></svg>
            </button>
            <div class="accordion-content pl-6 space-y-0.5 mt-0.5 <?= $otpOpen?'':'hidden' ?>">
                <a class="block py-1.5 px-2 <?= $cp=='otp-logs.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="otp-logs.php">OTP Logs</a>
                <a class="block py-1.5 px-2 <?= $cp=='otp-settings.php'?'text-white bg-[#171c3d]':'' ?> rounded hover:bg-[#171c3d] hover:text-white transition text-[11px]" href="otp-settings.php">OTP Settings</a>
            </div>
        </div>

        <!-- Support -->
        <a class="flex items-center justify-between px-3 py-2 rounded-lg <?= $cp=='support-tickets.php'?'text-white bg-[#171c3d]':'' ?> hover:bg-[#171c3d] hover:text-white transition" href="support-tickets.php">
            <div class="flex items-center gap-2.5"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" stroke-linecap="round" stroke-width="2"></path></svg><span>Support</span></div>
            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-width="2"></path></svg>
        </a>

        <!-- Notifications -->
        <a class="flex items-center justify-between px-3 py-2 rounded-lg <?= $cp=='notifications.php'?'text-white bg-[#171c3d]':'' ?> hover:bg-[#171c3d] hover:text-white transition" href="notifications.php">
            <div class="flex items-center gap-2.5"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-width="2"></path></svg><span>Notifications</span></div>
            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-width="2"></path></svg>
        </a>

        <!-- Website -->
        <a class="flex items-center justify-between px-3 py-2 rounded-lg <?= $cp=='theme-settings.php'?'text-white bg-[#171c3d]':'' ?> hover:bg-[#171c3d] hover:text-white transition" href="theme-settings.php">
            <div class="flex items-center gap-2.5"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" stroke-linecap="round" stroke-width="2"></path></svg><span>Website</span></div>
            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-width="2"></path></svg>
        </a>

        <!-- Security -->
        <a class="flex items-center justify-between px-3 py-2 rounded-lg <?= $cp=='security.php'?'text-white bg-[#171c3d]':'' ?> hover:bg-[#171c3d] hover:text-white transition" href="security.php">
            <div class="flex items-center gap-2.5"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-width="2"></path></svg><span>Security</span></div>
            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-width="2"></path></svg>
        </a>

        <!-- Reports -->
        <a class="flex items-center justify-between px-3 py-2 rounded-lg <?= $cp=='reports.php'?'text-white bg-[#171c3d]':'' ?> hover:bg-[#171c3d] hover:text-white transition" href="reports.php">
            <div class="flex items-center gap-2.5"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke-linecap="round" stroke-width="2"></path></svg><span>Reports</span></div>
            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-width="2"></path></svg>
        </a>

        <!-- System -->
        <a class="flex items-center justify-between px-3 py-2 rounded-lg <?= $cp=='settings.php'?'text-white bg-[#171c3d]':'' ?> hover:bg-[#171c3d] hover:text-white transition" href="settings.php">
            <div class="flex items-center gap-2.5"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke-linecap="round" stroke-width="2"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-width="2"></path></svg><span>System</span></div>
            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-width="2"></path></svg>
        </a>

        <!-- Logout -->
        <div class="pt-3 mt-3 border-t border-[#1a1c38]">
            <a class="flex items-center gap-2.5 px-3 py-2 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition text-xs font-medium" href="logout.php">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-width="2"></path></svg>
                <span>Logout</span>
            </a>
        </div>
    </div>
</aside>

<script>
function toggleAccordion(btn) {
    const content = btn.nextElementSibling;
    const chev = btn.querySelector('.chev');
    const isHidden = content.classList.contains('hidden');
    if (isHidden) {
        content.classList.remove('hidden');
        chev.classList.add('rotate-90');
    } else {
        content.classList.add('hidden');
        chev.classList.remove('rotate-90');
    }
}
</script>