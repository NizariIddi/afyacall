<?php
/**
 * patient_card.php — Phase 4
 * Printable patient ID card + QR code.
 * Linked from view_patient.php with ?id=N
 */
include("../config/session.php");
include("../config/database.php");

$id = intval($_GET['id'] ?? 0);
$patient = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM patients WHERE id='$id'"));
if (!$patient) { header("Location: view_patients.php"); exit(); }

// Age
$age = '';
if ($patient['date_of_birth']) {
    $dob = new DateTime($patient['date_of_birth']);
    $age = $dob->diff(new DateTime())->y . ' yrs';
}

// QR value: a URL the scanner will open, e.g. the patient profile page
// Adjust the base URL to match your deployment
$base_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$profile_url = $base_url . '/patients/view_patient.php?id=' . $id;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Card — <?php echo htmlspecialchars($patient['full_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:      #0d6e6e;
            --primary-dark: #094e4e;
            --accent:       #f4a235;
            --danger:       #e05353;
            --text-main:    #1a2333;
            --text-muted:   #6b7a99;
            --border:       #e2e8f0;
            --font:         'Plus Jakarta Sans', sans-serif;
            --font-mono:    'DM Mono', monospace;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font);
            background: #f0f4f8;
            color: var(--text-main);
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ── Screen toolbar ───────────────────────────── */
        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 28px;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-family: var(--font);
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all .18s;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-outline { background: transparent; border: 1.5px solid var(--border); color: var(--text-main); }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }

        /* ── Card ─────────────────────────────────────── */
        .card-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        /* Front face */
        .card-front {
            width: 340px;
            background: linear-gradient(145deg, var(--primary-dark) 0%, var(--primary) 100%);
            border-radius: 16px;
            padding: 22px;
            color: #fff;
            box-shadow: 0 8px 32px rgba(13,110,110,.3);
            position: relative;
            overflow: hidden;
        }
        .card-front::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 140px; height: 140px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
        }
        .card-front::after {
            content: '';
            position: absolute;
            bottom: -50px; left: -30px;
            width: 180px; height: 180px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
        }
        .card-logo {
            font-size: .65rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,.55);
            margin-bottom: 18px;
        }
        .card-name {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -.2px;
            margin-bottom: 4px;
        }
        .card-code {
            font-family: var(--font-mono);
            font-size: .7rem;
            color: rgba(255,255,255,.55);
            margin-bottom: 16px;
        }
        .card-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 18px;
        }
        .card-field-label {
            font-size: .6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: rgba(255,255,255,.45);
            margin-bottom: 2px;
        }
        .card-field-value {
            font-size: .82rem;
            font-weight: 600;
            color: #fff;
        }
        .card-blood {
            position: absolute;
            top: 22px; right: 22px;
            background: rgba(224,83,83,.85);
            color: #fff;
            font-size: .7rem;
            font-weight: 800;
            padding: 4px 9px;
            border-radius: 20px;
        }

        /* Back face */
        .card-back {
            width: 340px;
            background: #fff;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 8px 32px rgba(0,0,0,.08);
            border: 1px solid var(--border);
        }
        .card-back-title {
            font-size: .65rem;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 14px;
        }
        .back-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }
        .back-details { flex: 1; }
        .back-field { margin-bottom: 10px; }
        .back-field-label {
            font-size: .6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: var(--text-muted);
            margin-bottom: 2px;
        }
        .back-field-value {
            font-size: .82rem;
            font-weight: 600;
            color: var(--text-main);
        }
        #qr-canvas {
            border-radius: 8px;
            border: 1px solid var(--border);
            padding: 6px;
            background: #fff;
        }
        .card-footer {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
            font-size: .68rem;
            color: var(--text-muted);
            text-align: center;
        }

        /* ── Print styles ─────────────────────────────── */
        @media print {
            body { background: #fff; padding: 0; display: block; }
            .toolbar { display: none; }
            .card-wrap { gap: 12mm; }
            .card-front, .card-back { box-shadow: none; }
        }
    </style>
</head>
<body>

<!-- Toolbar (hidden on print) -->
<div class="toolbar">
    <button class="btn btn-primary" onclick="window.print()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Print Card
    </button>
    <a href="view_patient.php?id=<?php echo $id; ?>" class="btn btn-outline">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to Profile
    </a>
</div>

<div class="card-wrap">

    <!-- FRONT -->
    <div class="card-front">
        <div class="card-logo">&#9829; AfyaCall — Patient ID</div>
        <?php if($patient['blood_group']): ?>
        <div class="card-blood"><?php echo htmlspecialchars($patient['blood_group']); ?></div>
        <?php endif; ?>
        <div class="card-name"><?php echo htmlspecialchars($patient['full_name']); ?></div>
        <div class="card-code"><?php echo htmlspecialchars($patient['patient_code']); ?></div>
        <div class="card-fields">
            <div>
                <div class="card-field-label">Gender</div>
                <div class="card-field-value"><?php echo htmlspecialchars($patient['gender'] ?: '—'); ?></div>
            </div>
            <div>
                <div class="card-field-label">Age</div>
                <div class="card-field-value"><?php echo $age ?: '—'; ?></div>
            </div>
            <div>
                <div class="card-field-label">Date of Birth</div>
                <div class="card-field-value"><?php echo $patient['date_of_birth'] ? htmlspecialchars($patient['date_of_birth']) : '—'; ?></div>
            </div>
            <div>
                <div class="card-field-label">Phone</div>
                <div class="card-field-value"><?php echo $patient['phone'] ? htmlspecialchars($patient['phone']) : '—'; ?></div>
            </div>
        </div>
    </div>

    <!-- BACK -->
    <div class="card-back">
        <div class="card-back-title">Scan to open profile &amp; medical history</div>
        <div class="back-row">
            <div class="back-details">
                <?php if($patient['address']): ?>
                <div class="back-field">
                    <div class="back-field-label">Address</div>
                    <div class="back-field-value"><?php echo htmlspecialchars($patient['address']); ?></div>
                </div>
                <?php endif; ?>
                <?php if($patient['emergency_contact']): ?>
                <div class="back-field">
                    <div class="back-field-label">Emergency Contact</div>
                    <div class="back-field-value" style="color:var(--danger);"><?php echo htmlspecialchars($patient['emergency_contact']); ?></div>
                </div>
                <?php endif; ?>
                <?php if($patient['blood_group']): ?>
                <div class="back-field">
                    <div class="back-field-label">Blood Group</div>
                    <div class="back-field-value"><?php echo htmlspecialchars($patient['blood_group']); ?></div>
                </div>
                <?php endif; ?>
            </div>
            <canvas id="qr-canvas"></canvas>
        </div>
        <div class="card-footer">
            AfyaCall Hospital Management System &mdash; <?php echo htmlspecialchars($patient['patient_code']); ?>
        </div>
    </div>

</div>

<!-- QR Code generation (qrcode.js, no server dependency) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
var qr = new QRCode(document.getElementById("qr-canvas"), {
    text: <?php echo json_encode($profile_url); ?>,
    width: 90,
    height: 90,
    colorDark: "#0d6e6e",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.M
});
</script>
</body>
</html>
