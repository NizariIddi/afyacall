<?php
// If already logged in, go straight to dashboard
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AfyaCall — Hospital Management System</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/afyacall-icon.svg">

    <!-- Apple touch icon (iOS home screen) -->
    <link rel="apple-touch-icon" href="/assets/images/afyacall-icon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:      #0d6e6e;
            --primary-dark: #094e4e;
            --accent:       #f4a235;
            --text-main:    #1a2333;
            --text-muted:   #6b7a99;
            --border:       #e2e8f0;
            --font:         'Plus Jakarta Sans', sans-serif;
            --font-mono:    'DM Mono', monospace;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font);
            background: #f0f4f8;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── NAV ─────────────────────────────────────────── */
        nav {
            background: var(--primary-dark);
            padding: 0 48px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            text-decoration: none;
        }
        .nav-dot {
            width: 10px; height: 10px;
            background: var(--accent);
            border-radius: 50%;
        }
        .nav-brand-name {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -.3px;
        }
        .nav-brand-sub {
            font-size: .62rem;
            color: rgba(255,255,255,.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-left: 4px;
            margin-top: 2px;
        }
        .nav-login {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 20px;
            background: var(--accent);
            color: #fff;
            border-radius: 8px;
            font-size: .85rem;
            font-weight: 700;
            text-decoration: none;
            transition: opacity .18s;
        }
        .nav-login:hover { opacity: .88; }

        /* ── HERO ────────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            padding: 90px 48px 80px;
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
        }
        .hero::after {
            content: '';
            position: absolute;
            bottom: -100px; left: -60px;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,.03);
        }
        .hero-tag {
            display: inline-block;
            background: rgba(244,162,53,.2);
            color: var(--accent);
            font-size: .7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 5px 14px;
            border-radius: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(244,162,53,.3);
        }
        .hero h1 {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: -.8px;
            line-height: 1.15;
            max-width: 640px;
            margin: 0 auto 18px;
        }
        .hero h1 span { color: var(--accent); }
        .hero p {
            font-size: 1.05rem;
            color: rgba(255,255,255,.65);
            max-width: 480px;
            margin: 0 auto 36px;
            line-height: 1.7;
        }
        .hero-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 32px;
            background: var(--accent);
            color: #fff;
            border-radius: 10px;
            font-size: .95rem;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 6px 24px rgba(244,162,53,.35);
            transition: all .2s;
        }
        .hero-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(244,162,53,.4);
        }
        .hero-cta svg { transition: transform .2s; }
        .hero-cta:hover svg { transform: translateX(3px); }

        /* ── STATS BAR ───────────────────────────────────── */
        .stats-bar {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 28px 48px;
            display: flex;
            justify-content: center;
            gap: 64px;
        }
        .stat-item { text-align: center; }
        .stat-num {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
        }
        .stat-lbl {
            font-size: .72rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .6px;
            margin-top: 4px;
        }

        /* ── FEATURES ────────────────────────────────────── */
        .section { padding: 72px 48px; max-width: 1100px; margin: 0 auto; width: 100%; }
        .section-title {
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: -.4px;
            margin-bottom: 8px;
            text-align: center;
        }
        .section-sub {
            font-size: .88rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 44px;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .feature-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 26px;
            transition: transform .2s, box-shadow .2s;
        }
        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(13,110,110,.1);
        }
        .feature-icon {
            width: 46px; height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }
        .fi-teal  { background: #e6f4f4; color: var(--primary); }
        .fi-amber { background: #fff4e0; color: var(--accent); }
        .fi-green { background: #e6f9f1; color: #2db57a; }
        .fi-blue  { background: #e8f0ff; color: #3b5bdb; }
        .fi-red   { background: #fdeaea; color: #e05353; }
        .fi-purple{ background: #f3eeff; color: #7c3aed; }
        .feature-title {
            font-size: .95rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .feature-desc {
            font-size: .82rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* ── CTA BOTTOM ──────────────────────────────────── */
        .cta-section {
            background: var(--primary-dark);
            padding: 64px 48px;
            text-align: center;
            color: #fff;
            margin-top: auto;
        }
        .cta-section h2 {
            font-size: 1.7rem;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -.3px;
        }
        .cta-section p {
            font-size: .9rem;
            color: rgba(255,255,255,.55);
            margin-bottom: 28px;
        }
        .btn-login-lg {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 36px;
            background: var(--accent);
            color: #fff;
            border-radius: 10px;
            font-size: .95rem;
            font-weight: 700;
            text-decoration: none;
            transition: opacity .18s;
        }
        .btn-login-lg:hover { opacity: .88; }

        /* ── FOOTER ──────────────────────────────────────── */
        footer {
            background: #0a3c3c;
            padding: 20px 48px;
            text-align: center;
            font-size: .75rem;
            color: rgba(255,255,255,.3);
        }
    </style>
</head>
<body>

<!-- Nav -->
<nav>
    <a class="nav-brand" href="index.php">
        <div class="nav-dot"></div>
        <span class="nav-brand-name">AfyaCall</span>
        <span class="nav-brand-sub">HMS</span>
    </a>
    <a href="auth/login.php" class="nav-login">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Staff Login
    </a>
</nav>

<!-- Hero -->
<div class="hero">
    <div class="hero-tag">&#9829; Hospital Management System</div>
    <h1>Healthcare made <span>simpler</span> for every clinic</h1>
    <p>Manage patients, appointments, medical records, prescriptions, and USSD health services — all from one place.</p>
    <a href="auth/login.php" class="hero-cta">
        Go to Dashboard
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
</div>

<!-- Stats bar -->
<div class="stats-bar">
    <?php
    // Try to show live stats — silently skip if DB isn't set up yet
    $stats_ok = false;
    @include_once("config/database.php");
    if (isset($conn) && $conn) {
        $pt = @mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) AS t FROM patients"))['t'] ?? 0;
        $ap = @mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments"))['t'] ?? 0;
        $re = @mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) AS t FROM medical_records"))['t'] ?? 0;
        $stats_ok = true;
    }
    if ($stats_ok):
    ?>
    <div class="stat-item">
        <div class="stat-num"><?php echo number_format($pt); ?></div>
        <div class="stat-lbl">Patients Registered</div>
    </div>
    <div class="stat-item">
        <div class="stat-num"><?php echo number_format($ap); ?></div>
        <div class="stat-lbl">Appointments</div>
    </div>
    <div class="stat-item">
        <div class="stat-num"><?php echo number_format($re); ?></div>
        <div class="stat-lbl">Medical Records</div>
    </div>
    <?php else: ?>
    <div class="stat-item"><div class="stat-num">—</div><div class="stat-lbl">Patients</div></div>
    <div class="stat-item"><div class="stat-num">—</div><div class="stat-lbl">Appointments</div></div>
    <div class="stat-item"><div class="stat-num">—</div><div class="stat-lbl">Records</div></div>
    <?php endif; ?>
    <div class="stat-item">
        <div class="stat-num">24/7</div>
        <div class="stat-lbl">USSD Access</div>
    </div>
</div>

<!-- Features -->
<div class="section">
    <div class="section-title">Everything your clinic needs</div>
    <div class="section-sub">Built for Tanzanian healthcare — Swahili &amp; English, USSD-ready, works on any device.</div>

    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon fi-teal">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="feature-title">Patient Management</div>
            <div class="feature-desc">Register patients with full profiles, medical history, blood group, emergency contacts, and printable ID cards with QR codes.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon fi-amber">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="feature-title">Appointments</div>
            <div class="feature-desc">Schedule, track, and manage appointments. Calendar view, status tracking (Pending, Completed, Missed), and overdue alerts.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon fi-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="feature-title">Medical Records</div>
            <div class="feature-desc">Record diagnoses, medications, allergies, and clinical notes per visit. Print prescriptions directly from any record.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon fi-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
            </div>
            <div class="feature-title">USSD Services</div>
            <div class="feature-desc">Patients check appointments and receive health tips via USSD on any phone — no internet needed. Swahili and English menus.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon fi-red">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.39 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.29 6.29l1.13-1.13a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div class="feature-title">Health Broadcasts</div>
            <div class="feature-desc">Send health alerts and tips to patients via SMS/USSD. Manage a library of bilingual health tips by category.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon fi-purple">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="feature-title">Doctor Scheduling</div>
            <div class="feature-desc">Assign morning, afternoon, and night shifts to doctors. See who is on duty today and manage the full weekly rota.</div>
        </div>
    </div>
</div>

<!-- Bottom CTA -->
<div class="cta-section">
    <h2>Ready to get started?</h2>
    <p>Log in with your staff account to access the full system.</p>
    <a href="auth/login.php" class="btn-login-lg">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Staff Login
    </a>
</div>

<!-- Footer -->
<footer>
    &copy; <?php echo date('Y'); ?> AfyaCall Hospital Management System. All rights reserved.
</footer>

</body>
</html>
