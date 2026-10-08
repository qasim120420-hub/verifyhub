<?php
require_once '../config/session.php';
if (isLoggedIn()) redirect('../user/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = sanitize($_POST['login']);
    $password = $_POST['password'];
    if (empty($login) || empty($password)) {
        $error = "Please enter username/email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();
        if ($user) {
            if ($user['status'] === 'blocked') $error = "Your account has been blocked.";
            elseif (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'] ?? $user['username'];
                try { $pdo->prepare("INSERT INTO login_logs (user_id, ip_address) VALUES (?, ?)")->execute([$user['id'], $_SERVER['REMOTE_ADDR']]); } catch (Exception $e) {}
                redirect($user['role'] === 'admin' ? '../admin/dashboard.php' : '../user/dashboard.php');
            } else $error = "Incorrect password.";
        } else $error = "No account found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Login - <?= $theme['site_name'] ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<script>
    tailwind.config = {
      theme: { extend: { colors: { brand: { 500:'#7c3aed',600:'#6366f1',primary:'#5d50fe',hover:'#4f40ee' } } } }
    }
</script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .glow-radial-purple { background: radial-gradient(circle at 60% 80%, rgba(124, 58, 237, 0.35) 0%, rgba(93, 80, 254, 0.15) 35%, transparent 70%); }
    .glow-shield { filter: drop-shadow(0 0 25px rgba(124, 58, 237, 0.7)); }
    .phone-reflection { box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 30px rgba(99, 102, 241, 0.25); }
</style>
</head>
<body class="min-h-screen bg-[#070913] flex items-center justify-center p-4 md:p-8 text-slate-800">

<main class="w-full max-w-[1000px] min-h-[640px] bg-white rounded-2xl shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-2 border border-slate-800/20">
<div class="relative bg-gradient-to-b from-[#090b1c] via-[#0b0e24] to-[#080918] text-white p-8 md:p-12 flex flex-col justify-between overflow-hidden">
<div class="absolute inset-0 glow-radial-purple pointer-events-none"></div>
<div class="relative z-10">
<div class="flex items-center gap-2.5 mb-10">
<div class="w-9 h-9 rounded-lg bg-brand-primary flex items-center justify-center shadow-lg shadow-indigo-500/30">
<svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path clip-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.5 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 00.374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm3.094 8.016a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" fill-rule="evenodd"></path></svg>
</div>
<span class="text-xl font-bold tracking-tight text-white"><?= $theme['site_name'] ?></span>
</div>
<h1 class="text-3xl font-bold text-white mb-2 tracking-tight">Welcome Back</h1>
<p class="text-slate-400 text-sm mb-7">Sign in to your account to continue.</p>
<ul class="space-y-3.5 text-sm font-medium text-slate-300">
<li class="flex items-center gap-3"><span class="w-6 h-6 rounded border border-purple-500/40 bg-purple-500/10 flex items-center justify-center text-indigo-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><span>Secure login</span></li>
<li class="flex items-center gap-3"><span class="w-6 h-6 rounded border border-purple-500/40 bg-purple-500/10 flex items-center justify-center text-indigo-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><span>Fast access</span></li>
<li class="flex items-center gap-3"><span class="w-6 h-6 rounded border border-purple-500/40 bg-purple-500/10 flex items-center justify-center text-indigo-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><span>24/7 support</span></li>
</ul>
</div>
<div class="relative mt-8 h-48 flex items-center justify-center">
<div class="absolute -bottom-8 -left-12 -right-12 h-36 bg-gradient-to-t from-indigo-900/30 via-purple-600/10 to-transparent blur-xl rounded-full"></div>
<div class="relative w-40 h-52 bg-[#131633] rounded-2xl border-2 border-indigo-500/30 phone-reflection transform rotate-6 translate-x-12 translate-y-2 p-2 flex flex-col justify-between">
<div class="w-12 h-1 bg-slate-700/60 rounded-full mx-auto mt-1"></div>
<div class="bg-[#1b1f48] rounded-xl p-3 border border-indigo-400/20 text-center space-y-2">
<div class="w-8 h-8 rounded-full bg-indigo-500/20 mx-auto flex items-center justify-center text-indigo-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" stroke-linecap="round" stroke-linejoin="round"></path></svg></div>
<div class="h-1.5 w-14 bg-indigo-300/40 rounded-full mx-auto"></div>
<div class="h-1 w-8 bg-indigo-400/20 rounded-full mx-auto"></div>
</div>
<div class="w-6 h-1 bg-slate-700/60 rounded-full mx-auto mb-1"></div>
</div>
<div class="absolute left-10 bottom-2 z-20 transform -rotate-6 glow-shield">
<div class="w-20 h-24 bg-gradient-to-br from-indigo-500 via-purple-600 to-indigo-800 rounded-2xl p-1 shadow-2xl flex items-center justify-center border border-indigo-300/40">
<div class="w-full h-full bg-[#1e1b4b]/50 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/20">
<svg class="w-10 h-10 text-white drop-shadow" fill="currentColor" viewBox="0 0 24 24"><path clip-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.5 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 00.374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm3.094 8.016a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" fill-rule="evenodd"></path></svg>
</div>
</div>
</div>
</div>
</div>

<div class="bg-white p-8 md:p-14 flex flex-col justify-center">
<div class="max-w-md w-full mx-auto">
<header class="mb-7">
<h2 class="text-2xl font-bold text-gray-900 tracking-tight mb-1.5">Login to Your Account</h2>
<p class="text-slate-500 text-sm">Enter your credentials to access your dashboard.</p>
</header>

<?php if($error): ?>
<div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-100 text-rose-700 text-xs flex items-center gap-2">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form class="space-y-4" method="POST">
<div>
<label class="block text-xs font-semibold text-gray-700 mb-1.5" for="login">Username or Email</label>
<input class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm text-gray-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all" id="login" name="login" placeholder="Enter username or email" type="text" required autofocus/>
</div>
<div>
<label class="block text-xs font-semibold text-gray-700 mb-1.5" for="password">Password</label>
<div class="relative">
<input class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm text-gray-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all pr-10" id="password" name="password" placeholder="Enter password" type="password" required/>
<button aria-label="Toggle password visibility" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors" type="button" onclick="const p=document.getElementById('password'); p.type = p.type==='password'?'text':'password';">
<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</button>
</div>
</div>
<div class="flex items-center justify-between text-xs pt-0.5">
<label class="flex items-center gap-2 cursor-pointer select-none text-slate-600"><input class="w-4 h-4 rounded border-slate-300 text-brand-primary focus:ring-brand-primary/30" type="checkbox" name="remember"/><span>Remember Me</span></label>
<a class="font-medium text-brand-primary hover:text-brand-hover hover:underline" href="forgot-password.php">Forgot Password?</a>
</div>
<div class="pt-2">
<button class="w-full py-2.5 px-4 bg-brand-primary hover:bg-brand-hover text-white text-sm font-semibold rounded-lg shadow-sm shadow-indigo-500/20 transition-all active:scale-[0.99]" type="submit">Login</button>
</div>
<div class="relative py-1 flex items-center justify-center">
<div class="w-full border-t border-slate-200"></div>
<span class="absolute px-3 bg-white text-xs text-slate-400 lowercase">or</span>
</div>
<div>
<a class="w-full block text-center py-2 px-4 border border-slate-300 hover:border-slate-400 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-all" href="register.php">Create Account</a>
</div>
</form>
</div>
</div>
</main>

</body>
</html>