<?php

include("../config/session.php");
include("../config/database.php");

$patient_id = $_GET['id'];

// Get patient info first
$patient_query = "SELECT * FROM patients WHERE id='$patient_id'";
$patient_result = mysqli_query($conn, $patient_query);
$patient = mysqli_fetch_assoc($patient_result);

// Get medical history
$query = "
SELECT
    medical_records.*,
    admins.full_name AS doctor_name
FROM medical_records
INNER JOIN admins ON medical_records.recorded_by = admins.id
WHERE patient_id='$patient_id'
ORDER BY visit_date DESC
";

$result = mysqli_query($conn, $query);

?>

<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title">Patient History</div>
                <div class="page-subtitle">
                    <?php if($patient): ?>
                        <?php echo htmlspecialchars($patient['full_name']); ?>
                        &nbsp;<span style="font-family:var(--font-mono);font-size:.8rem;color:var(--text-muted);"><?php echo htmlspecialchars($patient['patient_code']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <a href="../patients/view_patients.php" class="btn btn-outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Patients
            </a>
        </div>

        <?php if(mysqli_num_rows($result) == 0): ?>
        <div class="card card-body">
            <div class="empty-state">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                <h3>No medical history yet</h3>
                <p>No records have been added for this patient</p>
            </div>
        </div>
        <?php endif; ?>

        <?php while($record = mysqli_fetch_assoc($result)): ?>
        <div class="card card-body" style="margin-bottom:16px;">

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:8px;">
                <div>
                    <div style="font-weight:700;font-size:.95rem;">Visit: <?php echo htmlspecialchars($record['visit_date']); ?></div>
                    <?php if($record['next_appointment']): ?>
                    <div style="font-size:.8rem;color:var(--text-muted);margin-top:2px;">Next appointment: <?php echo htmlspecialchars($record['next_appointment']); ?></div>
                    <?php endif; ?>
                </div>
                <span class="badge badge-primary"><?php echo htmlspecialchars($record['doctor_name']); ?></span>
            </div>

            <div class="history-grid">

                <div>
                    <div class="form-label" style="margin-bottom:4px;">Diagnosis</div>
                    <div style="font-size:.875rem;"><?php echo nl2br(htmlspecialchars($record['diagnosis'])); ?></div>
                </div>

                <div>
                    <div class="form-label" style="margin-bottom:4px;">Medication</div>
                    <div style="font-size:.875rem;"><?php echo $record['medication'] ? nl2br(htmlspecialchars($record['medication'])) : '<span style="color:var(--text-muted);">—</span>'; ?></div>
                </div>

                <div>
                    <div class="form-label" style="margin-bottom:4px;">Allergies</div>
                    <div style="font-size:.875rem;"><?php echo $record['allergies'] ? nl2br(htmlspecialchars($record['allergies'])) : '<span style="color:var(--text-muted);">None recorded</span>'; ?></div>
                </div>

                <div>
                    <div class="form-label" style="margin-bottom:4px;">Clinical Notes</div>
                    <div style="font-size:.875rem;"><?php echo $record['notes'] ? nl2br(htmlspecialchars($record['notes'])) : '<span style="color:var(--text-muted);">—</span>'; ?></div>
                </div>

            </div>

        </div>
        <?php endwhile; ?>

    </main>
</div>
<?php include("../includes/footer.php"); ?>
