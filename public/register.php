<?php
require_once '../config/session.php';
if (isLoggedIn()) redirect('../user/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) $error = "All required fields must be filled.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Invalid email format.";
    elseif ($password !== $confirm) $error = "Passwords do not match.";
    elseif (strlen($password) < 8) $error = "Password must be at least 8 characters.";
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) $error = "Username or Email already exists.";
        else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, full_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$username, $full_name, $email, $phone, $hashed])) {
                $user_id = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)")->execute([$user_id]);
                try { addNotification($pdo, $user_id, "Welcome!", "Your account is ready."); } catch (Exception $e) {}
                redirect('login.php');
            } else $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Register - <?= $theme['site_name'] ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<script>
    tailwind.config = {
      theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"','sans-serif'] }, colors: { brand: { 500:'#6366f1',600:'#4f46e5',700:'#4338ca',purple:'#5B4EF5','purple-hover':'#4E40E5' } } } }
    }
</script>
<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .glow-aura { background: radial-gradient(circle at 60% 85%, rgba(109, 40, 217, 0.45) 0%, rgba(79, 70, 229, 0.2) 35%, rgba(8, 8, 22, 0) 70%); }
    .badge-glow { box-shadow: 0 0 35px -5px rgba(124, 58, 237, 0.6), 0 0 15px -3px rgba(139, 92, 246, 0.4); }
    .phone-shadow { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 30px rgba(139, 92, 246, 0.25); }
</style>
</head>
<body class="bg-[#0b0c1b] min-h-screen flex items-center justify-center p-0 md:p-6 lg:p-10 text-slate-800 antialiased">

<main class="w-full max-w-6xl bg-white shadow-2xl rounded-2xl overflow-hidden flex flex-col lg:flex-row min-h-[640px] border border-slate-800/20">

<section class="lg:w-[45%] bg-[#08091a] relative text-white p-8 sm:p-12 flex flex-col justify-between overflow-hidden">
<div class="absolute inset-0 glow-aura pointer-events-none"></div>
<div class="relative z-10">
<div class="flex items-center gap-2.5 mb-10">
<div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#5B4EF5] to-[#7C3AED] flex items-center justify-center shadow-lg shadow-indigo-600/30 ring-1 ring-white/20">
<svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path clip-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.5 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 00.374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm3.094 8.016a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" fill-rule="evenodd"></path></svg>
</div>
<span class="text-xl font-bold tracking-tight text-white"><?= $theme['site_name'] ?></span>
</div>
<h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mb-2">Join <?= $theme['site_name'] ?></h1>
<p class="text-sm text-slate-400 font-normal mb-8">Start your journey with us today.</p>
<ul class="space-y-4 text-sm font-medium text-slate-300">
<li class="flex items-center gap-3"><span class="w-6 h-6 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-indigo-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><span>Access global numbers</span></li>
<li class="flex items-center gap-3"><span class="w-6 h-6 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-indigo-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><span>Receive OTP instantly</span></li>
<li class="flex items-center gap-3"><span class="w-6 h-6 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-indigo-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><span>Secure &amp; reliable</span></li>
<li class="flex items-center gap-3"><span class="w-6 h-6 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-indigo-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><span>24/7 support</span></li>
</ul>
</div>
<div class="relative mt-8 pt-6 flex items-end justify-start -mb-6 -ml-4">
<div class="absolute w-52 h-52 bg-indigo-600/30 rounded-full blur-2xl left-2 bottom-0 pointer-events-none"></div>
<div class="relative z-20 badge-glow transform -rotate-12 translate-y-3">
<div class="w-20 h-24 bg-gradient-to-b from-[#7c3aed] to-[#4c1d95] rounded-2xl p-1 shadow-2xl flex flex-col items-center justify-center border border-purple-400/30">
<svg class="w-10 h-10 text-white drop-shadow" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4.5 12.75l6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</div>
</div>
<div class="relative z-10 -ml-4 mb-2 transform rotate-6 phone-shadow">
<div class="w-40 h-48 bg-gradient-to-b from-[#1e1b4b] to-[#0f1026] rounded-2xl p-2.5 border border-indigo-500/30 flex flex-col justify-between">
<div class="flex items-center justify-between pb-2 border-b border-indigo-950/60"><div class="w-2.5 h-2.5 rounded-full bg-indigo-500/40"></div><div class="w-8 h-1.5 rounded-full bg-indigo-500/30"></div><div class="w-2 h-2 rounded-full bg-indigo-400/40"></div></div>
<div class="bg-indigo-900/40 rounded-xl p-2.5 border border-indigo-500/20 text-center">
<div class="text-[9px] text-indigo-300 font-medium tracking-wide mb-1">VERIFICATION</div>
<div class="text-xs font-mono font-bold tracking-widest text-white bg-indigo-950/60 py-1 px-2 rounded border border-indigo-400/20">482 • 931</div>
</div>
<div class="w-12 h-1 bg-white/20 rounded-full mx-auto"></div>
</div>
</div>
</div>
</section>

<section class="lg:w-[55%] bg-white p-8 sm:p-12 lg:p-14 flex flex-col justify-center">
<div class="mb-8">
<h2 class="text-2xl font-bold tracking-tight text-slate-900">Create Your Account</h2>
<p class="text-xs sm:text-sm text-slate-500 mt-1">Fill in your details to get started</p>
</div>

<?php if($error): ?>
<div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-100 text-rose-700 text-xs flex items-center gap-2">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form class="space-y-4" method="POST">
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div><label class="block text-xs font-semibold text-slate-700 mb-1.5" for="username">Username</label>
<input class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg text-xs sm:text-sm placeholder:text-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-purple focus:border-transparent transition-all shadow-sm" id="username" name="username" placeholder="Enter username" required type="text"/></div>
<div><label class="block text-xs font-semibold text-slate-700 mb-1.5" for="fullname">Full Name</label>
<input class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg text-xs sm:text-sm placeholder:text-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-purple focus:border-transparent transition-all shadow-sm" id="fullname" name="full_name" placeholder="Enter full name" required type="text"/></div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div><label class="block text-xs font-semibold text-slate-700 mb-1.5" for="email">Email Address</label>
<input class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg text-xs sm:text-sm placeholder:text-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-purple focus:border-transparent transition-all shadow-sm" id="email" name="email" placeholder="Enter email address" required type="email"/></div>
<div><label class="block text-xs font-semibold text-slate-700 mb-1.5" for="phone">Phone Number</label>
<input class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg text-xs sm:text-sm placeholder:text-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-purple focus:border-transparent transition-all shadow-sm" id="phone" name="phone" placeholder="Enter phone number" required type="tel"/></div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div><label class="block text-xs font-semibold text-slate-700 mb-1.5" for="password">Password</label>
<input class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg text-xs sm:text-sm placeholder:text-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-purple focus:border-transparent transition-all shadow-sm" id="password" name="password" placeholder="Enter password" required minlength="8" type="password"/></div>
<div><label class="block text-xs font-semibold text-slate-700 mb-1.5" for="confirm-password">Confirm Password</label>
<input class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg text-xs sm:text-sm placeholder:text-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-purple focus:border-transparent transition-all shadow-sm" id="confirm-password" name="confirm_password" placeholder="Confirm password" required minlength="8" type="password"/></div>
</div>
<div class="pt-2 flex items-start gap-2.5">
<input class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-purple focus:ring-brand-purple cursor-pointer" id="terms" name="terms" required type="checkbox"/>
<label class="text-xs text-slate-600 font-normal leading-relaxed cursor-pointer select-none" for="terms">I agree to the <a class="text-brand-purple hover:underline font-medium" href="#">Terms &amp; Conditions</a> and <a class="text-brand-purple hover:underline font-medium" href="#">Privacy Policy</a></label>
</div>
<div class="pt-3">
<button class="w-full py-3 px-4 bg-brand-purple hover:bg-brand-purple-hover text-white text-sm font-semibold rounded-lg shadow-md shadow-indigo-600/25 transition-all active:scale-[0.99]" type="submit">Register</button>
</div>
<p class="text-center text-xs text-slate-500 pt-3">Already have an account? <a class="font-semibold text-brand-purple hover:underline transition-colors ml-1" href="login.php">Sign In</a></p>
</form>
</section>
</main>
</body>
</html>