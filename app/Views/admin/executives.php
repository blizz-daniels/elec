<?php
$stateExecutives = $stateExecutives ?? [];
$senatorialExecutives = $senatorialExecutives ?? [];
$lgaExecutives = $lgaExecutives ?? [];
$wardExecutives = $wardExecutives ?? [];
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Executives</h1>
    <p class="text-muted mb-0">View the state, senatorial, LGA, and ward executive records stored in the database.</p>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card p-4">
            <h2 class="h5 mb-3">State Executives</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Position</th><th>Name</th><th>Phone</th><th>Email</th></tr></thead>
                    <tbody>
                    <?php foreach ($stateExecutives as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $row['position'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($row['user_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($row['phone'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($row['email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($stateExecutives === []): ?>
                        <tr><td colspan="4" class="text-muted">No state executive records.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 h-100">
            <h2 class="h5 mb-3">Senatorial Executives</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Position</th><th>Name</th><th>District</th></tr></thead>
                    <tbody>
                    <?php foreach ($senatorialExecutives as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $row['position'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($row['user_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) $row['district_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($senatorialExecutives === []): ?>
                        <tr><td colspan="3" class="text-muted">No senatorial executive records.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 h-100">
            <h2 class="h5 mb-3">LGA Executives</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Position</th><th>Name</th><th>LGA</th></tr></thead>
                    <tbody>
                    <?php foreach ($lgaExecutives as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $row['position'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($row['user_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) $row['lga_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($lgaExecutives === []): ?>
                        <tr><td colspan="3" class="text-muted">No LGA executive records.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 h-100">
            <h2 class="h5 mb-3">Ward Executives</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Position</th><th>Name</th><th>Ward</th></tr></thead>
                    <tbody>
                    <?php foreach ($wardExecutives as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $row['position'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($row['user_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) $row['ward_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($wardExecutives === []): ?>
                        <tr><td colspan="3" class="text-muted">No ward executive records.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
