<?php
include("../config/session.php");
require_role(['admin','doctor']);
include("../config/database.php");
include("../config/activity_log.php");

$id  = intval($_GET['id'] ?? 0);
$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT diagnosis FROM medical_records WHERE id='$id' LIMIT 1"));
if ($row) {
    mysqli_query($conn, "DELETE FROM medical_records WHERE id='$id'");
    log_activity($conn, "Deleted medical record #$id", 'records', $id);
}
header("Location: view_records.php");
exit();
