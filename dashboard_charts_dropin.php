<!-- ============================================================
     PHASE 6 — Dashboard Charts Drop-in
     Add this block BEFORE the closing </main> tag in dashboard/index.php
     Also add to header.php <head>:
       <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
     ============================================================ -->
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
