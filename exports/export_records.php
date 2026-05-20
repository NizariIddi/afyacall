<?php
/**
 * export_records.php — Phase 5
 * Exports medical records to CSV (opens in Excel).
 */
include("../config/session.php");
include("../config/database.php");

$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to']   ?? '';
$doctor_id = intval($_GET['doctor'] ?? 0);

$where = [];
if ($date_from) {
    $df = mysqli_real_escape_string($conn, $date_from);
    $where[] = "mr.visit_date >= '$df'";
}
if ($date_to) {
    $dt = mysqli_real_escape_string($conn, $date_to);
    $where[] = "mr.visit_date <= '$dt'";
}
if ($doctor_id) {
    $where[] = "mr.recorded_by = '$doctor_id'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$result = mysqli_query($conn, "
    SELECT
        p.patient_code          AS 'Patient Code',
        p.full_name             AS 'Patient Name',
        p.gender                AS 'Gender',
        p.phone                 AS 'Phone',
        mr.visit_date           AS 'Visit Date',
        mr.diagnosis            AS 'Diagnosis',
        mr.medication           AS 'Medication',
        mr.allergies            AS 'Allergies',
        mr.notes                AS 'Notes',
        mr.next_appointment     AS 'Next Appointment',
        a.full_name             AS 'Recorded By'
    FROM medical_records mr
    INNER JOIN patients p ON mr.patient_id = p.id
    INNER JOIN admins a ON mr.recorded_by = a.id
    $where_sql
    ORDER BY mr.visit_date DESC
");

$filename = 'afyacall_records_' . date('Y-m-d_His') . '.csv';

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
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($out, array_values($row));
    }
} else {
    fputcsv($out, ['No records found for the selected filters.']);
}
fclose($out);
exit();
