<?php
include("../config/session.php");
include("../config/database.php");

$id = intval($_GET['id'] ?? 0);

// Patient
$patient = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM patients WHERE id='$id'"));
if (!$patient) { header("Location: view_patients.php"); exit(); }

// Stats
$rec_count  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM medical_records WHERE patient_id='$id'"))['t'];
$appt_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments WHERE patient_id='$id'"))['t'];
$appt_pend  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments WHERE patient_id='$id' AND status='Pending'"))['t'];
$last_visit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT visit_date FROM medical_records WHERE patient_id='$id' ORDER BY visit_date DESC LIMIT 1"));

// Medical records (latest 5)
$records = mysqli_query($conn, "
    SELECT medical_records.*, admins.full_name AS doctor_name
    FROM medical_records
    INNER JOIN admins ON medical_records.recorded_by = admins.id
    WHERE patient_id='$id'
    ORDER BY visit_date DESC LIMIT 5
");

// Appointments (latest 5)
$appointments = mysqli_query($conn, "
    SELECT * FROM appointments WHERE patient_id='$id' ORDER BY appointment_date DESC LIMIT 5
");

// Age calc
$age = '';
if ($patient['date_of_birth']) {
    $dob = new DateTime($patient['date_of_birth']);
    $age = $dob->diff(new DateTime())->y . ' yrs';
}
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title"><?php echo htmlspecialchars($patient['full_name']); ?></div>
                <div class="page-subtitle">
                    Patient Profile &mdash;
                    <span style="font-family:var(--font-mono);"><?php echo htmlspecialchars($patient['patient_code']); ?></span>
                </div>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="edit_patient.php?id=<?php echo $id; ?>" class="btn btn-warning">Edit Patient</a>
                <a href="patient_card.php?id=<?php echo $id; ?>" class="btn btn-outline" target="_blank">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Print Card
                </a>
                <a href="view_patients.php" class="btn btn-outline">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back
                </a>
            </div>
        </div>

        <!-- Profile header card -->
        <div class="card card-body" style="margin-bottom:20px;">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($patient['full_name'],0,2)); ?>
                </div>
                <div class="profile-info">
                    <h2 style="font-size:1.3rem;font-weight:800;margin-bottom:4px;"><?php echo htmlspecialchars($patient['full_name']); ?></h2>
                    <div style="font-family:var(--font-mono);font-size:.8rem;color:var(--text-muted);margin-bottom:10px;"><?php echo htmlspecialchars($patient['patient_code']); ?></div>
                    <div class="profile-meta">
                        <span class="badge <?php echo $patient['gender']==='Male' ? 'badge-blue' : 'badge-primary'; ?>"><?php echo htmlspecialchars($patient['gender']); ?></span>
                        <?php if($age): ?><span class="profile-meta-item"><?php echo $age; ?></span><?php endif; ?>
                        <?php if($patient['blood_group']): ?><span class="badge badge-danger"><?php echo htmlspecialchars($patient['blood_group']); ?></span><?php endif; ?>
                        <?php if($patient['date_of_birth']): ?><span class="profile-meta-item">DOB: <?php echo htmlspecialchars($patient['date_of_birth']); ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="profile-contact">
                    <?php if($patient['phone']): ?>
                    <div class="profile-contact-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12"/></svg>
                        <?php echo htmlspecialchars($patient['phone']); ?>
                    </div>
                    <?php endif; ?>
                    <?php if($patient['address']): ?>
                    <div class="profile-contact-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?php echo htmlspecialchars($patient['address']); ?>
                    </div>
                    <?php endif; ?>
                    <?php if($patient['emergency_contact']): ?>
                    <div class="profile-contact-item" style="color:var(--danger);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Emergency: <?php echo htmlspecialchars($patient['emergency_contact']); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stats row -->
        <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
            <div class="stat-card">
                <div class="stat-icon green"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg></div>
                <div><div class="stat-label">Records</div><div class="stat-value" style="font-size:1.5rem;"><?php echo $rec_count; ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                <div><div class="stat-label">Appointments</div><div class="stat-value" style="font-size:1.5rem;"><?php echo $appt_count; ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon <?php echo $appt_pend > 0 ? 'amber' : 'teal'; ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                <div><div class="stat-label">Pending Appts</div><div class="stat-value" style="font-size:1.5rem;"><?php echo $appt_pend; ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon teal"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                <div><div class="stat-label">Last Visit</div><div class="stat-value" style="font-size:.95rem;margin-top:4px;"><?php echo $last_visit ? htmlspecialchars($last_visit['visit_date']) : '—'; ?></div></div>
            </div>
        </div>

        <!-- Records + Appointments side by side -->
        <div class="dash-grid">

            <!-- Medical Records -->
            <div class="card card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <div style="font-weight:700;font-size:.95rem;">Recent Medical Records</div>
                    <a href="../medical_records/patient_history.php?id=<?php echo $id; ?>" class="btn btn-outline btn-sm">View All</a>
                </div>
                <?php if(mysqli_num_rows($records) == 0): ?>
                    <div class="empty-state" style="padding:24px 0;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                        <p style="margin-top:8px;">No records yet</p>
                    </div>
                <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:12px;">
                <?php while($r = mysqli_fetch_assoc($records)): ?>
                    <div style="border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 14px;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                            <span style="font-size:.78rem;font-weight:700;color:var(--primary);"><?php echo htmlspecialchars($r['visit_date']); ?></span>
                            <span style="font-size:.72rem;color:var(--text-muted);">Dr. <?php echo htmlspecialchars($r['doctor_name']); ?></span>
                        </div>
                        <div style="font-size:.875rem;font-weight:600;margin-bottom:3px;"><?php echo htmlspecialchars($r['diagnosis']); ?></div>
                        <?php if($r['medication']): ?>
                        <div style="font-size:.8rem;color:var(--text-muted);">💊 <?php echo htmlspecialchars($r['medication']); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
                </div>
                <?php endif; ?>
                <div style="margin-top:14px;">
                    <a href="../medical_records/add_record.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">+ Add Record</a>
                </div>
            </div>

            <!-- Appointments -->
            <div class="card card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <div style="font-weight:700;font-size:.95rem;">Appointments</div>
                    <a href="../appointments/view_appointments.php" class="btn btn-outline btn-sm">View All</a>
                </div>
                <?php if(mysqli_num_rows($appointments) == 0): ?>
                    <div class="empty-state" style="padding:24px 0;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/></svg>
                        <p style="margin-top:8px;">No appointments</p>
                    </div>
                <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:12px;">
                <?php while($a = mysqli_fetch_assoc($appointments)): ?>
                    <div style="border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 14px;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-size:.875rem;font-weight:600;"><?php echo htmlspecialchars($a['appointment_date']); ?></div>
                            <?php if($a['remarks']): ?>
                            <div style="font-size:.8rem;color:var(--text-muted);margin-top:2px;"><?php echo htmlspecialchars(substr($a['remarks'],0,50)); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php
                        $sc = $a['status']==='Completed' ? 'badge-success' : ($a['status']==='Missed' ? 'badge-danger' : 'badge-warning');
                        ?>
                        <span class="badge <?php echo $sc; ?>"><?php echo htmlspecialchars($a['status']); ?></span>
                    </div>
                <?php endwhile; ?>
                </div>
                <?php endif; ?>
                <div style="margin-top:14px;">
                    <a href="../appointments/add_appointment.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">+ Schedule Appointment</a>
                </div>
            </div>

        </div>

    </main>
</div>
<?php include("../includes/footer.php"); ?>
