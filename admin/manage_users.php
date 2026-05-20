<?php
include("../config/session.php");
require_role(['admin']);
include("../config/database.php");
include("../config/csrf.php");
include("../config/sanitize.php");
include("../config/activity_log.php");

$success = $error = '';

// ── ADD USER ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    csrf_verify();
    $full_name = db_clean($conn, $_POST['full_name'] ?? '');
    $email     = clean_email($_POST['email'] ?? '');
    $role      = clean_enum($_POST['role'] ?? '', ['admin','doctor','nurse'], 'nurse');
    $password  = $_POST['password'] ?? '';

    if (!$full_name || !$email || strlen($password) < 8) {
        $error = "All fields required; password must be at least 8 characters.";
    } else {
        $chk = mysqli_query($conn, "SELECT id FROM admins WHERE email='$email' LIMIT 1");
        if (mysqli_num_rows($chk) > 0) {
            $error = "An account with that email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            mysqli_query($conn, "INSERT INTO admins (full_name,email,password,role,is_active,created_at) VALUES ('$full_name','$email','$hash','$role',1,NOW())");
            $new_id = mysqli_insert_id($conn);
            log_activity($conn, "Created user: $full_name ($role)", 'admin', $new_id);
            $success = "User <strong>" . htmlspecialchars($full_name) . "</strong> added successfully.";
        }
    }
}

// ── TOGGLE ACTIVE ─────────────────────────────────────────────────────────
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $tid = intval($_GET['toggle']);
    if ($tid !== intval($_SESSION['admin_id'])) { // Can't deactivate yourself
        $cur = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_active,full_name FROM admins WHERE id='$tid'"));
        $new_status = $cur['is_active'] ? 0 : 1;
        mysqli_query($conn, "UPDATE admins SET is_active='$new_status' WHERE id='$tid'");
        $action = $new_status ? 'Activated' : 'Deactivated';
        log_activity($conn, "$action user: {$cur['full_name']}", 'admin', $tid);
    }
    header("Location: manage_users.php");
    exit();
}

// ── DELETE ────────────────────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $did = intval($_GET['delete']);
    if ($did !== intval($_SESSION['admin_id'])) {
        $du = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name FROM admins WHERE id='$did'"));
        mysqli_query($conn, "DELETE FROM admins WHERE id='$did'");
        log_activity($conn, "Deleted user: {$du['full_name']}", 'admin', $did);
    }
    header("Location: manage_users.php");
    exit();
}

$users = mysqli_query($conn, "SELECT * FROM admins ORDER BY role, full_name");
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">User Management</div>
                <div class="page-subtitle">Manage staff accounts and roles</div>
            </div>
        </div>

        <?php if($success): ?>
        <div class="alert alert-success">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <?php echo $success; ?>
        </div>
        <?php endif; ?>
        <?php if($error): ?>
        <div class="alert alert-danger">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

            <!-- User list -->
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($u = mysqli_fetch_assoc($users)): ?>
                        <?php
                        $role_cls = ['admin'=>'badge-danger','doctor'=>'badge-primary','nurse'=>'badge-success'];
                        $is_me    = $u['id'] == $_SESSION['admin_id'];
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:36px;height:36px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:800;flex-shrink:0;">
                                        <?php echo strtoupper(substr($u['full_name'],0,2)); ?>
                                    </div>
                                    <div>
                                        <div class="patient-name"><?php echo htmlspecialchars($u['full_name']); ?><?php if($is_me) echo ' <span style="font-size:.72rem;color:var(--text-muted);font-weight:400;">(you)</span>'; ?></div>
                                        <div class="patient-code"><?php echo htmlspecialchars($u['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge <?php echo $role_cls[$u['role']] ?? 'badge-primary'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                            <td>
                                <?php if($u['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                <span class="badge badge-warning">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:.8rem;color:var(--text-muted);"><?php echo $u['last_login'] ? htmlspecialchars($u['last_login']) : '—'; ?></td>
                            <td>
                                <?php if(!$is_me): ?>
                                <div class="d-flex gap-2">
                                    <a href="?toggle=<?php echo $u['id']; ?>" class="btn btn-sm <?php echo $u['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                        <?php echo $u['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                    </a>
                                    <a href="?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger"
                                       onclick="return confirm('Delete <?php echo htmlspecialchars(addslashes($u['full_name'])); ?>?')">
                                        Delete
                                    </a>
                                </div>
                                <?php else: ?>
                                <span style="font-size:.8rem;color:var(--text-muted);">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add user form -->
            <div class="card card-body" style="position:sticky;top:24px;">
                <div style="font-weight:700;font-size:.95rem;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border);">Add New User</div>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="Dr. Jane Doe" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="jane@hospital.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-control">
                            <option value="nurse">Nurse</option>
                            <option value="doctor">Doctor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Temporary Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Create User
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>
<?php include("../includes/footer.php"); ?>
