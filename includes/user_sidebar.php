<?php
$cp = basename($_SERVER['PHP_SELF']);
$user_full_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
?>
<aside class="w-60 bg-[#0d0e22] text-[#9ca3af] flex flex-col flex-shrink-0 z-20 border-r border-[#1a1c38]" style="height: 100vh; position: sticky; top: 0;">
    <!-- Brand -->
    <div class="h-14 flex items-center px-5 gap-2.5 border-b border-[#1b1c3a] flex-shrink-0">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-indigo-900/50">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-3zm-1 15l-4-4 1.41-1.41L11 14.17l6.59-6.59L19 9l-8 8z"></path></svg>
        </div>
        <span class="text-white font-bold text-base tracking-wide"><?= $theme['site_name'] ?></span>
    </div>

    <!-- Nav (scrollable with dark bg) -->
    <div class="flex-1 overflow-y-auto sidebar-scroll py-3 px-2.5 space-y-0.5 text-[11px] bg-[#0d0e22]">

        <a class="flex items-center gap-2.5 px-3 py-2 rounded-lg <?= $cp=='dashboard.php' ? 'bg-[#5349e4] text-white font-semibold shadow-md shadow-indigo-900/40' : 'text-slate-300 hover:bg-[#1a1b38] hover:text-white' ?> transition" href="dashboard.php">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect height="7" rx="1.5" stroke-width="2" width="7" x="3" y="3"></rect><rect height="7" rx="1.5" stroke-width="2" width="7" x="14" y="3"></rect><rect height="7" rx="1.5" stroke-width="2" width="7" x="14" y="14"></rect><rect height="7" rx="1.5" stroke-width="2" width="7" x="3" y="14"></rect></svg>
            <span class="text-xs">Dashboard</span>
        </a>

        <!-- Numbers -->
        <?php $numbersOpen = in_array($cp, ['buy-number.php','my-numbers.php','virtual-numbers.php','physical-numbers.php']); ?>
        <div>
            <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-400 hover:text-white hover:bg-[#1a1b38] rounded-lg transition">
                <span class="flex items-center gap-2.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path></svg>
                    Numbers
                </span>
                <svg class="w-3 h-3 text-slate-500 chev transition-transform <?= $numbersOpen?'rotate-90':'' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
            </button>
            <div class="accordion-content pl-6 space-y-0.5 mt-0.5 <?= $numbersOpen?'':'hidden' ?>">
                <a class="block py-1.5 px-2 <?= $cp=='buy-number.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="buy-number.php">Buy Number</a>
                <a class="block py-1.5 px-2 <?= $cp=='virtual-numbers.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="my-numbers.php?type=virtual">Virtual Numbers</a>
                <a class="block py-1.5 px-2 <?= $cp=='physical-numbers.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="my-numbers.php?type=physical">Physical Numbers</a>
                <a class="block py-1.5 px-2 <?= $cp=='my-numbers.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="my-numbers.php">My Numbers</a>
            </div>
        </div>

        <!-- Orders -->
        <?php $ordersOpen = in_array($cp, ['active-orders.php','order-history.php']); ?>
        <div>
            <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-400 hover:text-white hover:bg-[#1a1b38] rounded-lg transition">
                <span class="flex items-center gap-2.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path></svg>
                    Orders
                </span>
                <svg class="w-3 h-3 text-slate-500 chev transition-transform <?= $ordersOpen?'rotate-90':'' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
            </button>
            <div class="accordion-content pl-6 space-y-0.5 mt-0.5 <?= $ordersOpen?'':'hidden' ?>">
                <a class="block py-1.5 px-2 <?= $cp=='active-orders.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="active-orders.php">Active Orders</a>
                <a class="block py-1.5 px-2 <?= $cp=='order-history.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="order-history.php">Order History</a>
            </div>
        </div>

        <!-- Wallet -->
        <?php $walletOpen = in_array($cp, ['wallet.php','deposit.php','transactions.php','transfer.php']); ?>
        <div>
            <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-400 hover:text-white hover:bg-[#1a1b38] rounded-lg transition">
                <span class="flex items-center gap-2.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path></svg>
                    Wallet
                </span>
                <svg class="w-3 h-3 text-slate-500 chev transition-transform <?= $walletOpen?'rotate-90':'' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
            </button>
            <div class="accordion-content pl-6 space-y-0.5 mt-0.5 <?= $walletOpen?'':'hidden' ?>">
                <a class="block py-1.5 px-2 <?= $cp=='wallet.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="wallet.php">Wallet</a>
                <a class="block py-1.5 px-2 <?= $cp=='deposit.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="deposit.php">Deposit Funds</a>
                <a class="block py-1.5 px-2 <?= $cp=='transactions.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="transactions.php">Transactions</a>
                <a class="block py-1.5 px-2 <?= $cp=='transfer.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="transfer.php">Transfer</a>
            </div>
        </div>

        <!-- Communication -->
        <?php $commOpen = in_array($cp, ['otp-messages.php','notifications.php']); ?>
        <div>
            <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-400 hover:text-white hover:bg-[#1a1b38] rounded-lg transition">
                <span class="flex items-center gap-2.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path></svg>
                    Communication
                </span>
                <svg class="w-3 h-3 text-slate-500 chev transition-transform <?= $commOpen?'rotate-90':'' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
            </button>
            <div class="accordion-content pl-6 space-y-0.5 mt-0.5 <?= $commOpen?'':'hidden' ?>">
                <a class="block py-1.5 px-2 <?= $cp=='otp-messages.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="otp-messages.php">OTP Messages</a>
                <a class="block py-1.5 px-2 <?= $cp=='notifications.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="notifications.php">Notifications</a>
            </div>
        </div>

        <!-- Support -->
        <?php $supportOpen = in_array($cp, ['support.php','help.php']); ?>
        <div>
            <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-400 hover:text-white hover:bg-[#1a1b38] rounded-lg transition">
                <span class="flex items-center gap-2.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path></svg>
                    Support
                </span>
                <svg class="w-3 h-3 text-slate-500 chev transition-transform <?= $supportOpen?'rotate-90':'' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
            </button>
            <div class="accordion-content pl-6 space-y-0.5 mt-0.5 <?= $supportOpen?'':'hidden' ?>">
                <a class="block py-1.5 px-2 <?= $cp=='support.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="support.php">Support Tickets</a>
                <a class="block py-1.5 px-2 <?= $cp=='help.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="help.php">Help Center</a>
            </div>
        </div>

        <!-- Account -->
        <?php $accountOpen = in_array($cp, ['profile.php','security.php','settings.php']); ?>
        <div>
            <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-400 hover:text-white hover:bg-[#1a1b38] rounded-lg transition">
                <span class="flex items-center gap-2.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path></svg>
                    Account
                </span>
                <svg class="w-3 h-3 text-slate-500 chev transition-transform <?= $accountOpen?'rotate-90':'' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
            </button>
            <div class="accordion-content pl-6 space-y-0.5 mt-0.5 <?= $accountOpen?'':'hidden' ?>">
                <a class="block py-1.5 px-2 <?= $cp=='profile.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="profile.php">My Profile</a>
                <a class="block py-1.5 px-2 <?= $cp=='security.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="security.php">Security</a>
                <a class="block py-1.5 px-2 <?= $cp=='settings.php'?'text-white bg-[#1a1b38]':'' ?> text-slate-400 hover:text-white rounded hover:bg-[#1a1b38] transition text-[11px]" href="settings.php">Settings</a>
            </div>
        </div>

        <!-- Logout -->
        <div class="pt-3 mt-3 border-t border-[#1a1c38]">
            <a class="flex items-center gap-2.5 px-3 py-2 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition text-xs font-medium" href="../public/logout.php">
                <svg class="w-3.5 h-3.5 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
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