<?php
include("../config/session.php");
include("../config/database.php");

$month = intval($_GET['month'] ?? date('n'));
$year  = intval($_GET['year']  ?? date('Y'));

// Clamp
if ($month < 1)  { $month = 12; $year--; }
if ($month > 12) { $month = 1;  $year++; }

$prev_month = $month - 1; $prev_year = $year;
if ($prev_month < 1) { $prev_month = 12; $prev_year--; }
$next_month = $month + 1; $next_year = $year;
if ($next_month > 12) { $next_month = 1; $next_year++; }

$first_day = mktime(0,0,0,$month,1,$year);
$days_in_month = date('t', $first_day);
$start_dow     = date('N', $first_day); // 1=Mon … 7=Sun

// Pull all appointments for this month
$month_str = str_pad($month,2,'0',STR_PAD_LEFT);
$appts_raw = mysqli_query($conn, "
    SELECT appointments.*, patients.full_name, patients.patient_code
    FROM appointments
    INNER JOIN patients ON appointments.patient_id = patients.id
    WHERE DATE_FORMAT(appointment_date,'%Y-%m') = '$year-$month_str'
    ORDER BY appointment_date ASC
");

$appts_by_day = [];
while ($a = mysqli_fetch_assoc($appts_raw)) {
    $day = intval(date('j', strtotime($a['appointment_date'])));
    $appts_by_day[$day][] = $a;
}

$month_name   = date('F', $first_day);
$today_day    = (intval(date('Y')) === $year && intval(date('n')) === $month) ? intval(date('j')) : -1;
$days_of_week = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title">Appointment Calendar</div>
                <div class="page-subtitle"><?php echo "$month_name $year"; ?></div>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="btn btn-outline">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    Prev
                </a>
                <a href="?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>" class="btn btn-outline">Today</a>
                <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="btn btn-outline">
                    Next
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="add_appointment.php" class="btn btn-primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add
                </a>
            </div>
        </div>

        <!-- Month summary pills -->
        <?php
        $total_month = array_sum(array_map('count', $appts_by_day));
        $pend_month  = 0; $done_month = 0; $miss_month = 0;
        foreach($appts_by_day as $day_appts) {
            foreach($day_appts as $aa) {
                if($aa['status']==='Pending')   $pend_month++;
                if($aa['status']==='Completed') $done_month++;
                if($aa['status']==='Missed')    $miss_month++;
            }
        }
        ?>
        <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
            <div class="status-pill active">Total <span><?php echo $total_month; ?></span></div>
            <div class="status-pill warning">Pending <span><?php echo $pend_month; ?></span></div>
            <div class="status-pill success">Completed <span><?php echo $done_month; ?></span></div>
            <div class="status-pill danger">Missed <span><?php echo $miss_month; ?></span></div>
        </div>

        <!-- Calendar grid -->
        <div class="cal-wrap">
            <!-- Day headers -->
            <div class="cal-grid cal-header">
                <?php foreach($days_of_week as $d): ?>
                <div class="cal-dow"><?php echo $d; ?></div>
                <?php endforeach; ?>
            </div>

            <div class="cal-grid cal-body">
                <?php
                // Empty cells before first day (Mon=1)
                for($e = 1; $e < $start_dow; $e++):
                ?>
                <div class="cal-cell cal-empty"></div>
                <?php endfor; ?>

                <?php for($d = 1; $d <= $days_in_month; $d++):
                    $is_today  = ($d === $today_day);
                    $day_appts = $appts_by_day[$d] ?? [];
                    $has_appts = count($day_appts) > 0;
                ?>
                <div class="cal-cell <?php echo $is_today ? 'cal-today' : ''; ?> <?php echo $has_appts ? 'cal-has-events' : ''; ?>">
                    <div class="cal-day-num"><?php echo $d; ?></div>
                    <?php foreach(array_slice($day_appts, 0, 3) as $a):
                        $ec = $a['status']==='Completed' ? 'ev-done' : ($a['status']==='Missed' ? 'ev-miss' : 'ev-pend');
                    ?>
                    <div class="cal-event <?php echo $ec; ?>" title="<?php echo htmlspecialchars($a['full_name'].' — '.$a['appointment_date']); ?>">
                        <?php echo htmlspecialchars(substr($a['full_name'],0,16)); ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if(count($day_appts) > 3): ?>
                    <div class="cal-more">+<?php echo count($day_appts)-3; ?> more</div>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>

                <?php
                // Trailing cells to complete last row
                $used = ($start_dow - 1) + $days_in_month;
                $trail = (7 - ($used % 7)) % 7;
                for($t=0;$t<$trail;$t++): ?>
                <div class="cal-cell cal-empty"></div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Upcoming appointments list below calendar -->
        <?php
        $upcoming = mysqli_query($conn, "
            SELECT appointments.*, patients.full_name, patients.patient_code
            FROM appointments
            INNER JOIN patients ON appointments.patient_id = patients.id
            WHERE appointment_date >= NOW() AND status='Pending'
            ORDER BY appointment_date ASC LIMIT 8
        ");
        if(mysqli_num_rows($upcoming) > 0):
        ?>
        <div style="margin-top:24px;">
            <div style="font-weight:700;font-size:.95rem;margin-bottom:14px;">Upcoming Appointments</div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Patient</th><th>Date &amp; Time</th><th>Status</th><th>Remarks</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php while($a = mysqli_fetch_assoc($upcoming)): ?>
                        <tr>
                            <td>
                                <div class="patient-name"><?php echo htmlspecialchars($a['full_name']); ?></div>
                                <div class="patient-code"><?php echo htmlspecialchars($a['patient_code']); ?></div>
                            </td>
                            <td style="font-size:.875rem;"><?php echo htmlspecialchars($a['appointment_date']); ?></td>
                            <td><span class="badge badge-warning">Pending</span></td>
                            <td style="font-size:.83rem;color:var(--text-muted);"><?php echo $a['remarks'] ? htmlspecialchars($a['remarks']) : '—'; ?></td>
                            <td>
                                <div style="display:flex;gap:6px;">
                                    <a href="view_appointments.php?complete=<?php echo $a['id']; ?>" class="btn btn-success btn-sm">✓ Done</a>
                                    <a href="view_appointments.php?missed=<?php echo $a['id']; ?>"  class="btn btn-danger btn-sm">✗ Miss</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </main>
</div>
<?php include("../includes/footer.php"); ?>
