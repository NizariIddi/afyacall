<?php
/**
 * monthly_report.php — Phase 5
 * Summary report: patients seen, appointments, records per month.
 * Printable HTML — no extra library needed.
 */
include("../config/session.php");
include("../config/database.php");

// ── Filters ────────────────────────────────────────────────
$year  = intval($_GET['year']  ?? date('Y'));
$month = intval($_GET['month'] ?? date('n'));
if ($month < 1 || $month > 12) $month = date('n');

$month_start = sprintf('%04d-%02d-01', $year, $month);
$month_end   = date('Y-m-t', strtotime($month_start));
$month_label = date('F Y', strtotime($month_start));

// ── Core counts ────────────────────────────────────────────
$new_patients = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS t FROM patients WHERE DATE(created_at) BETWEEN '$month_start' AND '$month_end'"))['t'] ?? 0;

$total_patients = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS t FROM patients WHERE DATE(created_at) <= '$month_end'"))['t'] ?? 0;

$appt_total = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS t FROM appointments WHERE DATE(appointment_date) BETWEEN '$month_start' AND '$month_end'"))['t'] ?? 0;

$appt_completed = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS t FROM appointments WHERE status='Completed' AND DATE(appointment_date) BETWEEN '$month_start' AND '$month_end'"))['t'] ?? 0;

$appt_missed = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS t FROM appointments WHERE status='Missed' AND DATE(appointment_date) BETWEEN '$month_start' AND '$month_end'"))['t'] ?? 0;

$appt_pending = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS t FROM appointments WHERE status='Pending' AND DATE(appointment_date) BETWEEN '$month_start' AND '$month_end'"))['t'] ?? 0;

$records_total = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS t FROM medical_records WHERE visit_date BETWEEN '$month_start' AND '$month_end'"))['t'] ?? 0;

$ussd_total = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS t FROM ussd_logs WHERE DATE(created_at) BETWEEN '$month_start' AND '$month_end'"))['t'] ?? 0;

// ── Gender breakdown (new patients this month) ─────────────
$gender_rows = mysqli_query($conn,
    "SELECT gender, COUNT(*) AS t FROM patients WHERE DATE(created_at) BETWEEN '$month_start' AND '$month_end' GROUP BY gender");
$gender_data = [];
while ($g = mysqli_fetch_assoc($gender_rows)) {
    $gender_data[$g['gender'] ?: 'Unknown'] = intval($g['t']);
}

// ── Appointment completion rate ────────────────────────────
$appt_rate = $appt_total > 0 ? round($appt_completed / $appt_total * 100) : 0;

// ── Daily patient registrations this month ─────────────────
$daily_rows = mysqli_query($conn,
    "SELECT DAY(created_at) AS d, COUNT(*) AS t FROM patients
     WHERE DATE(created_at) BETWEEN '$month_start' AND '$month_end'
     GROUP BY DAY(created_at) ORDER BY d ASC");
$daily = [];
while ($dr = mysqli_fetch_assoc($daily_rows)) $daily[intval($dr['d'])] = intval($dr['t']);

// ── Top 5 diagnoses this month ─────────────────────────────
$diag_rows = mysqli_query($conn,
    "SELECT diagnosis, COUNT(*) AS t FROM medical_records
     WHERE visit_date BETWEEN '$month_start' AND '$month_end'
     AND diagnosis != ''
     GROUP BY diagnosis ORDER BY t DESC LIMIT 5");
$diagnoses = [];
while ($d = mysqli_fetch_assoc($diag_rows)) $diagnoses[] = $d;

// ── Active doctors this month ──────────────────────────────
$doc_rows = mysqli_query($conn,
    "SELECT admins.full_name, COUNT(*) AS t
     FROM medical_records
     INNER JOIN admins ON medical_records.recorded_by = admins.id
     WHERE visit_date BETWEEN '$month_start' AND '$month_end'
     GROUP BY admins.id ORDER BY t DESC LIMIT 5");
$doctors = [];
while ($d = mysqli_fetch_assoc($doc_rows)) $doctors[] = $d;

// ── Year options for filter ────────────────────────────────
$year_min = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT MIN(YEAR(created_at)) AS y FROM patients"))['y'] ?? date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report — <?php echo $month_label; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <style>
        :root {
            --primary:      #0d6e6e;
            --primary-dark: #094e4e;
            --accent:       #f4a235;
            --success:      #2db57a;
            --danger:       #e05353;
            --warning:      #e6a817;
            --text-main:    #1a2333;
            --text-muted:   #6b7a99;
            --border:       #e2e8f0;
            --font:         'Plus Jakarta Sans', sans-serif;
            --font-mono:    'DM Mono', monospace;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font); background: #f0f4f8; color: var(--text-main); padding: 28px 20px; }

        /* ── Screen toolbar ── */
        .toolbar { display:flex; gap:10px; align-items:center; max-width:1000px; margin:0 auto 24px; flex-wrap:wrap; }
        .btn { display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-family:var(--font);font-size:.85rem;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all .18s; }
        .btn-primary { background:var(--primary);color:#fff; }
        .btn-primary:hover { background:var(--primary-dark); }
        .btn-outline { background:transparent;border:1.5px solid var(--border);color:var(--text-main); }
        .btn-outline:hover { border-color:var(--primary);color:var(--primary); }
        select.form-control, input.form-control { padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-family:var(--font);font-size:.85rem;color:var(--text-main);background:#fff; }

        /* ── Report doc ── */
        .report { max-width:1000px; margin:0 auto; }

        /* Header stripe */
        .report-header { background:var(--primary-dark);border-radius:12px 12px 0 0;padding:28px 36px;color:#fff;display:flex;justify-content:space-between;align-items:flex-start; }
        .report-title { font-size:1.4rem;font-weight:800;letter-spacing:-.3px;margin-bottom:4px; }
        .report-sub { font-size:.78rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.8px; }
        .report-meta { text-align:right;font-size:.78rem;color:rgba(255,255,255,.45); }
        .report-meta strong { display:block;font-size:1rem;font-weight:700;color:#fff;margin-bottom:2px; }

        /* Body */
        .report-body { background:#fff;border:1px solid var(--border);border-top:none;padding:32px 36px; }

        /* Section */
        .section { margin-bottom:32px; }
        .section-title { font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.9px;color:var(--text-muted);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border); }

        /* Stat grid */
        .stat-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:0; }
        .stat-box { border:1px solid var(--border);border-radius:10px;padding:16px;text-align:center; }
        .stat-box-num { font-size:1.8rem;font-weight:800;line-height:1;margin-bottom:4px; }
        .stat-box-lbl { font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted); }
        .stat-box-sub { font-size:.72rem;color:var(--text-muted);margin-top:4px; }
        .color-teal   { color:var(--primary); }
        .color-green  { color:var(--success); }
        .color-amber  { color:var(--accent); }
        .color-red    { color:var(--danger); }
        .color-blue   { color:#3b5bdb; }

        /* Two column layout */
        .two-col { display:grid;grid-template-columns:1fr 1fr;gap:24px; }

        /* Table */
        .mini-table { width:100%;border-collapse:collapse;font-size:.85rem; }
        .mini-table th { font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);padding:6px 0;border-bottom:1px solid var(--border);text-align:left; }
        .mini-table td { padding:9px 0;border-bottom:1px solid #f3f6f9;vertical-align:middle; }
        .mini-table tr:last-child td { border-bottom:none; }

        /* Progress bar */
        .bar-wrap { height:6px;background:var(--border);border-radius:10px;overflow:hidden;margin-top:4px; }
        .bar-fill { height:100%;border-radius:10px;background:var(--primary); }

        /* Completion ring placeholder */
        .ring-wrap { display:flex;flex-direction:column;align-items:center;justify-content:center; }

        /* Footer */
        .report-footer { background:#f8fafc;border:1px solid var(--border);border-top:none;border-radius:0 0 12px 12px;padding:14px 36px;display:flex;justify-content:space-between;font-size:.72rem;color:var(--text-muted); }

        /* ── Print ── */
        @media print {
            body { background:#fff;padding:0; }
            .toolbar { display:none; }
            .report-header { border-radius:0; }
            .report-footer { border-radius:0; }
        }
    </style>
</head>
<body>

<!-- Toolbar -->
<div class="toolbar">
    <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <select name="month" class="form-control">
            <?php for($m=1;$m<=12;$m++): ?>
            <option value="<?php echo $m; ?>" <?php if($m==$month) echo 'selected'; ?>>
                <?php echo date('F', mktime(0,0,0,$m,1)); ?>
            </option>
            <?php endfor; ?>
        </select>
        <select name="year" class="form-control">
            <?php for($y=$year_min;$y<=date('Y');$y++): ?>
            <option value="<?php echo $y; ?>" <?php if($y==$year) echo 'selected'; ?>><?php echo $y; ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" class="btn btn-primary">Load Report</button>
    </form>
    <button class="btn btn-outline" onclick="window.print()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Print Report
    </button>
    <a href="../dashboard/index.php" class="btn btn-outline">← Dashboard</a>
</div>

<!-- Report -->
<div class="report">

    <!-- Header -->
    <div class="report-header">
        <div>
            <div class="report-title">AfyaCall — Monthly Report</div>
            <div class="report-sub">Hospital Management Summary</div>
        </div>
        <div class="report-meta">
            <strong><?php echo $month_label; ?></strong>
            <?php echo $month_start; ?> to <?php echo $month_end; ?><br>
            Generated: <?php echo date('d M Y, H:i'); ?>
        </div>
    </div>

    <div class="report-body">

        <!-- 1. Patient Summary -->
        <div class="section">
            <div class="section-title">Patient Summary</div>
            <div class="stat-grid">
                <div class="stat-box">
                    <div class="stat-box-num color-teal"><?php echo number_format($new_patients); ?></div>
                    <div class="stat-box-lbl">New Patients</div>
                    <div class="stat-box-sub">Registered this month</div>
                </div>
                <div class="stat-box">
                    <div class="stat-box-num color-blue"><?php echo number_format($total_patients); ?></div>
                    <div class="stat-box-lbl">Total Patients</div>
                    <div class="stat-box-sub">Cumulative to <?php echo $month_end; ?></div>
                </div>
                <?php foreach($gender_data as $gname => $gcount): ?>
                <div class="stat-box">
                    <div class="stat-box-num color-amber"><?php echo number_format($gcount); ?></div>
                    <div class="stat-box-lbl"><?php echo htmlspecialchars($gname); ?></div>
                    <div class="stat-box-sub">New this month</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 2. Appointments -->
        <div class="section">
            <div class="section-title">Appointments</div>
            <div class="two-col">
                <div>
                    <div class="stat-grid" style="grid-template-columns:1fr 1fr 1fr;margin-bottom:16px;">
                        <div class="stat-box">
                            <div class="stat-box-num"><?php echo number_format($appt_total); ?></div>
                            <div class="stat-box-lbl">Total</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-box-num color-green"><?php echo number_format($appt_completed); ?></div>
                            <div class="stat-box-lbl">Completed</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-box-num color-red"><?php echo number_format($appt_missed); ?></div>
                            <div class="stat-box-lbl">Missed</div>
                        </div>
                    </div>
                    <div style="font-size:.82rem;color:var(--text-muted);margin-bottom:6px;">Completion rate</div>
                    <div style="font-size:1.4rem;font-weight:800;color:var(--success);margin-bottom:6px;"><?php echo $appt_rate; ?>%</div>
                    <div class="bar-wrap"><div class="bar-fill" style="width:<?php echo $appt_rate; ?>%;background:var(--success);"></div></div>
                </div>
                <div>
                    <canvas id="appt-chart" height="160"></canvas>
                </div>
            </div>
        </div>

        <!-- 3. Medical Records & USSD -->
        <div class="section">
            <div class="section-title">Clinical Activity</div>
            <div class="stat-grid">
                <div class="stat-box">
                    <div class="stat-box-num color-teal"><?php echo number_format($records_total); ?></div>
                    <div class="stat-box-lbl">Medical Records</div>
                    <div class="stat-box-sub">Created this month</div>
                </div>
                <div class="stat-box">
                    <div class="stat-box-num color-blue"><?php echo number_format($ussd_total); ?></div>
                    <div class="stat-box-lbl">USSD Sessions</div>
                    <div class="stat-box-sub">This month</div>
                </div>
            </div>
        </div>

        <!-- 4. Top diagnoses + Active doctors -->
        <div class="section">
            <div class="two-col">
                <div>
                    <div class="section-title">Top Diagnoses</div>
                    <?php if($diagnoses): ?>
                    <?php $max_d = max(array_column($diagnoses,'t')); ?>
                    <table class="mini-table">
                        <thead><tr><th>Diagnosis</th><th style="text-align:right;">Count</th></tr></thead>
                        <tbody>
                        <?php foreach($diagnoses as $d): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($d['diagnosis']); ?>
                                <div class="bar-wrap"><div class="bar-fill" style="width:<?php echo round($d['t']/$max_d*100); ?>%;"></div></div>
                            </td>
                            <td style="text-align:right;font-weight:700;font-family:var(--font-mono);"><?php echo $d['t']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?><p style="font-size:.85rem;color:var(--text-muted);">No records this month.</p><?php endif; ?>
                </div>
                <div>
                    <div class="section-title">Most Active Doctors</div>
                    <?php if($doctors): ?>
                    <?php $max_doc = max(array_column($doctors,'t')); ?>
                    <table class="mini-table">
                        <thead><tr><th>Doctor</th><th style="text-align:right;">Records</th></tr></thead>
                        <tbody>
                        <?php foreach($doctors as $d): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($d['full_name']); ?>
                                <div class="bar-wrap"><div class="bar-fill" style="width:<?php echo round($d['t']/$max_doc*100); ?>%;background:var(--accent);"></div></div>
                            </td>
                            <td style="text-align:right;font-weight:700;font-family:var(--font-mono);"><?php echo $d['t']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?><p style="font-size:.85rem;color:var(--text-muted);">No records this month.</p><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 5. Daily registrations chart -->
        <?php if($daily): ?>
        <div class="section">
            <div class="section-title">Daily Patient Registrations — <?php echo $month_label; ?></div>
            <canvas id="daily-chart" height="100"></canvas>
        </div>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <div class="report-footer">
        <span>AfyaCall Hospital Management System</span>
        <span>Report period: <?php echo $month_start; ?> – <?php echo $month_end; ?></span>
        <span>Generated: <?php echo date('d M Y H:i'); ?></span>
    </div>
</div>

<script>
// Appointments doughnut
(function(){
    var ctx = document.getElementById('appt-chart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Completed','Missed','Pending'],
            datasets:[{ data:[<?php echo $appt_completed; ?>,<?php echo $appt_missed; ?>,<?php echo $appt_pending; ?>], backgroundColor:['#2db57a','#e05353','#f4a235'], borderWidth:0, hoverOffset:4 }]
        },
        options: { plugins:{ legend:{ position:'bottom', labels:{ font:{ family:"'Plus Jakarta Sans',sans-serif", size:11 }, padding:12 }}}, cutout:'65%', responsive:true }
    });
})();

// Daily bar chart
(function(){
    var ctx = document.getElementById('daily-chart');
    if (!ctx) return;
    var days   = <?php echo json_encode(array_keys($daily)); ?>;
    var counts = <?php echo json_encode(array_values($daily)); ?>;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: days.map(d => 'Day ' + d),
            datasets:[{ label:'New patients', data:counts, backgroundColor:'#0d6e6e', borderRadius:4, borderSkipped:false }]
        },
        options: { plugins:{ legend:{ display:false }}, scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1, font:{ size:11 } }}, x:{ ticks:{ font:{ size:10 } }}}, responsive:true }
    });
})();
</script>

</body>
</html>
