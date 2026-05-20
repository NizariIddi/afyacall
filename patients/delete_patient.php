<?php
include("../config/session.php");
require_role(['admin']);
include("../config/database.php");
include("../config/activity_log.php");

$id  = intval($_GET['id'] ?? 0);
$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name FROM patients WHERE id='$id' LIMIT 1"));
if ($row) {
    mysqli_query($conn, "DELETE FROM patients WHERE id='$id'");
    log_activity($conn, "Deleted patient: {$row['full_name']}", 'patients', $id);
}
header("Location: view_patients.php");
exit();
