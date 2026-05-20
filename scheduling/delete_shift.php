<?php
/**
 * delete_shift.php — Phase 4
 */
include("../config/session.php");
include("../config/database.php");
include("../config/activity_log.php");

require_role(['admin']);

$id = intval($_GET['id'] ?? 0);
$shift = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM doctor_shifts WHERE id='$id'"));
if ($shift) {
    mysqli_query($conn, "DELETE FROM doctor_shifts WHERE id='$id'");
    log_activity($conn, "Deleted shift #$id (doctor #{$shift['doctor_id']}, {$shift['shift_date']})", 'scheduling', $id);
}
header("Location: view_schedule.php");
exit();
