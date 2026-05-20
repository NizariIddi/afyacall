<?php
/**
 * export_patients.php — Phase 5
 * Exports patients table to CSV (opens in Excel).
 * Supports date range and gender filters.
 * Access: reports/export_patients.php
 */
include("../config/session.php");
include("../config/database.php");

// ── Filters ────────────────────────────────────────────────
$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to']   ?? '';
$gender    = $_GET['gender']    ?? '';
$blood     = $_GET['blood']     ?? '';

$where = [];
if ($date_from) {
    $df = mysqli_real_escape_string($conn, $date_from);
    $where[] = "DATE(created_at) >= '$df'";
}
if ($date_to) {
    $dt = mysqli_real_escape_string($conn, $date_to);
    $where[] = "DATE(created_at) <= '$dt'";
}
if ($gender) {
    $g = mysqli_real_escape_string($conn, $gender);
    $where[] = "gender = '$g'";
}
if ($blood) {
    $b = mysqli_real_escape_string($conn, $blood);
    $where[] = "blood_group = '$b'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$result = mysqli_query($conn, "
    SELECT
        patient_code         AS 'Patient Code',
        full_name            AS 'Full Name',
        gender               AS 'Gender',
        date_of_birth        AS 'Date of Birth',
        TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) AS 'Age',
        phone                AS 'Phone',
        email                AS 'Email',
        address              AS 'Address',
        blood_group          AS 'Blood Group',
        emergency_contact    AS 'Emergency Contact',
        DATE(created_at)     AS 'Registered On'
    FROM patients
    $where_sql
    ORDER BY id DESC
");

// ── Stream CSV ─────────────────────────────────────────────
$filename = 'afyacall_patients_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// UTF-8 BOM so Excel opens correctly
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

// Header row
if ($result && mysqli_num_rows($result) > 0) {
    $first = mysqli_fetch_assoc($result);
    fputcsv($out, array_keys($first));
    fputcsv($out, array_values($first));
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($out, array_values($row));
    }
} else {
    fputcsv($out, ['No data found for the selected filters.']);
}

fclose($out);
exit();
