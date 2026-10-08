<?php
require_once '../config/session.php';
if (!isAdmin()) redirect('login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['primary_color','secondary_color','accent_color','bg_color','sidebar_color','text_color','card_color','border_color','site_name','site_tagline'];
    foreach ($fields as $f) {
        if (isset($_POST[$f])) {
            $val = sanitize($_POST[$f]);
            $stmt = $pdo->prepare("UPDATE theme_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$val, $f]);
        }
    }
    $msg = "Theme settings updated successfully!";
    $theme = [];
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM theme_settings");
    while($row = $stmt->fetch()) $theme[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Theme Settings - <?= $theme['site_name'] ?></title>
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
    <div class="max-w-5xl mx-auto space-y-4">

      <div>
        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Theme Settings</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Customize colors, branding and site appearance.</p>
      </div>

      <?php if($msg): ?>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">

        <!-- Site Info -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#6366f1] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Site Information</h3>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Site Name</label>
              <input type="text" name="site_name" value="<?= htmlspecialchars($theme['site_name']) ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">Tagline</label>
              <input type="text" name="site_tagline" value="<?= htmlspecialchars($theme['site_tagline']) ?>" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
          </div>
        </div>

        <!-- Brand Colors -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#8b5cf6] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle><path d="M12 2a10 10 0 100 20 2 2 0 001.5-3.3 2 2 0 011.5-3.3h2A5 5 0 0022 10a8 8 0 00-8-8h-2z" stroke-linecap="round" stroke-width="2"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Brand Colors</h3>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <?php
            $colors = [
              ['primary_color', 'Primary', 'indigo'],
              ['secondary_color', 'Secondary', 'violet'],
              ['accent_color', 'Accent', 'pink'],
              ['sidebar_color', 'Sidebar', 'slate']
            ];
            foreach($colors as $c): ?>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1"><?= $c[1] ?></label>
              <div class="flex items-center gap-2">
                <input type="color" name="<?= $c[0] ?>" value="<?= $theme[$c[0]] ?>" class="w-9 h-9 rounded-lg border border-slate-200 cursor-pointer">
                <input type="text" value="<?= $theme[$c[0]] ?>" readonly class="flex-1 px-2 py-2 text-[10px] font-mono rounded-lg border border-slate-200 bg-slate-50 text-slate-500">
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- UI Colors -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#06b6d4] flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"></rect><rect x="14" y="3" width="7" height="7" rx="1" stroke-width="2"></rect><rect x="14" y="14" width="7" height="7" rx="1" stroke-width="2"></rect><rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"></rect></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">UI Colors</h3>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <?php
            $uicolors = [
              ['bg_color', 'Background'],
              ['card_color', 'Card'],
              ['text_color', 'Text'],
              ['border_color', 'Border']
            ];
            foreach($uicolors as $c): ?>
            <div>
              <label class="block text-[11px] font-semibold text-slate-700 mb-1"><?= $c[1] ?></label>
              <div class="flex items-center gap-2">
                <input type="color" name="<?= $c[0] ?>" value="<?= $theme[$c[0]] ?>" class="w-9 h-9 rounded-lg border border-slate-200 cursor-pointer">
                <input type="text" value="<?= $theme[$c[0]] ?>" readonly class="flex-1 px-2 py-2 text-[10px] font-mono rounded-lg border border-slate-200 bg-slate-50 text-slate-500">
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white font-bold text-xs py-3 rounded-lg shadow-lg shadow-indigo-500/30 hover:shadow-xl transition-all">
          Save Theme Settings
        </button>

      </form>
    </div>
  </main>
</div>

</body>
</html>