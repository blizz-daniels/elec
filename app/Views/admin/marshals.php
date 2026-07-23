<?php
$marshals = $marshals ?? [];
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Marshals</h1>
    <p class="text-muted mb-0">Polling marshal assignments and deployment status.</p>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>Name</th><th>Polling Unit</th><th>Ward</th><th>LGA</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($marshals as $row): ?>
                <tr>
                    <td><?= htmlspecialchars((string) ($row['user_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) $row['polling_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) $row['ward_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) $row['lga_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="badge text-bg-secondary"><?= htmlspecialchars((string) ($row['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($marshals === []): ?>
                <tr><td colspan="5" class="text-muted">No marshal assignments.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
