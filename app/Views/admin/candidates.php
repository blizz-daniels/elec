<?php
$candidates = $candidates ?? [];
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Candidates</h1>
    <p class="text-muted mb-0">Candidate records tied to elections.</p>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>Election</th><th>Position</th><th>Name</th><th>Party</th></tr></thead>
            <tbody>
            <?php foreach ($candidates as $row): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $row['election_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) $row['position'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) ($row['party'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($candidates === []): ?>
                <tr><td colspan="4" class="text-muted">No candidates yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
