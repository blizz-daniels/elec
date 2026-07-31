<?php
$stats = $stats ?? [];
$recentResults = $recentResults ?? [];
$assignedPollingUnits = $assignedPollingUnits ?? [];
$assignedCount = (int) ($assignedCount ?? 0);
$dashboardTitle = $dashboardTitle ?? 'Polling Marshal Dashboard';
$dashboardSubtitle = $dashboardSubtitle ?? 'Polling-station assignment, result submission, and verification workflow.';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><?= htmlspecialchars($dashboardTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="text-muted mb-0"><?= htmlspecialchars($dashboardSubtitle, ENT_QUOTES, 'UTF-8'); ?></p>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3"><div class="card p-4 h-100"><div class="text-muted">Assigned Units</div><div class="display-6 fw-bold"><?= $assignedCount; ?></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card p-4 h-100"><div class="text-muted">Open Elections</div><div class="display-6 fw-bold"><?= (int) ($stats['open_elections'] ?? 0); ?></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card p-4 h-100"><div class="text-muted">Results Submitted</div><div class="display-6 fw-bold"><?= (int) ($stats['results_submitted'] ?? 0); ?></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card p-4 h-100"><div class="text-muted">Verified Progress</div><div class="display-6 fw-bold"><?= (int) (($stats['polling_units'] ?? 0) > 0 ? round((($stats['results_verified'] ?? 0) / ($stats['polling_units'] ?? 1)) * 100) : 0); ?>%</div></div></div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card p-4 h-100">
            <h2 class="h5 mb-3">Assigned Polling Units</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Polling Unit</th><th>Code</th><th>Ward</th><th>LGA</th></tr></thead>
                    <tbody>
                    <?php foreach ($assignedPollingUnits as $unit): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($unit['polling_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($unit['polling_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($unit['ward_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($unit['lga_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($assignedPollingUnits === []): ?><tr><td colspan="4" class="text-muted">No polling unit assignment found yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card p-4 h-100">
            <h2 class="h5 mb-3">Recent Results</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Election</th><th>Polling Unit</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentResults as $result): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($result['election_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($result['polling_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="badge text-bg-info"><?= htmlspecialchars((string) ($result['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($recentResults === []): ?><tr><td colspan="3" class="text-muted">No result records yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
