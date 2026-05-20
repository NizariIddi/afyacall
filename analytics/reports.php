<?php
include("../config/session.php");
include("../config/database.php");
$patients    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM patients"))['t'] ?? 0;
$records     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM medical_records"))['t'] ?? 0;
$appointments= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments"))['t'] ?? 0;
$ussd        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM ussd_logs"))['t'] ?? 0;
$swahili     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM ussd_logs WHERE language='Swahili'"))['t'] ?? 0;
$english     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM ussd_logs WHERE language='English'"))['t'] ?? 0;
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">Analytics</div>
                <div class="page-subtitle">System overview and usage statistics</div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon teal"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                <div><div class="stat-label">Total Patients</div><div class="stat-value"><?php echo number_format($patients); ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
                <div><div class="stat-label">Medical Records</div><div class="stat-value"><?php echo number_format($records); ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                <div><div class="stat-label">Appointments</div><div class="stat-value"><?php echo number_format($appointments); ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.06 6.06l1.77-1.77a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
                <div><div class="stat-label">USSD Requests</div><div class="stat-value"><?php echo number_format($ussd); ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div>
                <div><div class="stat-label">Swahili Users</div><div class="stat-value"><?php echo number_format($swahili); ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div>
                <div><div class="stat-label">English Users</div><div class="stat-value"><?php echo number_format($english); ?></div></div>
            </div>
        </div>

        <?php if($ussd > 0): ?>
        <div class="card card-body">
            <div style="font-weight:700;font-size:.95rem;margin-bottom:16px;">Language Distribution</div>
            <?php
            $sw_pct = $ussd > 0 ? round($swahili/$ussd*100) : 0;
            $en_pct = $ussd > 0 ? round($english/$ussd*100) : 0;
            ?>
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:.83rem;font-weight:600;">Swahili</span>
                    <span style="font-size:.83rem;color:var(--text-muted);"><?php echo $sw_pct; ?>%</span>
                </div>
                <div style="height:8px;background:var(--border);border-radius:10px;overflow:hidden;">
                    <div style="height:100%;width:<?php echo $sw_pct; ?>%;background:var(--primary);border-radius:10px;"></div>
                </div>
            </div>
            <div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:.83rem;font-weight:600;">English</span>
                    <span style="font-size:.83rem;color:var(--text-muted);"><?php echo $en_pct; ?>%</span>
                </div>
                <div style="height:8px;background:var(--border);border-radius:10px;overflow:hidden;">
                    <div style="height:100%;width:<?php echo $en_pct; ?>%;background:#3b5bdb;border-radius:10px;"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>
<?php include("../includes/footer.php"); ?>
