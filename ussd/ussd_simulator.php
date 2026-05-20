<?php
include("../config/session.php");
include("../config/database.php");
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">USSD Simulator</div>
                <div class="page-subtitle">Test the USSD menu flow in your browser</div>
            </div>
            <a href="menu_builder.php" class="btn btn-outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Builder
            </a>
        </div>

        <div style="display:flex;justify-content:center;">
            <div style="width:100%;max-width:400px;">
                <!-- Phone frame -->
                <div style="background:#1a2333;border-radius:36px;padding:28px 20px;box-shadow:0 20px 60px rgba(0,0,0,.3);">
                    <!-- Speaker -->
                    <div style="width:50px;height:5px;background:#333;border-radius:10px;margin:0 auto 20px;"></div>
                    <!-- Screen -->
                    <div style="background:#0d1117;border-radius:18px;padding:20px;min-height:220px;position:relative;">
                        <div style="font-size:.65rem;color:#4ade80;font-family:var(--font-mono);margin-bottom:12px;letter-spacing:.5px;">AFYACALL USSD</div>
                        <div id="simScreen" style="color:#e2e8f0;font-family:var(--font-mono);font-size:.82rem;white-space:pre-line;min-height:120px;line-height:1.6;"></div>
                        <div id="simStatus" style="position:absolute;top:12px;right:14px;font-size:.6rem;font-family:var(--font-mono);color:#6b7a99;"></div>
                    </div>
                    <!-- Input row -->
                    <div style="margin-top:16px;display:flex;gap:8px;">
                        <input type="text" id="simInput"
                               style="flex:1;padding:10px 14px;border-radius:10px;border:none;background:#2d3748;color:#e2e8f0;font-family:var(--font-mono);font-size:.875rem;outline:none;"
                               placeholder="Type your input…" autocomplete="off">
                        <button onclick="simSend()"
                                style="padding:10px 18px;border-radius:10px;background:var(--primary);color:#fff;border:none;font-family:var(--font);font-weight:700;font-size:.82rem;cursor:pointer;">
                            Send
                        </button>
                    </div>
                    <!-- Keypad-style quick keys -->
                    <div id="quickKeys" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;justify-content:center;"></div>
                    <!-- Reset -->
                    <button onclick="simReset()"
                            style="margin-top:12px;width:100%;padding:8px;border-radius:10px;background:#2d3748;color:#94a3b8;border:none;font-family:var(--font);font-size:.78rem;cursor:pointer;">
                        ↺ Reset Session
                    </button>
                </div>

                <!-- Session trail -->
                <div class="card card-body" style="margin-top:20px;">
                    <div style="font-weight:700;font-size:.85rem;margin-bottom:10px;">Session Trail</div>
                    <div id="simTrail" style="font-family:var(--font-mono);font-size:.75rem;color:var(--text-muted);max-height:180px;overflow-y:auto;line-height:1.8;"></div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
var simText = '';
var sessionId = 'SIM-' + Date.now();
var phone = '+255700000000';
var ended = false;

function renderScreen(text) {
    // Strip CON/END prefix for display
    var clean = text.replace(/^(CON|END) ?/, '');
    document.getElementById('simScreen').textContent = clean;
    var type = text.startsWith('END') ? 'END' : 'CON';
    document.getElementById('simStatus').textContent = type;
    ended = type === 'END';
    buildQuickKeys(clean);
}

function buildQuickKeys(text) {
    var container = document.getElementById('quickKeys');
    container.innerHTML = '';
    if (ended) return;
    // Find lines starting with a digit (menu options)
    var lines = text.split('\n');
    lines.forEach(function(line) {
        var m = line.match(/^(\d+)[.\)]/);
        if (m) {
            var btn = document.createElement('button');
            btn.textContent = m[1];
            btn.style.cssText = 'padding:8px 14px;border-radius:8px;background:#2d3748;color:#e2e8f0;border:none;font-family:var(--font-mono);font-size:.82rem;cursor:pointer;min-width:42px;';
            btn.addEventListener('click', function() {
                document.getElementById('simInput').value = m[1];
                simSend();
            });
            container.appendChild(btn);
        }
    });
}

function addTrail(direction, text) {
    var trail = document.getElementById('simTrail');
    var line = document.createElement('div');
    var color = direction === 'in' ? '#4ade80' : '#94a3b8';
    var arrow = direction === 'in' ? '→' : '←';
    line.innerHTML = '<span style="color:'+color+'">'+arrow+'</span> ' +
                     text.replace(/\n/g,' / ').replace(/^(CON|END) /,'');
    trail.appendChild(line);
    trail.scrollTop = trail.scrollHeight;
}

async function simSend() {
    if (ended) { simReset(); return; }
    var input = document.getElementById('simInput').value.trim();
    if (simText === '' && input === '') {
        // First request — dial the code
    } else if (input === '') {
        return;
    }

    if (simText !== '') {
        simText = simText + '*' + input;
    } else if (input !== '') {
        simText = input;
    }

    addTrail('in', input || '(dial)');

    var formData = new FormData();
    formData.append('sessionId', sessionId);
    formData.append('serviceCode', '*384*7#');
    formData.append('phoneNumber', phone);
    formData.append('text', simText);

    try {
        var res = await fetch('index.php', { method: 'POST', body: formData });
        var text = await res.text();
        renderScreen(text);
        addTrail('out', text);
    } catch(e) {
        document.getElementById('simScreen').textContent = 'Error connecting to USSD engine.';
    }

    document.getElementById('simInput').value = '';
    document.getElementById('simInput').focus();
}

function simReset() {
    simText = '';
    sessionId = 'SIM-' + Date.now();
    ended = false;
    document.getElementById('simScreen').textContent = 'Dial *384*7# to begin…';
    document.getElementById('simStatus').textContent = '';
    document.getElementById('simInput').value = '';
    document.getElementById('simTrail').innerHTML = '';
    document.getElementById('quickKeys').innerHTML = '';
}

// Enter key sends
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('simInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') simSend();
    });
    simReset();
    // Auto-dial on load
    simSend();
});
</script>
<?php include("../includes/footer.php"); ?>
