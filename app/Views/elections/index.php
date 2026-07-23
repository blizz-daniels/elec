<?php
$elections = $elections ?? [];
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Elections</h1>
    <p class="text-muted mb-0">Create elections and move them through the live cycle.</p>
</div>

<div class="card p-4 mb-4">
    <h2 class="h5 mb-3">Create Election</h2>
    <form method="post" action="<?= url('/elections'); ?>" class="row g-3">
        <?= csrf_field(); ?>
        <input type="hidden" name="action" value="create">
        <div class="col-md-4">
            <label class="form-label">Election name</label>
            <input class="form-control" name="name" placeholder="General Election" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Date</label>
            <input class="form-control" type="date" name="election_date" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Type</label>
            <input class="form-control" name="election_type" placeholder="General / Primary" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Description</label>
            <input class="form-control" name="description" placeholder="Optional">
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Create election</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Candidates</th>
                    <th>Results</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($elections as $election): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars((string) $election['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-muted small"><?= htmlspecialchars((string) ($election['description'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </td>
                    <td><?= htmlspecialchars((string) $election['election_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) $election['election_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="badge text-bg-secondary"><?= htmlspecialchars((string) $election['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td><?= (int) ($election['candidate_count'] ?? 0); ?></td>
                    <td><?= (int) ($election['result_count'] ?? 0); ?></td>
                    <td class="text-end">
                        <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                            <form method="post" action="<?= url('/elections'); ?>">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="action" value="status">
                                <input type="hidden" name="election_id" value="<?= (int) $election['id']; ?>">
                                <select class="form-select form-select-sm d-inline-block w-auto" name="status">
                                    <?php foreach (['draft', 'open', 'closed', 'archived'] as $status): ?>
                                        <option value="<?= $status; ?>" <?= $election['status'] === $status ? 'selected' : ''; ?>><?= ucfirst($status); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-outline-primary" type="submit">Update</button>
                            </form>
                            <form method="post" action="<?= url('/elections'); ?>" onsubmit="return confirm('Delete this election?');">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="election_id" value="<?= (int) $election['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($elections === []): ?>
                <tr><td colspan="7" class="text-muted">No elections yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
