<?php
/**
 * print_prescription.php — Phase 4
 * Generates a printable prescription from a medical record.
 * Accessed via: print_prescription.php?id=RECORD_ID
 */
include("../config/session.php");
include("../config/database.php");

$record_id = intval($_GET['id'] ?? 0);

// Fetch the record with patient + doctor info
$result = mysqli_query($conn, "
    SELECT medical_records.*,
           patients.full_name   AS patient_name,
           patients.patient_code,
           patients.date_of_birth,
           patients.gender,
           patients.phone       AS patient_phone,
           patients.blood_group,
           admins.full_name     AS doctor_name
    FROM medical_records
    INNER JOIN patients ON medical_records.patient_id = patients.id
    INNER JOIN admins   ON medical_records.recorded_by = admins.id
    WHERE medical_records.id = '$record_id'
    LIMIT 1
");

$r = mysqli_fetch_assoc($result);
if (!$r) { header("Location: view_records.php"); exit(); }

// Age
$age = '';
if ($r['date_of_birth']) {
    $dob = new DateTime($r['date_of_birth']);
    $age = $dob->diff(new DateTime())->y . ' yrs';
}

// Rx number: RX-<year>-<record_id>
$rx_number = 'RX-' . date('Y') . '-' . str_pad($record_id, 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription — <?php echo htmlspecialchars($rx_number); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:      #0d6e6e;
            --primary-dark: #094e4e;
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
            padding: 30px 20px;
        }

        /* ── Screen toolbar ───────────────────────────── */
        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 24px;
            max-width: 720px;
            margin-left: auto;
            margin-right: auto;
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

        /* ── Prescription document ────────────────────── */
        .rx-doc {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            overflow: hidden;
        }

        /* Header stripe */
        .rx-header {
            background: var(--primary-dark);
            padding: 24px 32px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .rx-hospital-name {
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -.3px;
        }
        .rx-hospital-sub {
            font-size: .7rem;
            color: rgba(255,255,255,.5);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .rx-number {
            text-align: right;
        }
        .rx-number-label {
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: rgba(255,255,255,.45);
        }
        .rx-number-value {
            font-family: var(--font-mono);
            font-size: .9rem;
            font-weight: 600;
            margin-top: 2px;
        }

        /* Body */
        .rx-body { padding: 28px 32px; }

        /* Patient + Date row */
        .rx-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px dashed var(--border);
        }
        .rx-section-label {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }
        .rx-field { margin-bottom: 8px; }
        .rx-field-label {
            font-size: .7rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 1px;
        }
        .rx-field-value {
            font-size: .9rem;
            font-weight: 600;
            color: var(--text-main);
        }

        /* Rx symbol + diagnosis */
        .rx-symbol {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
            margin-bottom: 10px;
            font-style: italic;
        }
        .rx-diagnosis-block {
            background: #f8fafc;
            border-left: 4px solid var(--primary);
            border-radius: 0 8px 8px 0;
            padding: 14px 18px;
            margin-bottom: 20px;
        }
        .rx-block-label {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .rx-block-text {
            font-size: .95rem;
            font-weight: 600;
            color: var(--text-main);
            line-height: 1.5;
        }

        /* Medication items */
        .rx-medication-block {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 20px;
        }
        .rx-med-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid var(--border);
        }
        .rx-med-item:last-child { border-bottom: none; padding-bottom: 0; }
        .rx-med-bullet {
            width: 20px;
            height: 20px;
            background: var(--primary);
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px;
        }
        .rx-med-bullet svg { color: #fff; }
        .rx-med-text {
            font-size: .9rem;
            font-weight: 500;
            line-height: 1.5;
        }

        /* Allergies warning */
        .rx-allergy {
            background: #fdeaea;
            border: 1px solid #f5c6c6;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .rx-allergy-icon { color: #e05353; flex-shrink: 0; margin-top: 1px; }
        .rx-allergy-text {
            font-size: .85rem;
            font-weight: 600;
            color: #c94747;
        }

        /* Notes */
        .rx-notes-block {
            background: #fffbf0;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: .85rem;
            color: var(--text-main);
            line-height: 1.6;
        }

        /* Next appointment */
        .rx-next-appt {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: #e6f4f4;
            border-radius: 8px;
            margin-bottom: 28px;
            font-size: .88rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        /* Signature area */
        .rx-signature {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 20px;
            border-top: 1px dashed var(--border);
        }
        .rx-sig-block { text-align: center; }
        .rx-sig-line {
            width: 180px;
            border-bottom: 1.5px solid var(--text-main);
            margin-bottom: 6px;
            height: 40px;
        }
        .rx-sig-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--text-muted);
        }
        .rx-sig-name {
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-main);
            margin-top: 2px;
        }

        /* Footer stripe */
        .rx-footer {
            background: #f8fafc;
            border-top: 1px solid var(--border);
            padding: 12px 32px;
            text-align: center;
            font-size: .68rem;
            color: var(--text-muted);
        }

        /* ── Print styles ─────────────────────────────── */
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .rx-doc {
                box-shadow: none;
                border: 1px solid #ccc;
                border-radius: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<!-- Toolbar -->
<div class="toolbar">
    <button class="btn btn-primary" onclick="window.print()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Print Prescription
    </button>
    <a href="../medical_records/view_records.php" class="btn btn-outline">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to Records
    </a>
</div>

<!-- Prescription Document -->
<div class="rx-doc">

    <!-- Header -->
    <div class="rx-header">
        <div>
            <div class="rx-hospital-name">AfyaCall</div>
            <div class="rx-hospital-sub">Hospital Management System</div>
        </div>
        <div class="rx-number">
            <div class="rx-number-label">Prescription No.</div>
            <div class="rx-number-value"><?php echo htmlspecialchars($rx_number); ?></div>
            <div style="font-size:.68rem;color:rgba(255,255,255,.4);margin-top:4px;">
                Date: <?php echo htmlspecialchars($r['visit_date']); ?>
            </div>
        </div>
    </div>

    <div class="rx-body">

        <!-- Patient + Doctor meta -->
        <div class="rx-meta">
            <div>
                <div class="rx-section-label">Patient Details</div>
                <div class="rx-field">
                    <div class="rx-field-label">Full Name</div>
                    <div class="rx-field-value"><?php echo htmlspecialchars($r['patient_name']); ?></div>
                </div>
                <div class="rx-field">
                    <div class="rx-field-label">Patient Code</div>
                    <div class="rx-field-value" style="font-family:var(--font-mono);font-size:.82rem;"><?php echo htmlspecialchars($r['patient_code']); ?></div>
                </div>
                <div style="display:flex;gap:16px;">
                    <?php if($age): ?>
                    <div class="rx-field">
                        <div class="rx-field-label">Age</div>
                        <div class="rx-field-value"><?php echo $age; ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if($r['gender']): ?>
                    <div class="rx-field">
                        <div class="rx-field-label">Gender</div>
                        <div class="rx-field-value"><?php echo htmlspecialchars($r['gender']); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if($r['blood_group']): ?>
                    <div class="rx-field">
                        <div class="rx-field-label">Blood Group</div>
                        <div class="rx-field-value" style="color:#e05353;"><?php echo htmlspecialchars($r['blood_group']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <div class="rx-section-label">Prescriber</div>
                <div class="rx-field">
                    <div class="rx-field-label">Doctor</div>
                    <div class="rx-field-value">Dr. <?php echo htmlspecialchars($r['doctor_name']); ?></div>
                </div>
                <div class="rx-field">
                    <div class="rx-field-label">Visit Date</div>
                    <div class="rx-field-value"><?php echo htmlspecialchars($r['visit_date']); ?></div>
                </div>
                <?php if($r['patient_phone']): ?>
                <div class="rx-field">
                    <div class="rx-field-label">Patient Phone</div>
                    <div class="rx-field-value"><?php echo htmlspecialchars($r['patient_phone']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rx symbol + Diagnosis -->
        <div class="rx-symbol">℞</div>

        <div class="rx-diagnosis-block">
            <div class="rx-block-label">Diagnosis</div>
            <div class="rx-block-text"><?php echo nl2br(htmlspecialchars($r['diagnosis'])); ?></div>
        </div>

        <!-- Allergies warning -->
        <?php if(!empty($r['allergies'])): ?>
        <div class="rx-allergy">
            <svg class="rx-allergy-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>
                <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#c94747;margin-bottom:2px;">Known Allergies</div>
                <div class="rx-allergy-text"><?php echo htmlspecialchars($r['allergies']); ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Medications -->
        <?php if(!empty($r['medication'])): ?>
        <div class="rx-block-label" style="margin-bottom:10px;">Prescribed Medication</div>
        <div class="rx-medication-block">
            <?php
            // Split medications by newline or semicolon for individual items
            $meds = preg_split('/[\n;]+/', trim($r['medication']));
            $meds = array_filter(array_map('trim', $meds));
            foreach ($meds as $med): ?>
            <div class="rx-med-item">
                <div class="rx-med-bullet">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div class="rx-med-text"><?php echo htmlspecialchars($med); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Notes -->
        <?php if(!empty($r['notes'])): ?>
        <div class="rx-block-label" style="margin-bottom:8px;">Clinical Notes</div>
        <div class="rx-notes-block"><?php echo nl2br(htmlspecialchars($r['notes'])); ?></div>
        <?php endif; ?>

        <!-- Next Appointment -->
        <?php if(!empty($r['next_appointment'])): ?>
        <div class="rx-next-appt">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Next appointment: <strong><?php echo htmlspecialchars($r['next_appointment']); ?></strong>
        </div>
        <?php endif; ?>

        <!-- Signature -->
        <div class="rx-signature">
            <div class="rx-sig-block">
                <div class="rx-sig-line"></div>
                <div class="rx-sig-label">Prescribing Doctor</div>
                <div class="rx-sig-name">Dr. <?php echo htmlspecialchars($r['doctor_name']); ?></div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:.65rem;color:var(--text-muted);margin-bottom:6px;">Dispensed by</div>
                <div class="rx-sig-line"></div>
                <div class="rx-sig-label">Pharmacist / Stamp</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:.65rem;color:var(--text-muted);margin-bottom:4px;">Valid for 30 days from</div>
                <div style="font-size:.88rem;font-weight:700;"><?php echo htmlspecialchars($r['visit_date']); ?></div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="rx-footer">
        AfyaCall Hospital Management &mdash; <?php echo htmlspecialchars($rx_number); ?> &mdash;
        Generated <?php echo date('d M Y, H:i'); ?>
        &mdash; This prescription is valid for 30 days from the date of issue.
    </div>
</div>

</body>
</html>
