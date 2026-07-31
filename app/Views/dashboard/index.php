<?php
$stats = $stats ?? [];
$progress = (int) ($progress ?? 0);
$recentMembers = $recentMembers ?? [];
$recentResults = $recentResults ?? [];
$statusBreakdown = $statusBreakdown ?? [];
$dashboardTitle = $dashboardTitle ?? 'Super Admin Dashboard';
$dashboardSubtitle = $dashboardSubtitle ?? 'Full platform overview across membership, elections, results, and geography.';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><?= htmlspecialchars($dashboardTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="text-muted mb-0"><?= htmlspecialchars($dashboardSubtitle, ENT_QUOTES, 'UTF-8'); ?></p>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card p-4 h-100">
            <div class="text-muted">Registered Members</div>
            <div class="display-6 fw-bold"><?= (int) ($stats['members'] ?? 0); ?></div>
            <div class="small text-muted"><?= (int) ($stats['approved_members'] ?? 0); ?> approved</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card p-4 h-100">
            <div class="text-muted">Polling Units</div>
            <div class="display-6 fw-bold"><?= (int) ($stats['polling_units'] ?? 0); ?></div>
            <div class="small text-muted"><?= (int) ($stats['districts'] ?? 0); ?> senatorial districts</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card p-4 h-100">
            <div class="text-muted">Open Elections</div>
            <div class="display-6 fw-bold"><?= (int) ($stats['open_elections'] ?? 0); ?></div>
            <div class="small text-muted"><?= (int) ($stats['elections'] ?? 0); ?> total elections</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card p-4 h-100">
            <div class="text-muted">Verified Progress</div>
            <div class="display-6 fw-bold"><?= $progress; ?>%</div>
            <div class="small text-muted"><?= (int) ($stats['results_verified'] ?? 0); ?> verified result sets</div>
        </div>
    </div>
</div>

<div class="card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1">Result workflow</h2>
            <p class="text-muted mb-0">Submitted, verified, approved, and published results in one flow.</p>
        </div>
        <div class="text-end">
            <div class="fw-semibold"><?= (int) ($stats['results_submitted'] ?? 0); ?> submitted</div>
            <div class="text-muted small"><?= (int) ($stats['results_pending'] ?? 0); ?> pending review</div>
        </div>
    </div>
    <div class="progress" style="height: 12px;">
        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progress; ?>%;" aria-valuenow="<?= $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card p-4 h-100">
            <h2 class="h5 mb-3">Recent Members</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Member No.</th>
                            <th>Name</th>
                            <th>LGA</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentMembers as $member): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($member['membership_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars(trim((string) ($member['surname'] ?? '') . ' ' . (string) ($member['first_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($member['lga_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="badge text-bg-secondary"><?= htmlspecialchars((string) ($member['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($recentMembers === []): ?>
                        <tr><td colspan="4" class="text-muted">No members yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card p-4 mb-4">
            <h2 class="h5 mb-3">Result status breakdown</h2>
            <div class="list-group list-group-flush">
                <?php foreach ($statusBreakdown as $row): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><?= htmlspecialchars((string) ($row['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        <strong><?= (int) ($row['total'] ?? 0); ?></strong>
                    </div>
                <?php endforeach; ?>
                <?php if ($statusBreakdown === []): ?>
                    <div class="text-muted">No results submitted yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card p-4">
            <h2 class="h5 mb-3">Recent Results</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Election</th>
                            <th>Polling Unit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentResults as $result): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($result['election_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($result['polling_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="badge text-bg-info"><?= htmlspecialchars((string) ($result['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($recentResults === []): ?>
                        <tr><td colspan="3" class="text-muted">No result records yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
