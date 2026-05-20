<?php
include("../config/session.php");
require_role(['admin']);
include("../config/database.php");
include("../config/csrf.php");
include("../config/sanitize.php");
include("../config/activity_log.php");

$success = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    foreach ($_POST as $key => $value) {
        if ($key === 'csrf_token') continue;
        $k = db_clean($conn, $key);
        $v = db_clean($conn, $value);
        mysqli_query($conn, "UPDATE settings SET setting_value='$v' WHERE setting_key='$k'");
    }
    log_activity($conn, 'Updated system settings', 'settings');
    $success = "Settings saved successfully.";
}
$settings = [];
$q = mysqli_query($conn, "SELECT * FROM settings");
while ($row = mysqli_fetch_assoc($q)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">Settings</div>
                <div class="page-subtitle">System configuration and preferences</div>
            </div>
        </div>
        <?php if($success): ?><div class="alert alert-success"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?php echo $success; ?></div><?php endif; ?>
        <div class="card card-body" style="max-width:680px;">
            <div style="font-weight:700;font-size:.95rem;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid var(--border);">Hospital Configuration</div>
            <form method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="form-label">Hospital Name</label>
                    <input type="text" name="hospital_name" class="form-control" value="<?php echo htmlspecialchars($settings['hospital_name'] ?? ''); ?>" placeholder="e.g. Muhimbili National Hospital">
                </div>
                <div class="form-group">
                    <label class="form-label">Welcome Message</label>
                    <input type="text" name="welcome_message" class="form-control" value="<?php echo htmlspecialchars($settings['welcome_message'] ?? ''); ?>" placeholder="USSD welcome message shown to patients">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Default Language</label>
                        <select name="default_language" class="form-control">
                            <option value="Swahili" <?php if(($settings['default_language'] ?? '')==="Swahili") echo "selected"; ?>>Swahili</option>
                            <option value="English" <?php if(($settings['default_language'] ?? '')==="English") echo "selected"; ?>>English</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">USSD Shortcode</label>
                        <input type="text" name="ussd_shortcode" class="form-control" value="<?php echo htmlspecialchars($settings['ussd_shortcode'] ?? ''); ?>" placeholder="e.g. *384*7#">
                    </div>
                </div>
                <div style="padding-top:8px;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include("../includes/footer.php"); ?>
