<?php include("header.php"); ?>
<div class="app-wrapper">
    <?php include("sidebar.php"); ?>
    <main class="main-content">
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;text-align:center;gap:16px;">
            <div style="width:72px;height:72px;border-radius:50%;background:var(--danger-light);display:flex;align-items:center;justify-content:center;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#e05353" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div>
                <div style="font-size:2rem;font-weight:800;color:var(--text-main);">403 – Access Denied</div>
                <div style="font-size:.95rem;color:var(--text-muted);margin-top:8px;">
                    Your role (<strong><?php echo htmlspecialchars($_SESSION['admin_role'] ?? 'unknown'); ?></strong>) does not have permission to access this page.
                </div>
            </div>
            <a href="../dashboard/index.php" class="btn btn-primary">← Back to Dashboard</a>
        </div>
    </main>
</div>
<?php include("footer.php"); ?>
