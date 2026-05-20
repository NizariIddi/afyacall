<?php
include("../config/session.php");
require_role(['admin','doctor']);
include("../config/database.php");
include("../config/csrf.php");
include("../config/sanitize.php");
include("../config/activity_log.php");

$success = $error = '';

// ── ADD TIP ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'add') {
    csrf_verify();
    $title    = db_clean($conn, $_POST['title']    ?? '');
    $body_sw  = db_clean($conn, $_POST['body_sw']  ?? '');
    $body_en  = db_clean($conn, $_POST['body_en']  ?? '');
    $category = clean_enum($_POST['category']??'',
        ['nutrition','exercise','malaria','diabetes','hiv','hygiene','maternal','mental','general'],
        'general');
    $created_by = intval($_SESSION['admin_id']);

    if (!$title || !$body_sw || !$body_en) {
        $error = "Title and both language bodies are required.";
    } else {
        mysqli_query($conn, "INSERT INTO health_tips (title,body_sw,body_en,category,is_active,created_by,created_at)
            VALUES ('$title','$body_sw','$body_en','$category',1,'$created_by',NOW())");
        $tid = mysqli_insert_id($conn);
        log_activity($conn, "Added health tip: $title ($category)", 'telecom', $tid);
        $success = "Health tip <strong>" . htmlspecialchars($title) . "</strong> added to the library.";
    }
}

// ── BROADCAST TIP ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'broadcast') {
    csrf_verify();
    $tip_id  = clean_int($_POST['tip_id'] ?? 0);
    $lang    = clean_enum($_POST['language']??'', ['both','sw','en'], 'both');
    $target  = clean_enum($_POST['target']??'', ['all','swahili','english'], 'all');
    $sent_by = intval($_SESSION['admin_id']);

    $tip = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM health_tips WHERE id='$tip_id' LIMIT 1"));
    if (!$tip) {
        $error = "Tip not found.";
    } else {
        $recipients = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM patients"))['t'] ?? 0;
        $title_esc  = mysqli_real_escape_string($conn, $tip['title']);
        $msg_esc    = mysqli_real_escape_string($conn,
            $lang === 'en' ? $tip['body_en'] :
            ($lang === 'sw' ? $tip['body_sw'] : $tip['body_sw'] . "\n\n" . $tip['body_en'])
        );
        mysqli_query($conn, "INSERT INTO broadcast_logs
            (type,title,message,severity,language,target_audience,recipients_count,sent_by,sent_at)
            VALUES ('tip','$title_esc','$msg_esc','info','$lang','$target','$recipients','$sent_by',NOW())");
        $bid = mysqli_insert_id($conn);
        // Mark tip as last broadcast
        mysqli_query($conn, "UPDATE health_tips SET last_broadcast=NOW(), broadcast_count=broadcast_count+1 WHERE id='$tip_id'");
        log_activity($conn, "Broadcast health tip: {$tip['title']} to $recipients patients", 'telecom', $bid);
        $success = "Tip <strong>" . htmlspecialchars($tip['title']) . "</strong> broadcast to <strong>$recipients</strong> patients.";
    }
}

// ── TOGGLE ACTIVE ─────────────────────────────────────────────────────────
if (isset($_GET['toggle'])) {
    $tid = clean_int($_GET['toggle']);
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_active,title FROM health_tips WHERE id='$tid'"));
    if ($row) {
        mysqli_query($conn, "UPDATE health_tips SET is_active=".($row['is_active']?0:1)." WHERE id='$tid'");
    }
    header("Location: health_tips.php"); exit();
}

// ── DELETE ────────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $did = clean_int($_GET['delete']);
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT title FROM health_tips WHERE id='$did'"));
    if ($row) {
        mysqli_query($conn, "DELETE FROM health_tips WHERE id='$did'");
        log_activity($conn, "Deleted health tip: {$row['title']}", 'telecom', $did);
    }
    header("Location: health_tips.php"); exit();
}

// ── FILTER + LIST ──────────────────────────────────────────────────────────
$cat_f  = db_clean($conn, $_GET['category'] ?? '');
$where  = $cat_f ? "WHERE category='$cat_f'" : '';
$tips   = mysqli_query($conn, "SELECT t.*, a.full_name AS creator FROM health_tips t LEFT JOIN admins a ON t.created_by=a.id $where ORDER BY t.id DESC");

$cats = ['nutrition'=>'Nutrition','exercise'=>'Exercise','malaria'=>'Malaria',
         'diabetes'=>'Diabetes','hiv'=>'HIV/AIDS','hygiene'=>'Hygiene',
         'maternal'=>'Maternal','mental'=>'Mental Health','general'=>'General'];
$cat_colors = ['nutrition'=>'badge-success','exercise'=>'badge-primary','malaria'=>'badge-warning',
               'diabetes'=>'badge-blue','hiv'=>'badge-danger','hygiene'=>'badge-success',
               'maternal'=>'badge-primary','mental'=>'badge-blue','general'=>'badge-warning'];
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">Health Tips</div>
                <div class="page-subtitle">Library of health tips — compose and broadcast to patients</div>
            </div>
            <a href="broadcasts.php" class="btn btn-outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
                Broadcast Log
            </a>
        </div>

        <?php if($success): ?><div class="alert alert-success"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?php echo $success; ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert alert-danger"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg><?php echo $error; ?></div><?php endif; ?>

        <!-- Category filter tabs -->
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:20px;">
            <a href="health_tips.php" class="btn btn-sm <?php echo !$cat_f?'btn-primary':'btn-outline'; ?>">All</a>
            <?php foreach($cats as $k=>$v): ?>
            <a href="?category=<?php echo $k; ?>" class="btn btn-sm <?php echo $cat_f===$k?'btn-primary':'btn-outline'; ?>"><?php echo $v; ?></a>
            <?php endforeach; ?>
        </div>

        <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

            <!-- Tips list -->
            <div style="display:flex;flex-direction:column;gap:14px;">
                <?php if(mysqli_num_rows($tips) === 0): ?>
                <div class="card card-body">
                    <div class="empty-state">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        <h3>No health tips yet</h3>
                        <p>Add tips to the library to start broadcasting</p>
                    </div>
                </div>
                <?php else: ?>
                <?php while($tip = mysqli_fetch_assoc($tips)): ?>
                <div class="card" style="<?php if(!$tip['is_active']) echo 'opacity:.6;'; ?>">
                    <div class="card-body">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                                    <span class="badge <?php echo $cat_colors[$tip['category']]??'badge-primary'; ?>"><?php echo $cats[$tip['category']]??$tip['category']; ?></span>
                                    <?php if($tip['broadcast_count'] > 0): ?>
                                    <span style="font-size:.72rem;color:var(--text-muted);">Sent <?php echo $tip['broadcast_count']; ?> time<?php echo $tip['broadcast_count']>1?'s':''; ?></span>
                                    <?php endif; ?>
                                    <?php if(!$tip['is_active']): ?>
                                    <span class="badge" style="background:#f0f4f8;color:var(--text-muted);">Inactive</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-weight:700;font-size:.95rem;margin-bottom:6px;"><?php echo htmlspecialchars($tip['title']); ?></div>
                                <!-- Tabbed language preview -->
                                <div class="tip-tabs" data-id="<?php echo $tip['id']; ?>">
                                    <div style="display:flex;gap:4px;margin-bottom:8px;">
                                        <button class="tab-btn active" onclick="switchTab(<?php echo $tip['id']; ?>,'sw',this)" style="padding:3px 10px;border-radius:20px;border:1.5px solid var(--primary);background:var(--primary);color:#fff;font-size:.7rem;font-weight:700;cursor:pointer;">SW</button>
                                        <button class="tab-btn" onclick="switchTab(<?php echo $tip['id']; ?>,'en',this)" style="padding:3px 10px;border-radius:20px;border:1.5px solid var(--border);background:transparent;color:var(--text-muted);font-size:.7rem;font-weight:700;cursor:pointer;">EN</button>
                                    </div>
                                    <div class="tab-sw-<?php echo $tip['id']; ?>" style="font-size:.83rem;color:var(--text-muted);"><?php echo nl2br(htmlspecialchars($tip['body_sw'])); ?></div>
                                    <div class="tab-en-<?php echo $tip['id']; ?>" style="font-size:.83rem;color:var(--text-muted);display:none;"><?php echo nl2br(htmlspecialchars($tip['body_en'])); ?></div>
                                </div>
                            </div>
                            <!-- Actions -->
                            <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0;">
                                <!-- Quick broadcast form -->
                                <form method="POST" style="display:flex;gap:4px;flex-wrap:wrap;justify-content:flex-end;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="broadcast">
                                    <input type="hidden" name="tip_id" value="<?php echo $tip['id']; ?>">
                                    <select name="language" class="form-control" style="padding:5px 28px 5px 8px;font-size:.77rem;width:auto;">
                                        <option value="both">Both</option>
                                        <option value="sw">Swahili</option>
                                        <option value="en">English</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm"
                                            <?php if(!$tip['is_active']) echo 'disabled'; ?>
                                            onclick="return confirm('Broadcast this tip now?')">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
                                        Broadcast
                                    </button>
                                </form>
                                <div style="display:flex;gap:4px;justify-content:flex-end;">
                                    <a href="?toggle=<?php echo $tip['id']; ?>" class="btn btn-sm btn-outline"><?php echo $tip['is_active']?'Disable':'Enable'; ?></a>
                                    <a href="?delete=<?php echo $tip['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this tip?')">Del</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php endif; ?>
            </div>

            <!-- Add tip form -->
            <div class="card card-body" style="position:sticky;top:24px;">
                <div style="font-weight:700;font-size:.95rem;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border);">Add New Tip</div>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label class="form-label">Title <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Drink Clean Water" required maxlength="120">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-control">
                            <?php foreach($cats as $k=>$v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Swahili Body <span style="color:var(--danger);">*</span></label>
                        <textarea name="body_sw" class="form-control" rows="4" placeholder="Kiswahili…" required maxlength="300"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">English Body <span style="color:var(--danger);">*</span></label>
                        <textarea name="body_en" class="form-control" rows="4" placeholder="English…" required maxlength="300"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Save to Library
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>
<script>
function switchTab(id, lang, btn) {
    document.querySelector('.tab-sw-'+id).style.display = lang==='sw' ? 'block' : 'none';
    document.querySelector('.tab-en-'+id).style.display = lang==='en' ? 'block' : 'none';
    btn.closest('.tip-tabs').querySelectorAll('.tab-btn').forEach(function(b){
        b.style.background = 'transparent';
        b.style.color = 'var(--text-muted)';
        b.style.borderColor = 'var(--border)';
    });
    btn.style.background = 'var(--primary)';
    btn.style.color = '#fff';
    btn.style.borderColor = 'var(--primary)';
}
</script>
<?php include("../includes/footer.php"); ?>
