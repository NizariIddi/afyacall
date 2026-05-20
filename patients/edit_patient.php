<?php
include("../config/session.php");
include("../config/database.php");
include("../config/csrf.php");
include("../config/sanitize.php");
include("../config/activity_log.php");

if (!can('edit_patient')) { include("../includes/403.php"); exit(); }

$id     = clean_int($_GET['id'] ?? 0);
$result = mysqli_query($conn, "SELECT * FROM patients WHERE id='$id' LIMIT 1");
if (!mysqli_num_rows($result)) { header("Location: view_patients.php"); exit(); }
$patient = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $full_name         = db_clean($conn, $_POST['full_name'] ?? '');
    $phone             = db_clean($conn, $_POST['phone'] ?? '');
    $address           = db_clean($conn, $_POST['address'] ?? '');
    $blood_group       = clean_enum($_POST['blood_group'] ?? '', ['A+','A-','B+','B-','AB+','AB-','O+','O-','']);
    $emergency_contact = db_clean($conn, $_POST['emergency_contact'] ?? '');

    mysqli_query($conn, "UPDATE patients SET full_name='$full_name',phone='$phone',address='$address',blood_group='$blood_group',emergency_contact='$emergency_contact' WHERE id='$id'");
    log_activity($conn, "Updated patient: $full_name", 'patients', $id);
    header("Location: view_patients.php");
    exit();
}
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">Edit Patient</div>
                <div class="page-subtitle">Update patient information</div>
            </div>
            <a href="view_patients.php" class="btn btn-outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Patients
            </a>
        </div>
        <div class="card card-body" style="max-width:680px;">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($patient['full_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($patient['phone']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Blood Group</label>
                        <select name="blood_group" class="form-control">
                            <option value="">Unknown / Not tested</option>
                            <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                            <option <?php if($patient['blood_group']==$bg) echo 'selected'; ?>><?php echo $bg; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Emergency Contact</label>
                        <input type="text" name="emergency_contact" class="form-control" value="<?php echo htmlspecialchars($patient['emergency_contact']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control"><?php echo htmlspecialchars($patient['address']); ?></textarea>
                </div>
                <div style="border-top:1px solid var(--border);padding-top:20px;display:flex;gap:10px;">
                    <button type="submit" class="btn btn-success btn-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Update Patient
                    </button>
                    <a href="view_patients.php" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include("../includes/footer.php"); ?>
