<?php
require_once '../config/session.php';

$step = $_GET['step'] ?? 1;
$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_otp'])) {
        $email = sanitize($_POST['identifier']);
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch();
        if ($user) {
            $otp = generateOTP(6);
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_expires'] = time() + 300;
            $msg = "OTP sent! (Demo OTP: <strong>$otp</strong>)";
            $step = 2;
        } else $error = "No account found with that email/username.";
    } elseif (isset($_POST['verify_otp'])) {
        $entered = $_POST['otp'];
        if (time() > ($_SESSION['reset_expires'] ?? 0)) { $error = "OTP expired."; $step = 1; }
        elseif ($entered == ($_SESSION['reset_otp'] ?? '')) { $_SESSION['reset_verified'] = true; $step = 3; }
        else { $error = "Invalid OTP."; $step = 2; }
    } elseif (isset($_POST['reset_password'])) {
        if (!empty($_SESSION['reset_verified'])) {
            $new = $_POST['password']; $confirm = $_POST['confirm_password'];
            if ($new !== $confirm) { $error = "Passwords do not match."; $step = 3; }
            elseif (strlen($new) < 8) { $error = "Min 8 characters."; $step = 3; }
            else {
                $hashed = password_hash($new, PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE users SET password = ? WHERE email = ? OR username = ?")->execute([$hashed, $_SESSION['reset_email'], $_SESSION['reset_email']]);
                session_destroy();
                session_start();
                $_SESSION['flash'] = "Password reset successful!";
                redirect('login.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Forgot Password - <?= $theme['site_name'] ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = {
      theme: { extend: { colors: { brand: { dark:'#080918',navy:'#0d0f28',deepPurple:'#131137',primary:'#6347f9',primaryHover:'#5238ee',accent:'#826afb' } }, fontFamily: { sans: ['Inter','system-ui','sans-serif'] } } }
    }
</script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    body { font-family: 'Inter', sans-serif; }
    .glow-radial { background: radial-gradient(circle at 60% 70%, rgba(130, 106, 251, 0.28) 0%, rgba(99, 71, 249, 0.08) 45%, transparent 70%); }
    .isometric-envelope { transform: rotate(-10deg) skew(4deg, -2deg); filter: drop-shadow(0 25px 35px rgba(99, 71, 249, 0.45)); }
</style>
</head>
<body class="bg-[#f0f2f8] min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-10 font-sans antialiased text-slate-800">

<main class="w-full max-w-[1040px] bg-white rounded-2xl sm:rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[640px] border border-slate-100">

<section class="lg:col-span-6 bg-[#0a0a20] relative p-8 sm:p-12 lg:p-14 flex flex-col justify-between overflow-hidden text-white select-none">
<div aria-hidden="true" class="absolute inset-0 glow-radial pointer-events-none"></div>
<div aria-hidden="true" class="absolute -bottom-24 -left-20 w-80 h-80 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>

<div class="relative z-10">
<div class="flex items-center gap-3 mb-10 sm:mb-12">
<div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#826afb] to-[#5b3bf5] flex items-center justify-center shadow-lg shadow-indigo-600/30">
<svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L4 5.5v6.2c0 5.4 3.4 10.3 8 11.3 4.6-1 8-5.9 8-11.3V5.5L12 2zm-1 15l-4-4 1.4-1.4L11 14.2l6.6-6.6L19 9l-8 8z"></path></svg>
</div>
<span class="text-2xl font-bold tracking-tight text-white"><?= $theme['site_name'] ?></span>
</div>
<h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-2">Reset Your Password</h1>
<p class="text-sm sm:text-base text-slate-300 font-normal mb-8 leading-relaxed">Enter your email or username to receive OTP.</p>
<ul class="space-y-4">
<li class="flex items-center gap-3.5 text-sm sm:text-base text-slate-200"><span class="w-6 h-6 rounded-lg bg-white/10 flex items-center justify-center text-purple-400 border border-white/10"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><span>Quick &amp; easy</span></li>
<li class="flex items-center gap-3.5 text-sm sm:text-base text-slate-200"><span class="w-6 h-6 rounded-lg bg-white/10 flex items-center justify-center text-purple-400 border border-white/10"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><span>Secure process</span></li>
<li class="flex items-center gap-3.5 text-sm sm:text-base text-slate-200"><span class="w-6 h-6 rounded-lg bg-white/10 flex items-center justify-center text-purple-400 border border-white/10"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><span>Get back access</span></li>
</ul>
</div>

<div class="relative z-10 pt-12 flex justify-center items-center">
<div class="relative w-44 h-32 flex items-center justify-center">
<div class="absolute -top-3 -left-4 w-3.5 h-3.5 rounded-full bg-purple-400/40 blur-[1px]"></div>
<div class="absolute top-10 -right-2 w-2.5 h-2.5 rounded-full bg-indigo-300/60 blur-[0.5px]"></div>
<div class="absolute bottom-2 -left-6 w-2 h-2 rounded-full bg-purple-500/50"></div>
<div class="isometric-envelope relative bg-gradient-to-tr from-[#6948ff] to-[#8d71ff] w-36 h-24 rounded-2xl shadow-2xl p-2.5 border border-purple-300/30 flex flex-col justify-between">
<div class="w-full h-8 border-b border-white/20 flex justify-between items-start"><div class="w-2.5 h-2.5 rounded-full bg-white/30"></div><div class="w-2.5 h-2.5 rounded-full bg-white/30"></div></div>
<div class="self-center bg-white text-[#5838ea] font-extrabold text-sm tracking-wider px-3 py-1 rounded-md shadow-md uppercase">OTP</div>
<div class="w-full flex justify-center items-center"><div class="w-12 h-1 bg-white/25 rounded-full"></div></div>
</div>
</div>
</div>
</section>

<section class="lg:col-span-6 bg-white p-8 sm:p-12 lg:p-14 flex flex-col justify-center">
<div class="w-full max-w-md mx-auto">
<header class="mb-8">
<h2 class="text-2xl sm:text-3xl font-extrabold text-[#11142d] tracking-tight mb-2">
<?php if($step==1): ?>Forgot Password<?php elseif($step==2): ?>Verify OTP<?php else: ?>New Password<?php endif; ?>
</h2>
<p class="text-sm text-slate-500 leading-relaxed">
<?php if($step==1): ?>We'll send you a verification code to your email or phone.
<?php elseif($step==2): ?>Enter the 6-digit code we sent you.
<?php else: ?>Set your new password below.<?php endif; ?>
</p>
</header>

<?php if($msg): ?>
<div class="mb-4 p-3 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs"><?= $msg ?></div>
<?php endif; ?>
<?php if($error): ?>
<div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-100 text-rose-700 text-xs"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if($step == 1): ?>
<form class="space-y-6" method="POST">
<div class="space-y-2">
<label class="block text-xs sm:text-sm font-semibold text-slate-700 tracking-wide" for="identifier">Email or Username</label>
<input class="w-full px-4 py-3 text-sm rounded-xl border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent transition-all" id="identifier" name="identifier" placeholder="Enter email or username" required type="text"/>
</div>
<div class="pt-2">
<button class="w-full py-3.5 px-6 rounded-xl bg-[#6347f9] hover:bg-[#5337eb] text-white font-semibold text-sm shadow-md shadow-purple-600/25 transition-all" type="submit" name="send_otp">Send OTP</button>
</div>
</form>
<?php elseif($step == 2): ?>
<form class="space-y-6" method="POST">
<div class="space-y-2">
<label class="block text-xs sm:text-sm font-semibold text-slate-700 tracking-wide" for="otp">Enter OTP</label>
<input class="w-full px-4 py-3 text-lg text-center font-mono font-bold tracking-[8px] rounded-xl border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-primary transition-all" id="otp" name="otp" maxlength="6" placeholder="------" required type="text"/>
</div>
<div class="pt-2">
<button class="w-full py-3.5 px-6 rounded-xl bg-[#6347f9] hover:bg-[#5337eb] text-white font-semibold text-sm shadow-md shadow-purple-600/25 transition-all" type="submit" name="verify_otp">Verify OTP</button>
</div>
</form>
<?php else: ?>
<form class="space-y-4" method="POST">
<div class="space-y-2">
<label class="block text-xs sm:text-sm font-semibold text-slate-700 tracking-wide" for="password">New Password</label>
<input class="w-full px-4 py-3 text-sm rounded-xl border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary transition-all" id="password" name="password" placeholder="Enter new password" required minlength="8" type="password"/>
</div>
<div class="space-y-2">
<label class="block text-xs sm:text-sm font-semibold text-slate-700 tracking-wide" for="confirm_password">Confirm Password</label>
<input class="w-full px-4 py-3 text-sm rounded-xl border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary transition-all" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required minlength="8" type="password"/>
</div>
<div class="pt-2">
<button class="w-full py-3.5 px-6 rounded-xl bg-[#6347f9] hover:bg-[#5337eb] text-white font-semibold text-sm shadow-md shadow-purple-600/25 transition-all" type="submit" name="reset_password">Reset Password</button>
</div>
</form>
<?php endif; ?>

<footer class="mt-8 text-center">
<a class="text-xs sm:text-sm text-slate-500 hover:text-slate-800 font-medium transition-colors inline-flex items-center gap-1.5" href="login.php">← Back to Login</a>
</footer>
</div>
</section>
</main>
</body>
</html>