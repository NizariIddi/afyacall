<?php
include("../config/session.php");
include("../config/database.php");
include("../config/csrf.php");
include("../config/sanitize.php");
include("../config/activity_log.php");

if (!can('add_patient')) { include("../includes/403.php"); exit(); }

$success = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $full_name         = db_clean($conn, $_POST['full_name'] ?? '');
    $gender            = clean_enum($_POST['gender'] ?? '', ['Male','Female']);
    $date_of_birth     = clean_date($_POST['date_of_birth'] ?? '');
    $phone             = db_clean($conn, $_POST['phone'] ?? '');
    $address           = db_clean($conn, $_POST['address'] ?? '');
    $blood_group       = clean_enum($_POST['blood_group'] ?? '', ['A+','A-','B+','B-','AB+','AB-','O+','O-','']);
    $emergency_contact = db_clean($conn, $_POST['emergency_contact'] ?? '');
    $patient_code      = "AC-" . rand(10000, 99999);
    $registered_by     = intval($_SESSION['admin_id']);

    if (!$full_name || !$gender) {
        $error = "Full name and gender are required.";
    } else {
        mysqli_query($conn, "INSERT INTO patients (patient_code,full_name,gender,date_of_birth,phone,address,blood_group,emergency_contact,registered_by,created_at)
            VALUES ('$patient_code','$full_name','$gender','$date_of_birth','$phone','$address','$blood_group','$emergency_contact','$registered_by',NOW())");
        $new_id = mysqli_insert_id($conn);
        log_activity($conn, "Added patient: $full_name ($patient_code)", 'patients', $new_id);
        $success = "Patient registered. Code: <strong>$patient_code</strong>";
    }
}
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">Add Patient</div>
                <div class="page-subtitle">Register a new patient in the system</div>
            </div>
            <a href="view_patients.php" class="btn btn-outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Patients
            </a>
        </div>

        <?php if(!empty($error)): ?><div class="alert alert-danger"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?php echo $success; ?></div><?php endif; ?>

        <div class="card card-body">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="full_name" class="form-control" placeholder="e.g. Amina Hassan" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gender <span style="color:var(--danger);">*</span></label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select gender</option>
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="+255 7XX XXX XXX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Blood Group</label>
                        <select name="blood_group" class="form-control">
                            <option value="">Unknown / Not tested</option>
                            <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?><option><?php echo $bg; ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Emergency Contact</label>
                        <input type="text" name="emergency_contact" class="form-control" placeholder="Name &amp; phone">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" placeholder="Street, area, city…"></textarea>
                </div>
                <div style="border-top:1px solid var(--border);padding-top:20px;display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Patient
                    </button>
                    <a href="view_patients.php" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include("../includes/footer.php"); ?>
