<?php
ini_set('session.cookie_httponly', 1);
if (session_status() === PHP_SESSION_NONE) session_start();

include("../config/database.php");
include("../config/csrf.php");

$step    = 1;
$success = '';
$error   = '';

// ── STEP 1: Submit email ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === '1') {
    csrf_verify();
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $error = "Please enter a valid email address.";
        $step  = 1;
    } else {
        $e   = mysqli_real_escape_string($conn, $email);
        $res = mysqli_query($conn, "SELECT id FROM admins WHERE email='$e' AND is_active=1 LIMIT 1");

        // Always show same message (prevent email enumeration)
        if (mysqli_num_rows($res) > 0) {
            $admin = mysqli_fetch_assoc($res);
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            mysqli_query($conn, "UPDATE admins SET reset_token='$token', reset_expires='$expires' WHERE id='{$admin['id']}'");

            // In production: email the link. For now, show it on screen (dev mode).
            $_SESSION['reset_link'] = "reset_password.php?token=$token";
        }
        $step    = 2;
        $success = "If that email matches an account, a reset link has been generated.";
    }
}

// ── STEP 2: Use token ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === '2') {
    csrf_verify();
    $token = preg_replace('/[^a-f0-9]/', '', $_POST['token'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (strlen($token) !== 64) { $error = "Invalid token."; }
    elseif (strlen($pass) < 8)  { $error = "Password must be at least 8 characters."; }
    elseif ($pass !== $pass2)   { $error = "Passwords do not match."; }
    else {
        $res = mysqli_query($conn, "SELECT id FROM admins WHERE reset_token='$token' AND reset_expires > NOW() LIMIT 1");
        if (mysqli_num_rows($res) === 0) {
            $error = "This reset link has expired or is invalid.";
        } else {
            $admin = mysqli_fetch_assoc($res);
            $hash  = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
            mysqli_query($conn, "UPDATE admins SET password='$hash', reset_token=NULL, reset_expires=NULL WHERE id='{$admin['id']}'");
            $success = "Password updated successfully.";
        }
    }
}

// Token from GET (reset link)
$token_get = preg_replace('/[^a-f0-9]/', '', $_GET['token'] ?? '');
if ($token_get) $step = 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AfyaCall – Password Reset</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <div class="logo-icon" style="background:#fff4e0;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#f4a235" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <h1>Reset Password</h1>
            <p><?php echo $step===3 ? 'Enter your new password' : 'Recover access to your account'; ?></p>
        </div>

        <?php if($error): ?>
        <div class="alert alert-danger">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if($success): ?>
        <div class="alert alert-success">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <?php echo htmlspecialchars($success); ?>
            <?php if(isset($_SESSION['reset_link'])): ?>
            <div style="margin-top:10px;padding:10px;background:#f0f4f8;border-radius:7px;font-size:.8rem;">
                <strong>Dev mode – reset link:</strong><br>
                <a href="<?php echo htmlspecialchars($_SESSION['reset_link']); ?>" style="color:var(--primary);word-break:break-all;"><?php echo htmlspecialchars($_SESSION['reset_link']); ?></a>
            </div>
            <?php unset($_SESSION['reset_link']); endif; ?>
        </div>
        <?php if($step === 2 || str_contains($success,'updated')): ?>
        <a href="login.php" class="btn btn-primary btn-full" style="margin-top:4px;">Back to Login</a>
        <?php endif; ?>

        <?php elseif($step === 3): ?>
        <!-- New password form -->
        <form method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="step" value="2">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token_get); ?>">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required minlength="8">
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password2" class="form-control" placeholder="Repeat password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg">Set New Password</button>
        </form>

        <?php else: ?>
        <!-- Email request form -->
        <form method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="step" value="1">
            <div class="form-group">
                <label class="form-label">Your Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="you@hospital.com" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg">Send Reset Link</button>
            <div style="text-align:center;margin-top:16px;">
                <a href="login.php" style="font-size:.82rem;color:var(--text-muted);">← Back to login</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
