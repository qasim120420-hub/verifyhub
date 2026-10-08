<?php
require_once '../config/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $theme['site_name'] ?> — <?= $theme['site_tagline'] ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<!-- ============ HEADER ============ -->
<header class="site-header">
    <a href="index.php" class="logo-wrap">
        <div class="logo-icon">
            <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 2L4 6v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V6l-8-4z"/><path d="M9 12l2 2 4-4"/></svg>
        </div>
        <div class="logo-text">
            <h2><?= $theme['site_name'] ?></h2>
            <p>Secure Verification</p>
        </div>
    </a>

    <nav class="site-nav">
        <a href="index.php" class="active">Home</a>
        <a href="#features">Features</a>
        <a href="#how">How It Works</a>
        <a href="#pricing">Pricing</a>
        <a href="#faq">FAQ</a>
        <a href="#contact">Contact</a>
    </nav>

    <div class="site-actions">
        <a href="login.php" class="btn btn-ghost btn-sm">Sign In</a>
        <a href="register.php" class="btn btn-primary btn-sm">Create Account</a>
    </div>
</header>

<!-- ============ HERO ============ -->
<section class="hero-dark">
    <div class="hero-container">
        <div class="reveal-left">
            <div class="hero-badge-dark">
                <span class="dot"></span>
                Virtual &amp; Physical Numbers
            </div>
            <h1>
                Secure OTP Verification<br>
                for <span class="gradient-text">Your Business</span>
            </h1>
            <p class="lead">
                Get real phone numbers and receive OTPs instantly. Fast, secure and reliable verification services worldwide — all from one powerful dashboard.
            </p>
            <div class="hero-buttons">
                <a href="register.php" class="btn btn-primary">
                    <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Create Account
                </a>
                <a href="login.php" class="btn btn-ghost">Sign In</a>
            </div>
        </div>

        <div class="hero-visual reveal-right">
            <div class="dashboard-preview">
                <div class="preview-header">
                    <div class="preview-dots"><span></span><span></span><span></span></div>
                    <div class="preview-title">Dashboard Preview</div>
                    <div style="width:40px;"></div>
                </div>
                <div class="preview-body">
                    <div class="preview-card">
                        <div class="label">Wallet Balance</div>
                        <div class="value small">PKR 5,240</div>
                    </div>
                    <div class="preview-card">
                        <div class="label">Active Orders</div>
                        <div class="value small">3</div>
                    </div>
                    <div class="preview-card">
                        <div class="label">Available Numbers</div>
                        <div class="value small">Virtual: 48</div>
                    </div>
                    <div class="preview-card">
                        <div class="label">OTP Messages</div>
                        <div class="value small">12</div>
                    </div>
                </div>
                <div style="margin-top:14px;background:rgba(139,92,246,0.08);border:1px solid rgba(168,85,247,0.15);border-radius:10px;padding:14px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <span style="color:#A1A1AA;font-size:11px;font-weight:700;text-transform:uppercase;">Active Order</span>
                        <span style="color:#10B981;font-size:11px;font-weight:700;">● LIVE</span>
                    </div>
                    <div style="color:#fff;font-size:14px;font-weight:700;margin-bottom:8px;">Waiting for OTP...</div>
                    <div style="height:6px;background:rgba(255,255,255,0.08);border-radius:10px;overflow:hidden;">
                        <div style="height:100%;width:65%;background:linear-gradient(90deg,#8B5CF6,#EC4899);border-radius:10px;"></div>
                    </div>
                </div>
            </div>

            <div class="floating-badge badge-1">
                <div class="badge-icon"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                <div class="badge-text"><p>Secure</p><p>256-bit encryption</p></div>
            </div>
            <div class="floating-badge badge-2">
                <div class="badge-icon"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div>
                <div class="badge-text"><p>Fast</p><p>Instant OTP</p></div>
            </div>
            <div class="floating-badge badge-3">
                <div class="badge-icon"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div>
                <div class="badge-text"><p>Reliable</p><p>99.9% uptime</p></div>
            </div>
        </div>
    </div>
</section>

<!-- ============ FEATURES ============ -->
<section class="section-features" id="features">
    <div class="section-heading reveal">
        <div class="section-badge">
            <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7l3-7z"/></svg>
            Powerful Features
        </div>
        <h2>Everything You Need to Scale</h2>
        <p>Complete suite for OTP verification, number management, and secure wallet operations.</p>
    </div>

    <div class="features-grid">
        <div class="feature-box reveal">
            <div class="fi fi-purple"><svg class="icon icon-lg" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/></svg></div>
            <h3>Virtual Numbers</h3>
            <p>Get numbers from 100+ countries instantly.</p>
        </div>
        <div class="feature-box reveal">
            <div class="fi fi-pink"><svg class="icon icon-lg" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M12 12h.01"/></svg></div>
            <h3>Physical Numbers</h3>
            <p>Real SIM cards for long-term use.</p>
        </div>
        <div class="feature-box reveal">
            <div class="fi fi-cyan"><svg class="icon icon-lg" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div>
            <h3>Live OTP</h3>
            <p>Receive OTPs in real-time.</p>
        </div>
        <div class="feature-box reveal">
            <div class="fi fi-green"><svg class="icon icon-lg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
            <h3>Wallet System</h3>
            <p>Secure and easy payments.</p>
        </div>
        <div class="feature-box reveal">
            <div class="fi fi-indigo"><svg class="icon icon-lg" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div>
            <h3>Order Management</h3>
            <p>Track your orders easily.</p>
        </div>
        <div class="feature-box reveal">
            <div class="fi fi-blue"><svg class="icon icon-lg" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
            <h3>Secure Account</h3>
            <p>Your data is always safe.</p>
        </div>
        <div class="feature-box reveal">
            <div class="fi fi-orange"><svg class="icon icon-lg" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/></svg></div>
            <h3>Transaction History</h3>
            <p>Full record of your activity.</p>
        </div>
        <div class="feature-box reveal">
            <div class="fi fi-red"><svg class="icon icon-lg" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.79 19.79 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg></div>
            <h3>Admin Controlled</h3>
            <p>Verified and trusted service.</p>
        </div>
    </div>
</section>

<!-- ============ HOW IT WORKS ============ -->
<section class="section-how" id="how">
    <div class="section-heading reveal">
        <div class="section-badge">
            <svg class="icon icon-sm" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Simple Process
        </div>
        <h2>How It Works</h2>
        <p>Get verified in 4 simple steps. No complex setup required.</p>
    </div>

    <div class="features-grid" style="max-width:1200px;margin:0 auto;">
        <div class="step-box reveal">
            <div class="step-num-big step-num-1">1</div>
            <h3>Create Account</h3>
            <p>Sign up in under 60 seconds with email verification.</p>
        </div>
        <div class="step-box reveal">
            <div class="step-num-big step-num-2">2</div>
            <h3>Add Funds</h3>
            <p>Deposit via bank, EasyPaisa, JazzCash, or crypto.</p>
        </div>
        <div class="step-box reveal">
            <div class="step-num-big step-num-3">3</div>
            <h3>Buy Number</h3>
            <p>Choose country, service, and pick from available numbers.</p>
        </div>
        <div class="step-box reveal">
            <div class="step-num-big step-num-4">4</div>
            <h3>Receive OTP</h3>
            <p>Watch OTPs arrive instantly and complete verification.</p>
        </div>
    </div>
</section>

<!-- ============ PRICING ============ -->
<section class="section-pricing" id="pricing">
    <div class="section-heading reveal">
        <div class="section-badge">
            <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            Transparent Pricing
        </div>
        <h2>Pay Only For What You Use</h2>
        <p>No subscriptions. No hidden fees. Pure pay-as-you-go.</p>
    </div>

    <div class="features-grid" style="max-width:1100px;margin:0 auto;">
        <div class="pricing-box reveal">
            <h3 style="font-size:20px;font-weight:800;margin-bottom:8px;">Virtual Numbers</h3>
            <div style="font-size:38px;font-weight:900;color:#8B5CF6;margin:16px 0;line-height:1;">
                PKR 80<span style="font-size:14px;color:#6B7280;font-weight:500;">/number</span>
            </div>
            <ul style="list-style:none;margin:22px 0;color:#6B7280;font-size:13.5px;display:flex;flex-direction:column;gap:10px;text-align:left;">
                <li>✓ Instant activation</li>
                <li>✓ 50+ countries available</li>
                <li>✓ Real-time OTP delivery</li>
                <li>✓ Auto-refund on expiry</li>
            </ul>
            <a href="register.php" class="btn btn-outline btn-block">Get Started</a>
        </div>

        <div class="pricing-box popular reveal">
            <span class="pricing-badge">Most Popular</span>
            <h3 style="font-size:20px;font-weight:800;margin-bottom:8px;">Physical SIMs</h3>
            <div style="font-size:38px;font-weight:900;color:#EC4899;margin:16px 0;line-height:1;">
                PKR 500<span style="font-size:14px;color:#6B7280;font-weight:500;">/SIM</span>
            </div>
            <ul style="list-style:none;margin:22px 0;color:#6B7280;font-size:13.5px;display:flex;flex-direction:column;gap:10px;text-align:left;">
                <li>✓ Doorstep delivery</li>
                <li>✓ Real SIM card</li>
                <li>✓ Full activation support</li>
                <li>✓ Extended validity</li>
            </ul>
            <a href="register.php" class="btn btn-primary btn-block">Order Now</a>
        </div>

        <div class="pricing-box reveal">
            <h3 style="font-size:20px;font-weight:800;margin-bottom:8px;">Bulk Verification</h3>
            <div style="font-size:38px;font-weight:900;color:#06B6D4;margin:16px 0;line-height:1;">Custom</div>
            <ul style="list-style:none;margin:22px 0;color:#6B7280;font-size:13.5px;display:flex;flex-direction:column;gap:10px;text-align:left;">
                <li>✓ Volume discounts</li>
                <li>✓ API access</li>
                <li>✓ Dedicated manager</li>
                <li>✓ Custom SLAs</li>
            </ul>
            <a href="register.php" class="btn btn-outline btn-block">Contact Sales</a>
        </div>
    </div>
</section>

<!-- ============ FAQ ============ -->
<section class="section-faq" id="faq">
    <div class="section-heading reveal">
        <div class="section-badge">
            <svg class="icon icon-sm" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Common Questions
        </div>
        <h2>Frequently Asked Questions</h2>
        <p>Everything you need to know about <?= $theme['site_name'] ?>.</p>
    </div>

    <div class="faq-list">
        <?php
        $faqs = [
            ['What are virtual numbers?', 'Virtual numbers are cloud-based phone numbers that can receive SMS and calls without a physical SIM card. Perfect for instant verification and privacy-focused online activity.'],
            ['How fast are OTPs delivered?', 'OTPs are delivered in real-time — typically within 5-30 seconds. Our system uses fast polling for instant updates on your dashboard without any page refresh.'],
            ['What payment methods do you accept?', 'We accept Bank Transfer, EasyPaisa, JazzCash, and USDT (Crypto). All deposits are verified manually by our admin team for maximum security within 24 hours.'],
            ['What happens when an order expires?', 'If an OTP is not received within the validity period, the order expires and the amount is automatically refunded to your wallet — no questions asked, no manual request needed.'],
            ['Is my data secure?', 'Absolutely. We use bank-grade encryption, hashed passwords, and role-based access control. Every action is logged for audit purposes and unauthorized access is prevented.'],
            ['Can I get bulk verification?', 'Yes! We offer custom enterprise plans with volume discounts, API access, and dedicated account managers. Contact our sales team for custom pricing.']
        ];
        foreach ($faqs as $i => $f): ?>
            <div class="faq-item reveal" onclick="toggleFaq(this)">
                <div class="faq-question">
                    <span><?= $f[0] ?></span>
                    <span class="faq-icon">
                        <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                    </span>
                </div>
                <div class="faq-answer"><?= $f[1] ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ CTA ============ -->
<section class="hero-dark" style="padding:70px 5%;text-align:center;">
    <div class="reveal-scale" style="max-width:700px;margin:0 auto;position:relative;z-index:1;">
        <h2 style="font-size:38px;font-weight:800;margin-bottom:14px;letter-spacing:-0.5px;">
            Ready to <span class="gradient-text">Start Verifying?</span>
        </h2>
        <p style="color:#A1A1AA;font-size:15px;margin-bottom:30px;">
            Join thousands of businesses who trust <?= $theme['site_name'] ?> for their verification needs.
        </p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="register.php" class="btn btn-primary" style="padding:14px 32px;">Create Free Account</a>
            <a href="login.php" class="btn btn-ghost" style="padding:14px 32px;">Sign In</a>
        </div>
    </div>
</section>

<!-- ============ FOOTER ============ -->
<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="logo-wrap">
                <div class="logo-icon"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 2L4 6v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V6l-8-4z"/><path d="M9 12l2 2 4-4"/></svg></div>
                <div class="logo-text">
                    <h2 style="color:#fff;"><?= $theme['site_name'] ?></h2>
                    <p style="color:#A1A1AA;">Secure Verification</p>
                </div>
            </div>
            <p>The most reliable platform for number verification, OTP reception, and secure wallet management. Trusted by businesses worldwide.</p>
            <div class="social-row">
                <a href="#" class="social-btn fb" title="Facebook"><svg class="icon icon-sm" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg></a>
                <a href="#" class="social-btn tw" title="Twitter"><svg class="icon icon-sm" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/></svg></a>
                <a href="#" class="social-btn ig" title="Instagram"><svg class="icon icon-sm" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
                <a href="#" class="social-btn li" title="LinkedIn"><svg class="icon icon-sm" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2zM4 2a2 2 0 110 4 2 2 0 010-4z"/></svg></a>
                <a href="#" class="social-btn wa" title="WhatsApp"><svg class="icon icon-sm" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M17.5 14.4l-2.4-1.2a1 1 0 00-1.1.1l-1 1a9 9 0 01-4.5-4.5l1-1a1 1 0 00.1-1.1L8.4 5.3a1 1 0 00-1.1-.6C5.7 5 5 6.4 5 8a11 11 0 0011 11c1.6 0 3-.7 3.3-2.3a1 1 0 00-.6-1.1z"/></svg></a>
                <a href="#" class="social-btn yt" title="YouTube"><svg class="icon icon-sm" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M22.5 6.4a2.6 2.6 0 00-1.8-1.8C19.1 4 12 4 12 4s-7.1 0-8.7.5A2.6 2.6 0 001.5 6.4C1 8 1 12 1 12s0 4 .5 5.6a2.6 2.6 0 001.8 1.8C4.9 20 12 20 12 20s7.1 0 8.7-.5a2.6 2.6 0 001.8-1.8C23 16 23 12 23 12s0-4-.5-5.6zM10 15V9l5 3-5 3z"/></svg></a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Product</h4>
            <ul>
                <li><a href="#features">Features</a></li>
                <li><a href="#pricing">Pricing</a></li>
                <li><a href="#how">How It Works</a></li>
                <li><a href="#faq">FAQ</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Company</h4>
            <ul>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Careers</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Support</h4>
            <ul>
                <li><a href="#">Help Center</a></li>
                <li><a href="#">Terms of Service</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Refund Policy</a></li>
            </ul>
            <p style="color:#A1A1AA;font-size:13px;margin-top:16px;display:flex;align-items:center;gap:8px;">
                <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                support@<?= strtolower($theme['site_name']) ?>.com
            </p>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> <?= $theme['site_name'] ?>. All rights reserved.
    </div>
</footer>

<script>
// Scroll reveal
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('active'); });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale').forEach(el => observer.observe(el));

// FAQ Accordion
function toggleFaq(el) {
    const isOpen = el.classList.contains('open');
    // Close all
    document.querySelectorAll('.faq-item').forEach(item => item.classList.remove('open'));
    // Open clicked (if wasn't open)
    if (!isOpen) el.classList.add('open');
}
</script>

</body>
</html>