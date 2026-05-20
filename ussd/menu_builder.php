<?php
include("../config/session.php");
require_role(['admin','doctor']);
include("../config/database.php");
include("../config/csrf.php");
include("../config/sanitize.php");
include("../config/activity_log.php");

$success = $error = '';

// ── ADD / EDIT ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action']??'', ['add','edit'])) {
    csrf_verify();
    $action      = $_POST['action'];
    $menu_path   = db_clean($conn, $_POST['menu_path']  ?? '');
    $menu_text   = db_clean($conn, $_POST['menu_text']  ?? '');
    $language    = clean_enum($_POST['language'] ?? '', ['sw','en','both'], 'both');
    $is_terminal = isset($_POST['is_terminal']) ? 1 : 0;
    $description = db_clean($conn, $_POST['description'] ?? '');

    // Validate path: only digits and *
    if (!preg_match('/^[0-9*]+$/', $menu_path)) {
        $error = "Menu path must contain only digits and * (e.g. 1*3*2)";
    } elseif (empty($menu_text)) {
        $error = "Menu text cannot be empty.";
    } else {
        if ($action === 'add') {
            $chk = mysqli_query($conn, "SELECT id FROM ussd_menus WHERE menu_path='$menu_path' AND language='$language' LIMIT 1");
            if (mysqli_num_rows($chk) > 0) {
                $error = "A menu entry for path <strong>$menu_path</strong> in that language already exists. Edit it instead.";
            } else {
                mysqli_query($conn, "INSERT INTO ussd_menus (menu_path,menu_text,language,is_terminal,description,is_active,created_by,created_at)
                    VALUES ('$menu_path','$menu_text','$language','$is_terminal','$description',1,'{$_SESSION['admin_id']}',NOW())");
                log_activity($conn, "Added USSD menu node: $menu_path ($language)", 'ussd', mysqli_insert_id($conn));
                $success = "Menu node <strong>$menu_path</strong> added.";
            }
        } else {
            $id = clean_int($_POST['id'] ?? 0);
            mysqli_query($conn, "UPDATE ussd_menus SET menu_path='$menu_path',menu_text='$menu_text',language='$language',is_terminal='$is_terminal',description='$description' WHERE id='$id'");
            log_activity($conn, "Updated USSD menu node: $menu_path", 'ussd', $id);
            $success = "Menu node updated.";
        }
    }
}

// ── TOGGLE ACTIVE ─────────────────────────────────────────────────────────
if (isset($_GET['toggle'])) {
    $tid = clean_int($_GET['toggle']);
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_active,menu_path FROM ussd_menus WHERE id='$tid' LIMIT 1"));
    if ($row) {
        $ns = $row['is_active'] ? 0 : 1;
        mysqli_query($conn, "UPDATE ussd_menus SET is_active='$ns' WHERE id='$tid'");
        log_activity($conn, ($ns?'Enabled':'Disabled')." USSD menu: {$row['menu_path']}", 'ussd', $tid);
    }
    header("Location: menu_builder.php"); exit();
}

// ── DELETE ────────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $did = clean_int($_GET['delete']);
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT menu_path FROM ussd_menus WHERE id='$did' LIMIT 1"));
    if ($row) {
        mysqli_query($conn, "DELETE FROM ussd_menus WHERE id='$did'");
        log_activity($conn, "Deleted USSD menu node: {$row['menu_path']}", 'ussd', $did);
    }
    header("Location: menu_builder.php"); exit();
}

// ── FETCH EDIT TARGET ─────────────────────────────────────────────────────
$edit_node = null;
if (isset($_GET['edit'])) {
    $edit_id   = clean_int($_GET['edit']);
    $edit_node = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ussd_menus WHERE id='$edit_id' LIMIT 1"));
}

// ── LIST ──────────────────────────────────────────────────────────────────
$menus = mysqli_query($conn, "SELECT m.*, a.full_name AS creator FROM ussd_menus m LEFT JOIN admins a ON m.created_by=a.id ORDER BY CAST(REPLACE(m.menu_path,'*','.') AS CHAR), m.language");
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">USSD Menu Builder</div>
                <div class="page-subtitle">Edit and manage dynamic USSD menu nodes</div>
            </div>
            <a href="ussd_simulator.php" class="btn btn-outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                Test Simulator
            </a>
        </div>

        <?php if($success): ?>
        <div class="alert alert-success"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
        <div class="alert alert-danger"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Path Reference Card -->
        <div class="card card-body" style="margin-bottom:20px;background:#f8fafc;">
            <div style="font-weight:700;font-size:.85rem;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Menu Path Reference
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:8px;font-size:.8rem;">
                <div style="padding:6px 10px;background:#fff;border-radius:6px;border:1px solid var(--border);"><code style="color:var(--primary);font-family:var(--font-mono);">1</code> &nbsp;Swahili root → main menu</div>
                <div style="padding:6px 10px;background:#fff;border-radius:6px;border:1px solid var(--border);"><code style="color:var(--primary);font-family:var(--font-mono);">2</code> &nbsp;English root → main menu</div>
                <div style="padding:6px 10px;background:#fff;border-radius:6px;border:1px solid var(--border);"><code style="color:var(--primary);font-family:var(--font-mono);">1*1</code> &nbsp;Medical Records</div>
                <div style="padding:6px 10px;background:#fff;border-radius:6px;border:1px solid var(--border);"><code style="color:var(--primary);font-family:var(--font-mono);">1*2</code> &nbsp;Appointments</div>
                <div style="padding:6px 10px;background:#fff;border-radius:6px;border:1px solid var(--border);"><code style="color:var(--primary);font-family:var(--font-mono);">1*3</code> &nbsp;Health Tips menu</div>
                <div style="padding:6px 10px;background:#fff;border-radius:6px;border:1px solid var(--border);"><code style="color:var(--primary);font-family:var(--font-mono);">1*3*1</code> &nbsp;Tip: Nutrition</div>
                <div style="padding:6px 10px;background:#fff;border-radius:6px;border:1px solid var(--border);"><code style="color:var(--primary);font-family:var(--font-mono);">1*4</code> &nbsp;Emergency menu</div>
                <div style="padding:6px 10px;background:#fff;border-radius:6px;border:1px solid var(--border);"><code style="color:var(--primary);font-family:var(--font-mono);">1*4*1</code> &nbsp;Emergency numbers</div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

            <!-- Menu list -->
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Path</th>
                            <th>Lang</th>
                            <th>Type</th>
                            <th>Text Preview</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(mysqli_num_rows($menus) === 0): ?>
                        <tr><td colspan="6">
                            <div class="empty-state">
                                <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/></svg>
                                <h3>No custom menu nodes yet</h3>
                                <p>Built-in menus are always active. Add an override here to customise any node.</p>
                            </div>
                        </td></tr>
                    <?php else: ?>
                    <?php while($m = mysqli_fetch_assoc($menus)):
                        $lang_cls  = ['sw'=>'badge-primary','en'=>'badge-blue','both'=>'badge-success'];
                        $lang_lbl  = ['sw'=>'Swahili','en'=>'English','both'=>'Both'];
                    ?>
                        <tr style="<?php if(!$m['is_active']) echo 'opacity:.55;'; ?>">
                            <td><code style="font-family:var(--font-mono);font-weight:700;color:var(--primary);font-size:.85rem;"><?php echo htmlspecialchars($m['menu_path']); ?></code></td>
                            <td><span class="badge <?php echo $lang_cls[$m['language']]??'badge-primary'; ?>"><?php echo $lang_lbl[$m['language']]??$m['language']; ?></span></td>
                            <td>
                                <?php if($m['is_terminal']): ?>
                                <span class="badge badge-danger">END</span>
                                <?php else: ?>
                                <span class="badge badge-warning">CON</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width:220px;">
                                <div style="font-size:.8rem;white-space:pre-line;color:var(--text-muted);max-height:52px;overflow:hidden;"><?php echo htmlspecialchars(substr($m['menu_text'],0,120)).(strlen($m['menu_text'])>120?'…':''); ?></div>
                            </td>
                            <td>
                                <?php if($m['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                <span class="badge" style="background:#f0f4f8;color:var(--text-muted);">Off</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2" style="flex-wrap:nowrap;">
                                    <a href="?edit=<?php echo $m['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="?toggle=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline"><?php echo $m['is_active']?'Disable':'Enable'; ?></a>
                                    <a href="?delete=<?php echo $m['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this menu node?')">Del</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add / Edit form -->
            <div class="card card-body" style="position:sticky;top:24px;">
                <div style="font-weight:700;font-size:.95rem;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border);">
                    <?php echo $edit_node ? 'Edit Menu Node' : 'Add Menu Node'; ?>
                </div>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="<?php echo $edit_node ? 'edit' : 'add'; ?>">
                    <?php if($edit_node): ?><input type="hidden" name="id" value="<?php echo $edit_node['id']; ?>"><?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Menu Path <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="menu_path" class="form-control" style="font-family:var(--font-mono);"
                               value="<?php echo htmlspecialchars($edit_node['menu_path'] ?? ''); ?>"
                               placeholder="e.g. 1*3*1" pattern="[0-9*]+" required>
                        <div style="font-size:.72rem;color:var(--text-muted);margin-top:4px;">Digits and * only. Must match USSD input sequence.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Language</label>
                        <select name="language" class="form-control">
                            <option value="both"  <?php if(($edit_node['language']??'both')==='both')  echo 'selected'; ?>>Both (override all)</option>
                            <option value="sw"    <?php if(($edit_node['language']??'')==='sw')         echo 'selected'; ?>>Swahili only</option>
                            <option value="en"    <?php if(($edit_node['language']??'')==='en')         echo 'selected'; ?>>English only</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Menu Text <span style="color:var(--danger);">*</span></label>
                        <textarea name="menu_text" class="form-control" rows="6"
                                  placeholder="Use \n for new lines. For CON menus, list options like:\n1. Option one\n2. Option two"
                                  required><?php echo htmlspecialchars($edit_node['menu_text'] ?? ''); ?></textarea>
                        <div style="font-size:.72rem;color:var(--text-muted);margin-top:4px;">Max 182 chars for USSD. Use actual newlines.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description / Notes</label>
                        <input type="text" name="description" class="form-control"
                               value="<?php echo htmlspecialchars($edit_node['description'] ?? ''); ?>"
                               placeholder="Internal note about this node">
                    </div>

                    <div class="form-group" style="display:flex;align-items:center;gap:10px;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.875rem;font-weight:600;">
                            <input type="checkbox" name="is_terminal" value="1"
                                   <?php if($edit_node && $edit_node['is_terminal']) echo 'checked'; ?>
                                   style="width:16px;height:16px;accent-color:var(--primary);">
                            Terminal (END) — closes the session
                        </label>
                    </div>

                    <div style="border-top:1px solid var(--border);padding-top:16px;display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary btn-full">
                            <?php if($edit_node): ?>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>Update Node
                            <?php else: ?>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Add Node
                            <?php endif; ?>
                        </button>
                        <?php if($edit_node): ?>
                        <a href="menu_builder.php" class="btn btn-outline">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

        </div>
    </main>
</div>
<?php include("../includes/footer.php"); ?>
