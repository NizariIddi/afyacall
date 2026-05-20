<?php
/**
 * AfyaCall USSD Engine — Phase 3
 * Supports: dynamic DB menus, multi-language (SW/EN),
 *           health tips, emergency info, appointment lookup, medical records.
 *
 * Africa's Talking POST params:
 *   sessionId, serviceCode, phoneNumber, text, networkCode
 */

include("../config/database.php");
include("../config/lang.php");

// ── Grab telecom POST params ──────────────────────────────────────────────
$sessionId   = $_POST['sessionId']   ?? '';
$serviceCode = $_POST['serviceCode'] ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$text        = trim($_POST['text']   ?? '');

$parts = $text === '' ? [] : explode('*', $text);
$level = count($parts);

// Language detection (first choice: 1=SW, 2=EN)
$lang = detect_lang($parts[0] ?? '');

$response = '';

// ─────────────────────────────────────────────────────────────────────────
// MENU ROUTER
// Priority: check DB custom menus first, then built-in handlers.
// ─────────────────────────────────────────────────────────────────────────

/**
 * Try to resolve a menu response from the ussd_menus table.
 * Returns null if no DB menu matches.
 */
function db_menu(string $path, string $lang, $conn): ?string {
    $p  = mysqli_real_escape_string($conn, $path);
    $l  = mysqli_real_escape_string($conn, $lang);
    $q  = mysqli_query($conn,
        "SELECT * FROM ussd_menus
         WHERE menu_path='$p' AND (language='$l' OR language='both') AND is_active=1
         LIMIT 1");
    if (!$q || mysqli_num_rows($q) === 0) return null;
    $row = mysqli_fetch_assoc($q);
    $prefix = $row['is_terminal'] ? 'END ' : 'CON ';
    return $prefix . $row['menu_text'];
}

// ── LEVEL 0: Language select ──────────────────────────────────────────────
if ($text === '') {
    $response = 'CON ' . t('root_welcome', $lang);

// ── LEVEL 1: Main menu ────────────────────────────────────────────────────
} elseif ($level === 1) {
    // Check DB for a custom override at this path
    $db = db_menu($parts[0], $lang, $conn);
    if ($db) {
        $response = $db;
    } else {
        $response = 'CON ' . t('main_menu', $lang);
    }

// ─────────────────────────────────────────────────────────────────────────
// BRANCH 1: Medical Records  (1*1 or 2*1)
// ─────────────────────────────────────────────────────────────────────────
} elseif ($level === 2 && $parts[1] === '1') {
    $db = db_menu($parts[0].'*'.$parts[1], $lang, $conn);
    $response = $db ?? 'CON ' . t('enter_code_med', $lang);

} elseif ($level === 3 && $parts[1] === '1') {
    $code = mysqli_real_escape_string($conn, strtoupper(trim($parts[2])));
    $q = mysqli_query($conn,
        "SELECT p.full_name, r.diagnosis, r.medication, r.next_appointment
         FROM patients p
         INNER JOIN medical_records r ON p.id = r.patient_id
         WHERE p.patient_code = '$code'
         ORDER BY r.id DESC LIMIT 1");
    if (mysqli_num_rows($q) > 0) {
        $r = mysqli_fetch_assoc($q);
        $response = 'END ' . t('record_found', $lang, [
            'name'      => $r['full_name'],
            'diagnosis' => $r['diagnosis'],
            'medication'=> $r['medication'] ?: t('none', $lang),
            'next'      => $r['next_appointment'] ?: t('none', $lang),
        ]);
    } else {
        $response = 'END ' . t('record_not_found', $lang, ['code' => $code]);
    }

// ─────────────────────────────────────────────────────────────────────────
// BRANCH 2: Appointments  (1*2 or 2*2)
// ─────────────────────────────────────────────────────────────────────────
} elseif ($level === 2 && $parts[1] === '2') {
    $db = db_menu($parts[0].'*'.$parts[1], $lang, $conn);
    $response = $db ?? 'CON ' . t('enter_code_appt', $lang);

} elseif ($level === 3 && $parts[1] === '2') {
    $code = mysqli_real_escape_string($conn, strtoupper(trim($parts[2])));
    $q = mysqli_query($conn,
        "SELECT a.appointment_date, a.status, a.remarks, p.full_name
         FROM appointments a
         INNER JOIN patients p ON a.patient_id = p.id
         WHERE p.patient_code = '$code'
         ORDER BY a.id DESC LIMIT 1");
    if (mysqli_num_rows($q) > 0) {
        $a = mysqli_fetch_assoc($q);
        $response = 'END ' . t('appt_found', $lang, [
            'name'    => $a['full_name'],
            'date'    => $a['appointment_date'],
            'status'  => $a['status'],
            'remarks' => $a['remarks'] ?: t('none', $lang),
        ]);
    } else {
        $response = 'END ' . t('appt_not_found', $lang);
    }

// ─────────────────────────────────────────────────────────────────────────
// BRANCH 3: Health Tips  (1*3 or 2*3)
// ─────────────────────────────────────────────────────────────────────────
} elseif ($level === 2 && $parts[1] === '3') {
    // Check DB first, otherwise show built-in tips menu
    $db = db_menu($parts[0].'*'.$parts[1], $lang, $conn);
    $response = $db ?? 'CON ' . t('tips_menu', $lang);

} elseif ($level === 3 && $parts[1] === '3') {
    $tip_map = ['1'=>'tip_nutrition','2'=>'tip_exercise','3'=>'tip_malaria','4'=>'tip_diabetes','5'=>'tip_hiv'];
    $tip_key = $tip_map[$parts[2]] ?? null;

    if ($tip_key) {
        // Check DB for a custom tip override
        $db = db_menu($parts[0].'*'.$parts[1].'*'.$parts[2], $lang, $conn);
        $response = $db ?? 'END ' . t($tip_key, $lang);
    } else {
        $response = 'END ' . t('invalid', $lang);
    }

// ─────────────────────────────────────────────────────────────────────────
// BRANCH 4: Emergency Alerts  (1*4 or 2*4)
// ─────────────────────────────────────────────────────────────────────────
} elseif ($level === 2 && $parts[1] === '4') {
    $db = db_menu($parts[0].'*'.$parts[1], $lang, $conn);
    $response = $db ?? 'CON ' . t('alert_menu', $lang);

} elseif ($level === 3 && $parts[1] === '4') {
    switch ($parts[2]) {
        case '1':
            $db = db_menu($parts[0].'*'.$parts[1].'*'.$parts[2], $lang, $conn);
            $response = $db ?? 'END ' . t('alert_numbers', $lang);
            break;
        case '2':
            $db = db_menu($parts[0].'*'.$parts[1].'*'.$parts[2], $lang, $conn);
            $response = $db ?? 'END ' . t('alert_hospitals', $lang);
            break;
        case '3':
            // Log the emergency report against this phone number
            $ph = mysqli_real_escape_string($conn, $phoneNumber);
            mysqli_query($conn,
                "INSERT INTO emergency_reports (phone_number, session_id, reported_at)
                 VALUES ('$ph','".mysqli_real_escape_string($conn,$sessionId)."',NOW())");
            $response = 'END ' . t('alert_report', $lang);
            break;
        default:
            $response = 'END ' . t('invalid', $lang);
    }

// ─────────────────────────────────────────────────────────────────────────
// FALLBACK — try DB, else invalid
// ─────────────────────────────────────────────────────────────────────────
} else {
    $db = db_menu(implode('*', $parts), $lang, $conn);
    $response = $db ?? 'END ' . t('invalid', $lang);
}

// ─────────────────────────────────────────────────────────────────────────
// LOG every request
// ─────────────────────────────────────────────────────────────────────────
if ($sessionId !== '') {
    mysqli_query($conn, "
        INSERT INTO ussd_logs (session_id, phone_number, service_code, user_input, language, response)
        VALUES (
            '".mysqli_real_escape_string($conn,$sessionId)."',
            '".mysqli_real_escape_string($conn,$phoneNumber)."',
            '".mysqli_real_escape_string($conn,$serviceCode)."',
            '".mysqli_real_escape_string($conn,$text)."',
            '".($lang===LANG_SW?'Swahili':'English')."',
            '".mysqli_real_escape_string($conn,$response)."'
        )
    ");
}

// ─────────────────────────────────────────────────────────────────────────
// OUTPUT
// ─────────────────────────────────────────────────────────────────────────
header('Content-type: text/plain');
echo $response;
