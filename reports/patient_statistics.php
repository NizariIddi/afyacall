<?php
/**
 * patient_statistics.php — Phase 5
 * Visual patient stats: gender, age groups, blood group, registration trend.
 */
include("../config/session.php");
include("../config/database.php");

// ── Gender breakdown ──────────────────────────────────────
$gender_rows = mysqli_query($conn, "SELECT gender, COUNT(*) AS t FROM patients GROUP BY gender ORDER BY t DESC");
$genders = []; $gender_labels = []; $gender_counts = [];
while ($g = mysqli_fetch_assoc($gender_rows)) {
    $label = $g['gender'] ?: 'Unknown';
    $gender_labels[] = $label;
    $gender_counts[] = intval($g['t']);
}

// ── Age groups ────────────────────────────────────────────
$age_groups = ['0–12' => 0, '13–17' => 0, '18–35' => 0, '36–60' => 0, '60+' => 0, 'Unknown' => 0];
$ages = mysqli_query($conn, "SELECT date_of_birth FROM patients");
while ($a = mysqli_fetch_assoc($ages)) {
    if (!$a['date_of_birth']) { $age_groups['Unknown']++; continue; }
    $age = (new DateTime($a['date_of_birth']))->diff(new DateTime())->y;
    if      ($age <= 12) $age_groups['0–12']++;
    elseif  ($age <= 17) $age_groups['13–17']++;
    elseif  ($age <= 35) $age_groups['18–35']++;
    elseif  ($age <= 60) $age_groups['36–60']++;
    else                 $age_groups['60+']++;
}

// ── Blood group breakdown ─────────────────────────────────
$blood_rows = mysqli_query($conn, "SELECT blood_group, COUNT(*) AS t FROM patients WHERE blood_group != '' GROUP BY blood_group ORDER BY t DESC");
$blood_labels = []; $blood_counts = [];
while ($b = mysqli_fetch_assoc($blood_rows)) {
    $blood_labels[] = $b['blood_group'];
    $blood_counts[] = intval($b['t']);
}

// ── Monthly registration trend (last 12 months) ───────────
$trend_rows = mysqli_query($conn,
    "SELECT DATE_FORMAT(created_at,'%Y-%m') AS ym, COUNT(*) AS t
     FROM patients
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
     GROUP BY ym ORDER BY ym ASC");
$trend_labels = []; $trend_counts = [];
while ($t = mysqli_fetch_assoc($trend_rows)) {
    $trend_labels[] = date('M Y', strtotime($t['ym'] . '-01'));
    $trend_counts[] = intval($t['t']);
}

// ── Summary numbers ───────────────────────────────────────
$total      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM patients"))['t'] ?? 0;
$this_month = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM patients WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())"))['t'] ?? 0;
$this_year  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM patients WHERE YEAR(created_at)=YEAR(CURDATE())"))['t'] ?? 0;
$avg_age    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(AVG(TIMESTAMPDIFF(YEAR,date_of_birth,CURDATE()))) AS a FROM patients WHERE date_of_birth IS NOT NULL"))['a'] ?? '—';
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
<?php include("../includes/sidebar.php"); ?>
<main class="main-content">

<div class="page-header">
    <div>
        <div class="page-title">Patient Statistics</div>
        <div class="page-subtitle">Demographics and registration insights</div>
    </div>
    <a href="patient_statistics.php?print=1" target="_blank" class="btn btn-outline">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Print / Export PDF
    </a>
</div>

<!-- Summary stat cards -->
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-icon teal"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <div><div class="stat-label">Total Patients</div><div class="stat-value"><?php echo number_format($total); ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
        <div><div class="stat-label">This Month</div><div class="stat-value"><?php echo number_format($this_month); ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
        <div><div class="stat-label">This Year</div><div class="stat-value"><?php echo number_format($this_year); ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div><div class="stat-label">Average Age</div><div class="stat-value"><?php echo $avg_age; ?> <span style="font-size:.9rem;font-weight:500;">yrs</span></div></div>
    </div>
</div>

<!-- Charts row 1: gender + age -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
    <div class="card card-body">
        <div style="font-weight:700;font-size:.95rem;margin-bottom:16px;">Gender Distribution</div>
        <div style="max-width:280px;margin:0 auto;">
            <canvas id="gender-chart"></canvas>
        </div>
    </div>
    <div class="card card-body">
        <div style="font-weight:700;font-size:.95rem;margin-bottom:16px;">Age Groups</div>
        <canvas id="age-chart"></canvas>
    </div>
</div>

<!-- Charts row 2: blood group + registration trend -->
<div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;margin-bottom:20px;">
    <div class="card card-body">
        <div style="font-weight:700;font-size:.95rem;margin-bottom:16px;">Blood Groups</div>
        <?php if($blood_labels): ?>
        <canvas id="blood-chart"></canvas>
        <?php else: ?>
        <p style="font-size:.85rem;color:var(--text-muted);">No blood group data recorded yet.</p>
        <?php endif; ?>
    </div>
    <div class="card card-body">
        <div style="font-weight:700;font-size:.95rem;margin-bottom:16px;">Registration Trend — Last 12 Months</div>
        <?php if($trend_labels): ?>
        <canvas id="trend-chart"></canvas>
        <?php else: ?>
        <p style="font-size:.85rem;color:var(--text-muted);">No registration data yet.</p>
        <?php endif; ?>
    </div>
</div>

</main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
var font = { family: "'Plus Jakarta Sans', sans-serif", size: 11 };
Chart.defaults.font = font;
Chart.defaults.color = '#6b7a99';

// Gender doughnut
new Chart(document.getElementById('gender-chart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($gender_labels); ?>,
        datasets: [{ data: <?php echo json_encode($gender_counts); ?>, backgroundColor: ['#0d6e6e','#f4a235','#e05353','#3b5bdb'], borderWidth: 0, hoverOffset: 4 }]
    },
    options: { plugins: { legend: { position: 'bottom', labels: { padding: 14, font } } }, cutout: '60%' }
});

// Age groups bar
new Chart(document.getElementById('age-chart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($age_groups)); ?>,
        datasets: [{ label: 'Patients', data: <?php echo json_encode(array_values($age_groups)); ?>, backgroundColor: ['#0d6e6e','#0f8080','#1a9999','#2db57a','#f4a235','#ccc'], borderRadius: 5, borderSkipped: false }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});

// Blood group bar
<?php if($blood_labels): ?>
new Chart(document.getElementById('blood-chart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($blood_labels); ?>,
        datasets: [{ label: 'Patients', data: <?php echo json_encode($blood_counts); ?>, backgroundColor: '#e05353', borderRadius: 5, borderSkipped: false }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
<?php endif; ?>

// Registration trend line
<?php if($trend_labels): ?>
new Chart(document.getElementById('trend-chart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($trend_labels); ?>,
        datasets: [{ label: 'New patients', data: <?php echo json_encode($trend_counts); ?>, borderColor: '#0d6e6e', backgroundColor: 'rgba(13,110,110,.08)', tension: 0.35, fill: true, pointBackgroundColor: '#0d6e6e', pointRadius: 4 }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
<?php endif; ?>
</script>
<?php include("../includes/footer.php"); ?>
