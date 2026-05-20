<?php
include("../config/session.php");
require_role(['admin','doctor']);
include("../config/database.php");
include("../config/csrf.php");
include("../config/sanitize.php");
include("../config/activity_log.php");

$success = $error = '';

// ── SEND ALERT ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'send') {
    csrf_verify();
    $title    = db_clean($conn, $_POST['title']   ?? '');
    $message  = db_clean($conn, $_POST['message'] ?? '');
    $severity = clean_enum($_POST['severity']??'', ['info','warning','critical'], 'info');
    $lang     = clean_enum($_POST['language']??'', ['sw','en','both'], 'both');
    $target   = clean_enum($_POST['target']??'', ['all','swahili','english'], 'all');
    $sent_by  = intval($_SESSION['admin_id']);

    if (!$title || !$message) {
        $error = "Title and message are required.";
    } else {
        // Count target recipients
        $pat_q = "SELECT COUNT(*) AS t FROM patients";
        $recipients = mysqli_fetch_assoc(mysqli_query($conn, $pat_q))['t'] ?? 0;

        mysqli_query($conn, "INSERT INTO broadcast_logs
            (type,title,message,severity,language,target_audience,recipients_count,sent_by,sent_at)
            VALUES ('alert','$title','$message','$severity','$lang','$target','$recipients','$sent_by',NOW())");
        $bid = mysqli_insert_id($conn);
        log_activity($conn, "Sent emergency alert: $title ($severity) to $recipients recipients", 'telecom', $bid);
        $success = "Emergency alert <strong>" . htmlspecialchars($title) . "</strong> broadcast to <strong>$recipients</strong> patients.";
    }
}

// ── RECENT ALERTS ─────────────────────────────────────────────────────────
$alerts = mysqli_query($conn,
    "SELECT b.*, a.full_name AS sender FROM broadcast_logs b
     LEFT JOIN admins a ON b.sent_by = a.id
     WHERE b.type='alert'
     ORDER BY b.sent_at DESC LIMIT 30");
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">Emergency Alerts</div>
                <div class="page-subtitle">Broadcast urgent health notifications to patients</div>
            </div>
        </div>

        <?php if($success): ?>
        <div class="alert alert-success"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
        <div class="alert alert-danger"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg><?php echo $error; ?></div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">

            <!-- Recent alerts log -->
            <div>
                <div style="font-weight:700;font-size:.95rem;margin-bottom:14px;">Broadcast History</div>
                <?php if(mysqli_num_rows($alerts) === 0): ?>
                <div class="card card-body">
                    <div class="empty-state">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <h3>No alerts sent yet</h3>
                        <p>Sent alerts will appear here</p>
                    </div>
                </div>
                <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:12px;">
                <?php while($al = mysqli_fetch_assoc($alerts)):
                    $sev_styles = [
                        'critical' => ['bg'=>'#fdeaea','border'=>'#f5b8b8','icon'=>'#e05353','label'=>'CRITICAL'],
                        'warning'  => ['bg'=>'#fff4e0','border'=>'#f4d08a','icon'=>'#f4a235','label'=>'WARNING'],
                        'info'     => ['bg'=>'#e6f4f4','border'=>'#b2dada','icon'=>'#0d6e6e','label'=>'INFO'],
                    ];
                    $s = $sev_styles[$al['severity']] ?? $sev_styles['info'];
                ?>
                <div class="card" style="border-left:4px solid <?php echo $s['border']; ?>;">
                    <div class="card-body">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                            <div style="flex:1;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                    <span style="padding:3px 9px;border-radius:20px;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;background:<?php echo $s['bg']; ?>;color:<?php echo $s['icon']; ?>;"><?php echo $s['label']; ?></span>
                                    <span style="font-size:.78rem;color:var(--text-muted);"><?php echo htmlspecialchars($al['sent_at']); ?></span>
                                </div>
                                <div style="font-weight:700;font-size:.95rem;margin-bottom:4px;"><?php echo htmlspecialchars($al['title']); ?></div>
                                <div style="font-size:.83rem;color:var(--text-muted);"><?php echo htmlspecialchars($al['message']); ?></div>
                            </div>
                            <div style="text-align:right;flex-shrink:0;">
                                <div style="font-size:1.4rem;font-weight:800;color:var(--text-main);"><?php echo number_format($al['recipients_count']); ?></div>
                                <div style="font-size:.72rem;color:var(--text-muted);">recipients</div>
                                <div style="font-size:.75rem;color:var(--text-muted);margin-top:4px;">by <?php echo htmlspecialchars($al['sender']??'—'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Compose form -->
            <div class="card card-body" style="position:sticky;top:24px;">
                <div style="font-weight:700;font-size:.95rem;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border);">
                    Compose Alert
                </div>

                <!-- Severity legend -->
                <div style="display:flex;gap:6px;margin-bottom:16px;">
                    <div style="flex:1;text-align:center;padding:8px 4px;border-radius:8px;background:#fdeaea;font-size:.7rem;font-weight:700;color:#e05353;text-transform:uppercase;letter-spacing:.4px;">Critical</div>
                    <div style="flex:1;text-align:center;padding:8px 4px;border-radius:8px;background:#fff4e0;font-size:.7rem;font-weight:700;color:#c07c1a;text-transform:uppercase;letter-spacing:.4px;">Warning</div>
                    <div style="flex:1;text-align:center;padding:8px 4px;border-radius:8px;background:#e6f4f4;font-size:.7rem;font-weight:700;color:#0d6e6e;text-transform:uppercase;letter-spacing:.4px;">Info</div>
                </div>

                <form method="POST" id="alertForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="send">

                    <div class="form-group">
                        <label class="form-label">Severity <span style="color:var(--danger);">*</span></label>
                        <select name="severity" class="form-control" id="severitySelect">
                            <option value="info">Info — General notification</option>
                            <option value="warning">Warning — Moderate risk</option>
                            <option value="critical">Critical — Immediate action required</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alert Title <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Cholera outbreak warning" required maxlength="120">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message <span style="color:var(--danger);">*</span></label>
                        <textarea name="message" class="form-control" rows="5"
                                  placeholder="Write the alert message here. Keep it concise and actionable."
                                  required maxlength="320" id="alertMsg"></textarea>
                        <div style="font-size:.72rem;color:var(--text-muted);margin-top:4px;text-align:right;"><span id="charCount">0</span>/320</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Language</label>
                        <select name="language" class="form-control">
                            <option value="both">Both (Swahili + English)</option>
                            <option value="sw">Swahili only</option>
                            <option value="en">English only</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Target Audience</label>
                        <select name="target" class="form-control">
                            <option value="all">All patients</option>
                            <option value="swahili">Swahili users only</option>
                            <option value="english">English users only</option>
                        </select>
                    </div>

                    <div id="criticalWarning" style="display:none;padding:10px 12px;border-radius:8px;background:#fdeaea;border:1px solid #f5b8b8;font-size:.8rem;color:#c03434;margin-bottom:14px;">
                        <strong>⚠ Critical alert</strong> — This will be sent to all patients immediately. Please confirm before sending.
                    </div>

                    <button type="submit" class="btn btn-danger btn-full btn-lg" onclick="return confirmSend(event)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Broadcast Alert
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>
<script>
// Char counter
var msgEl = document.getElementById('alertMsg');
var cntEl = document.getElementById('charCount');
if(msgEl) msgEl.addEventListener('input', function(){ cntEl.textContent = msgEl.value.length; });

// Severity warning
var sevEl = document.getElementById('severitySelect');
if(sevEl) sevEl.addEventListener('change', function(){
    document.getElementById('criticalWarning').style.display = this.value === 'critical' ? 'block' : 'none';
    var btn = document.querySelector('#alertForm button[type=submit]');
    btn.className = this.value === 'critical' ? 'btn btn-danger btn-full btn-lg' : 'btn btn-primary btn-full btn-lg';
});

function confirmSend(e) {
    var sev = document.getElementById('severitySelect').value;
    if (sev === 'critical') {
        return confirm('You are about to send a CRITICAL alert to all patients. Are you absolutely sure?');
    }
    return true;
}
</script>
<?php include("../includes/footer.php"); ?>
