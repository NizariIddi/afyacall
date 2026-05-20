<?php
include("../config/session.php");
include("../config/database.php");

// Core counts
$patients_count     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM patients"))['t'] ?? 0;
$appointments_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments"))['t'] ?? 0;
$records_count      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM medical_records"))['t'] ?? 0;
$ussd_count         = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM ussd_logs"))['t'] ?? 0;

// Today's counts
$today_patients     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM patients WHERE DATE(created_at)=CURDATE()"))['t'] ?? 0;
$today_appts        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments WHERE DATE(appointment_date)=CURDATE()"))['t'] ?? 0;
$today_ussd         = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM ussd_logs WHERE DATE(created_at)=CURDATE()"))['t'] ?? 0;
$pending_appts      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments WHERE status='Pending'"))['t'] ?? 0;

// USSD success rate
$ussd_end = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM ussd_logs WHERE response LIKE 'END %'"))['t'] ?? 0;
$ussd_rate = $ussd_count > 0 ? round($ussd_end / $ussd_count * 100) : 0;

// Recent patients (5)
$recent_patients = mysqli_query($conn, "SELECT * FROM patients ORDER BY id DESC LIMIT 5");

// Upcoming appointments (5)
$upcoming_appts = mysqli_query($conn, "
    SELECT appointments.*, patients.full_name, patients.patient_code
    FROM appointments INNER JOIN patients ON appointments.patient_id = patients.id
    WHERE status='Pending' AND appointment_date >= NOW()
    ORDER BY appointment_date ASC LIMIT 5
");

// Overdue pending
$overdue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments WHERE status='Pending' AND appointment_date < NOW()"))['t'] ?? 0;
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title">Dashboard</div>
                <div class="page-subtitle">
                    Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?> &mdash;
                    <?php echo date('l, d F Y'); ?>
                </div>
            </div>
            <div class="actions">
                <a href="../patients/add_patient.php" class="btn btn-primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Patient
                </a>
            </div>
        </div>

        <?php if($overdue > 0): ?>
        <div class="alert alert-danger" style="margin-bottom:20px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <strong><?php echo $overdue; ?> overdue appointment<?php echo $overdue>1?'s':''; ?></strong> &mdash;
            <a href="../appointments/view_appointments.php?status=Pending" style="color:inherit;text-decoration:underline;">Review now</a>
        </div>
        <?php endif; ?>

        <!-- Main stat cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon teal">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <div class="stat-label">Total Patients</div>
                    <div class="stat-value"><?php echo number_format($patients_count); ?></div>
                    <?php if($today_patients > 0): ?>
                    <div class="stat-delta stat-delta-up">+<?php echo $today_patients; ?> today</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <div class="stat-label">Appointments</div>
                    <div class="stat-value"><?php echo number_format($appointments_count); ?></div>
                    <?php if($today_appts > 0): ?>
                    <div class="stat-delta stat-delta-up"><?php echo $today_appts; ?> today</div>
                    <?php endif; ?>
                    <?php if($pending_appts > 0): ?>
                    <div class="stat-delta"><?php echo $pending_appts; ?> pending</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <div class="stat-label">Medical Records</div>
                    <div class="stat-value"><?php echo number_format($records_count); ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.06 6.06l1.77-1.77a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </div>
                <div>
                    <div class="stat-label">USSD Requests</div>
                    <div class="stat-value"><?php echo number_format($ussd_count); ?></div>
                    <?php if($today_ussd > 0): ?>
                    <div class="stat-delta stat-delta-up">+<?php echo $today_ussd; ?> today</div>
                    <?php endif; ?>
                    <div class="stat-delta"><?php echo $ussd_rate; ?>% success</div>
                </div>
            </div>
        </div>

        <!-- Quick actions + Upcoming + Recent -->
        <div class="dash-grid" style="margin-bottom:20px;">

            <!-- Quick actions -->
            <div class="card card-body">
                <div style="font-weight:700;font-size:.95rem;margin-bottom:14px;">Quick Actions</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <a href="../patients/add_patient.php" class="btn btn-outline" style="justify-content:flex-start;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                        Register New Patient
                    </a>
                    <a href="../appointments/add_appointment.php" class="btn btn-outline" style="justify-content:flex-start;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Schedule Appointment
                    </a>
                    <a href="../medical_records/add_record.php" class="btn btn-outline" style="justify-content:flex-start;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                        Add Medical Record
                    </a>
                    <a href="../appointments/calendar.php" class="btn btn-outline" style="justify-content:flex-start;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        View Calendar
                    </a>
                    <a href="../ussd/view_logs.php" class="btn btn-outline" style="justify-content:flex-start;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07"/><polyline points="2 2 6 6"/></svg>
                        USSD Logs
                    </a>
                </div>
            </div>

            <!-- System status + USSD success -->
            <div class="card card-body">
                <div style="font-weight:700;font-size:.95rem;margin-bottom:14px;">System Status</div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:.875rem;color:var(--text-muted);">USSD Service</span>
                        <span class="badge badge-success">Active</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:.875rem;color:var(--text-muted);">Database</span>
                        <span class="badge badge-success">Connected</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:.875rem;color:var(--text-muted);">Analytics</span>
                        <span class="badge badge-primary">Running</span>
                    </div>
                </div>
                <?php if($ussd_count > 0): ?>
                <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
                    <div style="font-size:.8rem;font-weight:700;color:var(--text-muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">USSD Success Rate</div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:.83rem;font-weight:600;"><?php echo $ussd_rate; ?>% completed sessions</span>
                        <span style="font-size:.83rem;color:var(--text-muted);"><?php echo $ussd_end; ?>/<?php echo $ussd_count; ?></span>
                    </div>
                    <div style="height:8px;background:var(--border);border-radius:10px;overflow:hidden;">
                        <div style="height:100%;width:<?php echo $ussd_rate; ?>%;background:var(--success);border-radius:10px;transition:width .4s;"></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming appointments + Recent patients -->
        <div class="dash-grid">

            <!-- Upcoming -->
            <div class="card card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                    <div style="font-weight:700;font-size:.95rem;">Upcoming Appointments</div>
                    <a href="../appointments/view_appointments.php" class="btn btn-outline btn-sm">View All</a>
                </div>
                <?php if(mysqli_num_rows($upcoming_appts) == 0): ?>
                <div class="empty-state" style="padding:20px 0;">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/></svg>
                    <p style="margin-top:8px;">No upcoming appointments</p>
                </div>
                <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:10px;">
                <?php while($a = mysqli_fetch_assoc($upcoming_appts)): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border:1px solid var(--border);border-radius:var(--radius-sm);">
                        <div>
                            <div style="font-size:.875rem;font-weight:600;"><?php echo htmlspecialchars($a['full_name']); ?></div>
                            <div style="font-size:.75rem;color:var(--text-muted);"><?php echo htmlspecialchars($a['appointment_date']); ?></div>
                        </div>
                        <a href="../patients/view_patient.php?id=<?php echo $a['patient_id']; ?>" class="btn btn-outline btn-sm">View</a>
                    </div>
                <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent patients -->
            <div class="card card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                    <div style="font-weight:700;font-size:.95rem;">Recently Registered</div>
                    <a href="../patients/view_patients.php" class="btn btn-outline btn-sm">View All</a>
                </div>
                <?php if(mysqli_num_rows($recent_patients) == 0): ?>
                <div class="empty-state" style="padding:20px 0;">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <p style="margin-top:8px;">No patients yet</p>
                </div>
                <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:10px;">
                <?php while($p = mysqli_fetch_assoc($recent_patients)): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border:1px solid var(--border);border-radius:var(--radius-sm);">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:34px;height:34px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:800;flex-shrink:0;">
                                <?php echo strtoupper(substr($p['full_name'],0,2)); ?>
                            </div>
                            <div>
                                <div style="font-size:.875rem;font-weight:600;"><?php echo htmlspecialchars($p['full_name']); ?></div>
                                <div style="font-size:.72rem;font-family:var(--font-mono);color:var(--text-muted);"><?php echo htmlspecialchars($p['patient_code']); ?></div>
                            </div>
                        </div>
                        <a href="../patients/view_patient.php?id=<?php echo $p['id']; ?>" class="btn btn-outline btn-sm">Profile</a>
                    </div>
                <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </main>
    
    <?php
        // ── Chart data queries ────────────────────────────────────

        // Last 6 months patient registrations
        $patient_trend = mysqli_query($conn,
            "SELECT DATE_FORMAT(created_at,'%b %Y') AS mo, COUNT(*) AS t
            FROM patients
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at,'%Y-%m')
            ORDER BY MIN(created_at) ASC");
        $pt_labels = []; $pt_data = [];
        while ($r = mysqli_fetch_assoc($patient_trend)) { $pt_labels[] = $r['mo']; $pt_data[] = intval($r['t']); }

        // Last 6 months appointment status breakdown
        $appt_trend = mysqli_query($conn,
            "SELECT DATE_FORMAT(appointment_date,'%b %Y') AS mo,
                    SUM(status='Completed') AS completed,
                    SUM(status='Missed') AS missed,
                    SUM(status='Pending') AS pending
            FROM appointments
            WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(appointment_date,'%Y-%m')
            ORDER BY MIN(appointment_date) ASC");
        $ap_labels = []; $ap_completed = []; $ap_missed = []; $ap_pending = [];
        while ($r = mysqli_fetch_assoc($appt_trend)) {
            $ap_labels[]    = $r['mo'];
            $ap_completed[] = intval($r['completed']);
            $ap_missed[]    = intval($r['missed']);
            $ap_pending[]   = intval($r['pending']);
        }

        // Today's hourly appointments (for today's activity spark)
        $today_appts = mysqli_query($conn,
            "SELECT HOUR(appointment_date) AS hr, COUNT(*) AS t
            FROM appointments WHERE DATE(appointment_date)=CURDATE()
            GROUP BY HOUR(appointment_date) ORDER BY hr ASC");
        $hr_labels = []; $hr_data = [];
        while ($r = mysqli_fetch_assoc($today_appts)) { $hr_labels[] = $r['hr'].':00'; $hr_data[] = intval($r['t']); }
        ?>

        <!-- Charts Section -->
        <div style="margin-top:8px;">
            <div style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:14px;">Analytics</div>

            <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;">

                <!-- Patient registration trend -->
                <div class="card card-body">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <div style="font-weight:700;font-size:.95rem;">Patient Registrations — Last 6 Months</div>
                        <a href="../reports/patient_statistics.php" class="btn btn-outline btn-sm">Full Stats</a>
                    </div>
                    <?php if($pt_labels): ?>
                    <canvas id="dash-pt-chart" height="120"></canvas>
                    <?php else: ?>
                    <div style="text-align:center;padding:30px 0;font-size:.85rem;color:var(--text-muted);">No registration data yet.</div>
                    <?php endif; ?>
                </div>

                <!-- Appointment pie (this month) -->
                <div class="card card-body">
                    <div style="font-weight:700;font-size:.95rem;margin-bottom:16px;">Appointments — This Month</div>
                    <?php
                    $am_c = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments WHERE status='Completed' AND MONTH(appointment_date)=MONTH(CURDATE()) AND YEAR(appointment_date)=YEAR(CURDATE())"))['t'] ?? 0;
                    $am_m = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments WHERE status='Missed' AND MONTH(appointment_date)=MONTH(CURDATE()) AND YEAR(appointment_date)=YEAR(CURDATE())"))['t'] ?? 0;
                    $am_p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments WHERE status='Pending' AND MONTH(appointment_date)=MONTH(CURDATE()) AND YEAR(appointment_date)=YEAR(CURDATE())"))['t'] ?? 0;
                    ?>
                    <canvas id="dash-appt-pie" height="130"></canvas>
                    <div style="display:flex;gap:12px;justify-content:center;margin-top:12px;font-size:.75rem;">
                        <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:50%;background:#2db57a;display:inline-block;"></span>Completed</span>
                        <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:50%;background:#e05353;display:inline-block;"></span>Missed</span>
                        <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:50%;background:#f4a235;display:inline-block;"></span>Pending</span>
                    </div>
                </div>
            </div>

            <!-- Appointment stacked bar (6 months) -->
            <?php if($ap_labels): ?>
            <div class="card card-body" style="margin-bottom:20px;">
                <div style="font-weight:700;font-size:.95rem;margin-bottom:16px;">Appointment Outcomes — Last 6 Months</div>
                <canvas id="dash-appt-bar" height="100"></canvas>
            </div>
            <?php endif; ?>
        </div>

        <script>
        (function(){
            var font = { family:"'Plus Jakarta Sans',sans-serif", size:11 };
            Chart.defaults.font = font;
            Chart.defaults.color = '#6b7a99';

            <?php if($pt_labels): ?>
            // Patient trend line
            new Chart(document.getElementById('dash-pt-chart'), {
                type:'line',
                data:{
                    labels:<?php echo json_encode($pt_labels); ?>,
                    datasets:[{label:'New patients',data:<?php echo json_encode($pt_data); ?>,
                        borderColor:'#0d6e6e',backgroundColor:'rgba(13,110,110,.08)',
                        tension:.35,fill:true,pointBackgroundColor:'#0d6e6e',pointRadius:4}]
                },
                options:{plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}},responsive:true}
            });
            <?php endif; ?>

            // Appointment doughnut (this month)
            new Chart(document.getElementById('dash-appt-pie'), {
                type:'doughnut',
                data:{
                    labels:['Completed','Missed','Pending'],
                    datasets:[{data:[<?php echo $am_c; ?>,<?php echo $am_m; ?>,<?php echo $am_p; ?>],
                        backgroundColor:['#2db57a','#e05353','#f4a235'],borderWidth:0,hoverOffset:4}]
                },
                options:{plugins:{legend:{display:false}},cutout:'65%',responsive:true}
            });

            <?php if($ap_labels): ?>
            // Appointment stacked bar
            new Chart(document.getElementById('dash-appt-bar'), {
                type:'bar',
                data:{
                    labels:<?php echo json_encode($ap_labels); ?>,
                    datasets:[
                        {label:'Completed',data:<?php echo json_encode($ap_completed); ?>,backgroundColor:'#2db57a',borderRadius:3,stack:'s'},
                        {label:'Missed',   data:<?php echo json_encode($ap_missed); ?>,   backgroundColor:'#e05353',stack:'s'},
                        {label:'Pending',  data:<?php echo json_encode($ap_pending); ?>,  backgroundColor:'#f4a235',stack:'s'}
                    ]
                },
                options:{plugins:{legend:{position:'bottom',labels:{padding:14,font}}},scales:{x:{stacked:true},y:{stacked:true,beginAtZero:true,ticks:{stepSize:1}}},responsive:true}
            });
            <?php endif; ?>
        })();
        </script>

</div>
<?php include("../includes/footer.php"); ?>
