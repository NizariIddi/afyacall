<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in → go to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: ../dashboard/index.php");
    exit();
}

include("../config/database.php");
include("../config/csrf.php");

$error   = "";
$timeout = isset($_GET['timeout']);

// ── Brute-force throttle (5 attempts per 10 minutes per IP) ─────────────
$ip        = $_SERVER['REMOTE_ADDR'] ?? '';
$ip_key    = 'login_fail_' . md5($ip);
$max_tries = 5;
$lockout   = 600; // 10 min

if (!isset($_SESSION[$ip_key])) $_SESSION[$ip_key] = ['count' => 0, 'since' => time()];

// Reset window if expired
if (time() - $_SESSION[$ip_key]['since'] > $lockout) {
    $_SESSION[$ip_key] = ['count' => 0, 'since' => time()];
}

$locked = $_SESSION[$ip_key]['count'] >= $max_tries;

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$locked) {
    csrf_verify();

    $email    = clean_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {
        $error = "Please enter a valid email address.";
    } else {
        $e      = mysqli_real_escape_string($conn, $email);
        $result = mysqli_query($conn, "SELECT * FROM admins WHERE email='$e' AND is_active=1 LIMIT 1");

        if (mysqli_num_rows($result) > 0) {
            $admin = mysqli_fetch_assoc($result);
            if (password_verify($password, $admin['password'])) {
                // ── Success ──────────────────────────────────────────
                session_regenerate_id(true);
                $_SESSION['admin_id']       = $admin['id'];
                $_SESSION['admin_name']     = $admin['full_name'];
                $_SESSION['admin_role']     = $admin['role'];
                $_SESSION['admin_email']    = $admin['email'];
                $_SESSION['last_activity']  = time();
                $_SESSION['last_regen']     = time();
                unset($_SESSION[$ip_key]);

                // Log the login event
                include("../config/activity_log.php");
                log_activity($conn, 'Logged in', 'auth', $admin['id']);

                // Update last login
                mysqli_query($conn, "UPDATE admins SET last_login=NOW() WHERE id='{$admin['id']}'");

                header("Location: ../dashboard/index.php");
                exit();
            } else {
                $error = "Incorrect password.";
                $_SESSION[$ip_key]['count']++;
            }
        } else {
            $error = "No active account found for that email.";
            $_SESSION[$ip_key]['count']++;
        }
    }
}

function clean_email(string $v): string {
    return filter_var(trim($v), FILTER_VALIDATE_EMAIL) ?: '';
}

$remaining = $max_tries - ($_SESSION[$ip_key]['count'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AfyaCall – Sign In</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/afyacall-icon.svg">

    <!-- Apple touch icon (iOS home screen) -->
    <link rel="apple-touch-icon" href="/assets/images/afyacall-icon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <div class="logo-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0d6e6e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.06 6.06l1.77-1.77a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <h1>AfyaCall</h1>
            <p>Sign in to your account</p>
        </div>

        <?php if($timeout): ?>
        <div class="alert alert-warning" style="background:#fff4e0;color:#c07c1a;border:1px solid #f4d08a;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Your session expired after 30 minutes of inactivity.
        </div>
        <?php endif; ?>

        <?php if($locked): ?>
        <div class="alert alert-danger">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Too many failed attempts. Please wait 10 minutes before trying again.
        </div>
        <?php elseif($error): ?>
        <div class="alert alert-danger">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <?php echo htmlspecialchars($error); ?>
            <?php if($remaining <= 2 && $remaining > 0): ?>
            <span style="display:block;margin-top:4px;font-size:.8rem;"><?php echo $remaining; ?> attempt<?php echo $remaining==1?'':'s'; ?> remaining before lockout.</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off" <?php if($locked) echo 'style="opacity:.5;pointer-events:none;"'; ?>>
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="you@hospital.com" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="pwdField" class="form-control" placeholder="Enter your password" required>
                    <button type="button" class="password-toggle" onclick="togglePwd()" title="Show / hide">
                        <svg id="eyeIcon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
            <div style="margin-bottom:18px;text-align:right;">
                <a href="forgot_password.php" style="font-size:.82rem;color:var(--primary);font-weight:600;">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Sign In
            </button>
        </form>
    </div>
</div>
<script>
function togglePwd(){
    var f = document.getElementById('pwdField');
    f.type = f.type === 'password' ? 'text' : 'password';
}
// Warn before leaving if user has typed
(function(){
    var dirty = false;
    document.querySelector('form').addEventListener('input', function(){ dirty = true; });
    window.addEventListener('beforeunload', function(e){
        if(dirty){ e.preventDefault(); e.returnValue = ''; }
    });
})();
</script>
</body>
</html>
