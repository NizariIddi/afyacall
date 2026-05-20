<?php
/**
 * export_appointments.php — Phase 5
 */
include("../config/session.php");
include("../config/database.php");

$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to']   ?? '';
$status    = $_GET['status']    ?? '';

$where = [];
if ($date_from) { $df = mysqli_real_escape_string($conn, $date_from); $where[] = "DATE(a.appointment_date) >= '$df'"; }
if ($date_to)   { $dt = mysqli_real_escape_string($conn, $date_to);   $where[] = "DATE(a.appointment_date) <= '$dt'"; }
if ($status)    { $st = mysqli_real_escape_string($conn, $status);    $where[] = "a.status = '$st'"; }
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$result = mysqli_query($conn, "
    SELECT
        p.patient_code       AS 'Patient Code',
        p.full_name          AS 'Patient Name',
        p.phone              AS 'Phone',
        a.appointment_date   AS 'Appointment Date',
        a.reason             AS 'Reason',
        a.status             AS 'Status',
        DATE(a.created_at)   AS 'Booked On'
    FROM appointments a
    INNER JOIN patients p ON a.patient_id = p.id
    $where_sql
    ORDER BY a.appointment_date DESC
");

$filename = 'afyacall_appointments_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF";
$out = fopen('php://output', 'w');
if ($result && mysqli_num_rows($result) > 0) {
    $first = mysqli_fetch_assoc($result);
    fputcsv($out, array_keys($first));
    fputcsv($out, array_values($first));
    while ($row = mysqli_fetch_assoc($result)) fputcsv($out, array_values($row));
} else {
    fputcsv($out, ['No appointments found for the selected filters.']);
}
fclose($out);
exit();
