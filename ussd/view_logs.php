<?php
include("../config/session.php");
include("../config/database.php");

// ── FILTERS ───────────────────────────────────────────────
$where   = [];
$search  = trim($_GET['search']  ?? '');
$lang    = $_GET['lang']         ?? '';
$date_from = $_GET['date_from']  ?? '';
$date_to   = $_GET['date_to']    ?? '';

if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where[] = "(phone_number LIKE '%$s%' OR session_id LIKE '%$s%' OR user_input LIKE '%$s%')";
}
if ($lang === 'Swahili' || $lang === 'English') {
    $l = mysqli_real_escape_string($conn, $lang);
    $where[] = "language = '$l'";
}
if ($date_from !== '') {
    $df = mysqli_real_escape_string($conn, $date_from);
    $where[] = "DATE(created_at) >= '$df'";
}
if ($date_to !== '') {
    $dt = mysqli_real_escape_string($conn, $date_to);
    $where[] = "DATE(created_at) <= '$dt'";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// ── PAGINATION ────────────────────────────────────────────
$per_page   = 20;
$page       = max(1, intval($_GET['page'] ?? 1));
$offset     = ($page - 1) * $per_page;

$total_row  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM ussd_logs $where_sql"));
$total      = $total_row['t'];
$total_pages = max(1, ceil($total / $per_page));

$result = mysqli_query($conn, "
    SELECT * FROM ussd_logs
    $where_sql
    ORDER BY created_at DESC
    LIMIT $per_page OFFSET $offset
");

// ── SUMMARY STATS ─────────────────────────────────────────
$stat_total   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM ussd_logs"))['t'];
$stat_today   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM ussd_logs WHERE DATE(created_at)=CURDATE()"))['t'];
$stat_sw      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM ussd_logs WHERE language='Swahili'"))['t'];
$stat_en      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM ussd_logs WHERE language='English'"))['t'];
$stat_success = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM ussd_logs WHERE response LIKE 'END %'"))['t'];
$stat_cont    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM ussd_logs WHERE response LIKE 'CON %'"))['t'];

// Build query string for pagination links
$qs_parts = [];
if ($search)    $qs_parts[] = "search=" . urlencode($search);
if ($lang)      $qs_parts[] = "lang="   . urlencode($lang);
if ($date_from) $qs_parts[] = "date_from=" . urlencode($date_from);
if ($date_to)   $qs_parts[] = "date_to="   . urlencode($date_to);
$qs_base = $qs_parts ? '&' . implode('&', $qs_parts) : '';
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title">USSD Logs</div>
                <div class="page-subtitle">All USSD sessions and request history</div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="stats-grid" style="margin-bottom:24px;">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.06 6.06l1.77-1.77a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </div>
                <div><div class="stat-label">Total Requests</div><div class="stat-value"><?php echo number_format($stat_total); ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div><div class="stat-label">Today</div><div class="stat-value"><?php echo number_format($stat_today); ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div><div class="stat-label">Completed (END)</div><div class="stat-value"><?php echo number_format($stat_success); ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <div>
                    <div class="stat-label">Languages</div>
                    <div class="stat-value" style="font-size:1rem;line-height:1.6;margin-top:2px;">
                        <span style="color:var(--primary);">SW <?php echo number_format($stat_sw); ?></span>
                        <span style="color:var(--text-muted);font-size:.75rem;margin:0 4px;">/</span>
                        <span style="color:#3b5bdb;">EN <?php echo number_format($stat_en); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card card-body" style="margin-bottom:20px;">
            <form method="GET" class="filter-row">
                <div class="filter-group">
                    <label class="form-label">Search</label>
                    <div class="search-wrap">
                        <svg class="search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="search" class="form-control search-input" placeholder="Phone, session ID or input…" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="filter-group" style="min-width:140px;">
                    <label class="form-label">Language</label>
                    <select name="lang" class="form-control">
                        <option value="">All</option>
                        <option value="Swahili"  <?php if($lang==='Swahili')  echo 'selected'; ?>>Swahili</option>
                        <option value="English"  <?php if($lang==='English')  echo 'selected'; ?>>English</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="filter-group">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div class="filter-group filter-actions">
                    <label class="form-label">&nbsp;</label>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="view_logs.php" class="btn btn-outline">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results count -->
        <div style="font-size:.82rem;color:var(--text-muted);margin-bottom:10px;">
            Showing <?php echo number_format(min($offset+1,$total)); ?>–<?php echo number_format(min($offset+$per_page,$total)); ?> of <?php echo number_format($total); ?> entries
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Phone</th>
                        <th>Session ID</th>
                        <th>Input</th>
                        <th>Language</th>
                        <th>Response Type</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($result) == 0): ?>
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07"/></svg>
                            <h3>No logs found</h3>
                            <p>No USSD requests match your filters</p>
                        </div>
                    </td></tr>
                <?php else: ?>
                <?php while($log = mysqli_fetch_assoc($result)):
                    $is_end = str_starts_with($log['response'], 'END');
                    $is_con = str_starts_with($log['response'], 'CON');
                ?>
                    <tr>
                        <td style="color:var(--text-muted);font-size:.78rem;"><?php echo $log['id']; ?></td>
                        <td style="font-family:var(--font-mono);font-weight:600;font-size:.83rem;"><?php echo htmlspecialchars($log['phone_number'] ?: '—'); ?></td>
                        <td style="font-family:var(--font-mono);font-size:.72rem;color:var(--text-muted);max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo htmlspecialchars($log['session_id']); ?>"><?php echo htmlspecialchars($log['session_id'] ?: '—'); ?></td>
                        <td style="font-family:var(--font-mono);font-size:.82rem;max-width:160px;">
                            <?php echo $log['user_input'] !== '' ? htmlspecialchars($log['user_input']) : '<span style="color:var(--text-muted);">—</span>'; ?>
                        </td>
                        <td>
                            <?php if($log['language'] === 'Swahili'): ?>
                                <span class="badge badge-primary">Swahili</span>
                            <?php elseif($log['language'] === 'English'): ?>
                                <span class="badge badge-blue">English</span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($is_end): ?>
                                <span class="badge badge-success">END</span>
                            <?php elseif($is_con): ?>
                                <span class="badge badge-warning">CON</span>
                            <?php else: ?>
                                <span class="badge badge-danger">ERR</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:.8rem;color:var(--text-muted);white-space:nowrap;"><?php echo htmlspecialchars($log['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?><?php echo $qs_base; ?>" class="page-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end   = min($total_pages, $page + 2);
            if($start > 1)           echo '<span class="page-ellipsis">…</span>';
            for($i = $start; $i <= $end; $i++):
            ?>
                <a href="?page=<?php echo $i; ?><?php echo $qs_base; ?>" class="page-btn <?php if($i==$page) echo 'active'; ?>"><?php echo $i; ?></a>
            <?php endfor;
            if($end < $total_pages)  echo '<span class="page-ellipsis">…</span>'; ?>

            <?php if($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?><?php echo $qs_base; ?>" class="page-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </main>
</div>
<?php include("../includes/footer.php"); ?>
