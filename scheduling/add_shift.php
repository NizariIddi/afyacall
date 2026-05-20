<?php
/**
 * add_shift.php — Phase 4: Doctor Scheduling
 * Admin-only: assign a shift to a doctor.
 */
include("../config/session.php");
include("../config/database.php");
include("../config/csrf.php");
include("../config/sanitize.php");
include("../config/activity_log.php");

require_role(['admin']);

$success = '';
$error   = '';

// Load doctors for dropdown
$doctors = mysqli_query($conn, "SELECT id, full_name FROM admins WHERE role IN ('doctor','admin') AND is_active=1 ORDER BY full_name ASC");

// Shift type → default times
$shift_defaults = [
    'morning'   => ['07:00', '14:00'],
    'afternoon' => ['14:00', '21:00'],
    'night'     => ['21:00', '07:00'],
];

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
        $error = 'Doctor, date, shift type, and times are required.';
    } else {
        // Check for duplicate shift on same day for this doctor
        $dup = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT id FROM doctor_shifts WHERE doctor_id='$doctor_id' AND shift_date='$shift_date' AND shift_type='$shift_type' LIMIT 1"
        ));
        if ($dup) {
            $error = 'This doctor already has a ' . htmlspecialchars($shift_type) . ' shift on that date.';
        } else {
            mysqli_query($conn, "
                INSERT INTO doctor_shifts (doctor_id, shift_date, shift_type, shift_start, shift_end, department, notes, created_by)
                VALUES ('$doctor_id','$shift_date','$shift_type','$shift_start','$shift_end','$department','$notes','" . intval($_SESSION['admin_id']) . "')
            ");
            $new_id = mysqli_insert_id($conn);
            log_activity($conn, "Added shift for doctor #$doctor_id on $shift_date ($shift_type)", 'scheduling', $new_id);
            $success = 'Shift added successfully.';
        }
    }
}
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title">Add Shift</div>
                <div class="page-subtitle">Assign a shift to a doctor</div>
            </div>
            <a href="view_schedule.php" class="btn btn-outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Schedule
            </a>
        </div>

        <?php if($error): ?><div class="alert alert-danger">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <?php echo $error; ?>
        </div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <?php echo $success; ?>
        </div><?php endif; ?>

        <div class="card card-body" style="max-width:680px;">
            <form method="POST">
                <?php echo csrf_field(); ?>

                <div class="form-row">
                    <div class="form-group" style="flex:2;">
                        <label class="form-label">Doctor <span style="color:var(--danger);">*</span></label>
                        <select name="doctor_id" class="form-control" required>
                            <option value="">— Select doctor —</option>
                            <?php while($d = mysqli_fetch_assoc($doctors)): ?>
                            <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['full_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Shift Date <span style="color:var(--danger);">*</span></label>
                        <input type="date" name="shift_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Shift Type <span style="color:var(--danger);">*</span></label>
                        <select name="shift_type" class="form-control" id="shift_type" required onchange="applyShiftDefaults(this.value)">
                            <option value="">— Select —</option>
                            <option value="morning">Morning (07:00 – 14:00)</option>
                            <option value="afternoon">Afternoon (14:00 – 21:00)</option>
                            <option value="night">Night (21:00 – 07:00)</option>
                            <option value="custom">Custom hours</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Time <span style="color:var(--danger);">*</span></label>
                        <input type="time" name="shift_start" id="shift_start" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Time <span style="color:var(--danger);">*</span></label>
                        <input type="time" name="shift_end" id="shift_end" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" placeholder="e.g. General, Maternity, Paediatrics">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this shift…" style="resize:vertical;"></textarea>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary">Save Shift</button>
                    <a href="view_schedule.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>

    </main>
</div>

<script>
var defaults = {
    morning:   ['07:00', '14:00'],
    afternoon: ['14:00', '21:00'],
    night:     ['21:00', '07:00']
};
function applyShiftDefaults(type) {
    var d = defaults[type];
    if (d) {
        document.getElementById('shift_start').value = d[0];
        document.getElementById('shift_end').value   = d[1];
    }
}
</script>

<?php include("../includes/footer.php"); ?>
