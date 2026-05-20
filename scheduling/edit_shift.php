<?php
/**
 * edit_shift.php — Phase 4: Doctor Scheduling
 */
include("../config/session.php");
include("../config/database.php");
include("../config/csrf.php");
include("../config/sanitize.php");
include("../config/activity_log.php");

require_role(['admin']);

$id = intval($_GET['id'] ?? 0);
$shift = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM doctor_shifts WHERE id='$id'"));
if (!$shift) { header("Location: view_schedule.php"); exit(); }

$success = '';
$error   = '';

$doctors = mysqli_query($conn, "SELECT id, full_name FROM admins WHERE role IN ('doctor','admin') AND is_active=1 ORDER BY full_name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $doctor_id   = clean_int($_POST['doctor_id'] ?? 0);
    $shift_date  = clean_date($_POST['shift_date'] ?? '');
    $shift_type  = clean_enum($_POST['shift_type'] ?? '', ['morning','afternoon','night','custom']);
    $shift_start = db_clean($conn, $_POST['shift_start'] ?? '');
    $shift_end   = db_clean($conn, $_POST['shift_end']   ?? '');
    $department  = db_clean($conn, $_POST['department']  ?? '');
    $notes       = db_clean($conn, $_POST['notes']       ?? '');

    if (!$doctor_id || !$shift_date || !$shift_type || !$shift_start || !$shift_end) {
        $error = 'All required fields must be filled.';
    } else {
        mysqli_query($conn, "
            UPDATE doctor_shifts
            SET doctor_id='$doctor_id', shift_date='$shift_date', shift_type='$shift_type',
                shift_start='$shift_start', shift_end='$shift_end',
                department='$department', notes='$notes'
            WHERE id='$id'
        ");
        log_activity($conn, "Updated shift #$id for doctor #$doctor_id on $shift_date", 'scheduling', $id);
        $success = 'Shift updated successfully.';
        $shift = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM doctor_shifts WHERE id='$id'"));
    }
}
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title">Edit Shift</div>
                <div class="page-subtitle">Update shift details</div>
            </div>
            <a href="view_schedule.php" class="btn btn-outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back
            </a>
        </div>

        <?php if($error): ?><div class="alert alert-danger"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?php echo $success; ?></div><?php endif; ?>

        <div class="card card-body" style="max-width:680px;">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-row">
                    <div class="form-group" style="flex:2;">
                        <label class="form-label">Doctor <span style="color:var(--danger);">*</span></label>
                        <select name="doctor_id" class="form-control" required>
                            <?php while($d = mysqli_fetch_assoc($doctors)): ?>
                            <option value="<?php echo $d['id']; ?>" <?php if($shift['doctor_id']==$d['id']) echo 'selected'; ?>><?php echo htmlspecialchars($d['full_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Shift Date <span style="color:var(--danger);">*</span></label>
                        <input type="date" name="shift_date" class="form-control" value="<?php echo htmlspecialchars($shift['shift_date']); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Shift Type <span style="color:var(--danger);">*</span></label>
                        <select name="shift_type" class="form-control" required>
                            <?php foreach(['morning','afternoon','night','custom'] as $t): ?>
                            <option value="<?php echo $t; ?>" <?php if($shift['shift_type']===$t) echo 'selected'; ?>><?php echo ucfirst($t); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Time <span style="color:var(--danger);">*</span></label>
                        <input type="time" name="shift_start" class="form-control" value="<?php echo htmlspecialchars(substr($shift['shift_start'],0,5)); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Time <span style="color:var(--danger);">*</span></label>
                        <input type="time" name="shift_end" class="form-control" value="<?php echo htmlspecialchars(substr($shift['shift_end'],0,5)); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($shift['department'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" style="resize:vertical;"><?php echo htmlspecialchars($shift['notes'] ?? ''); ?></textarea>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="delete_shift.php?id=<?php echo $id; ?>" class="btn btn-danger" onclick="return confirm('Delete this shift permanently?')">Delete Shift</a>
                    <a href="view_schedule.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include("../includes/footer.php"); ?>
