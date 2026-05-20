<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AfyaCall</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/afyacall-icon.svg">

    <!-- Apple touch icon (iOS home screen) -->
    <link rel="apple-touch-icon" href="/assets/images/afyacall-icon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
</head>
<body>
<!-- Session timeout warning: shows at 25 min, logs out at 30 min -->
<?php if(isset($_SESSION['admin_id'])): ?>
<div class="timeout-bar hidden" id="timeoutBar">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    Your session will expire in <span id="timeoutCountdown">5:00</span> due to inactivity.
    <a href="#" onclick="resetSession(event)" style="color:#c07c1a;font-weight:700;margin-left:4px;">Stay logged in</a>
</div>
<script>
(function(){
    var TIMEOUT   = 1800; // must match PHP SESSION_TIMEOUT
    var WARN_AT   = 300;  // show warning 5 min before
    var remaining = TIMEOUT;

    function pad(n){ return n < 10 ? '0'+n : n; }

    var bar = document.getElementById('timeoutBar');
    var cdEl= document.getElementById('timeoutCountdown');

    // Reset on any user interaction
    var lastReset = Date.now();
    ['mousemove','keydown','click','touchstart'].forEach(function(e){
        document.addEventListener(e, function(){
            lastReset = Date.now();
        }, {passive:true});
    });

    setInterval(function(){
        var idle = Math.floor((Date.now() - lastReset) / 1000);
        remaining = Math.max(0, TIMEOUT - idle);
        if(remaining <= WARN_AT){
            bar.classList.remove('hidden');
            var mins = Math.floor(remaining/60);
            var secs = remaining % 60;
            cdEl.textContent = mins+':'+pad(secs);
        } else {
            bar.classList.add('hidden');
        }
        if(remaining === 0){
            window.location.href = '../auth/logout.php';
        }
    }, 1000);

    window.resetSession = function(e){
        e.preventDefault();
        fetch('../config/ping.php'); // lightweight keepalive
        lastReset = Date.now();
        bar.classList.add('hidden');
    };
})();
</script>
<?php endif; ?>
