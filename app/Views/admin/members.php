<?php
$members = $members ?? [];
$lgas = $lgas ?? [];
$pollingUnits = $pollingUnits ?? [];
$status = (string) ($status ?? '');
$selectedLgaId = (int) ($selectedLgaId ?? 0);
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Members</h1>
        <p class="text-muted mb-0">Review registrations, filter by LGA, and approve or reject records.</p>
    </div>
</div>

<div class="card p-4 mb-4">
    <form method="get" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="">All statuses</option>
                <?php foreach (['pending','approved','rejected','active','inactive'] as $item): ?>
                    <option value="<?= $item; ?>" <?= $status === $item ? 'selected' : ''; ?>><?= ucfirst($item); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">LGA</label>
            <select class="form-select" name="lga_id">
                <option value="0">All LGAs</option>
                <?php foreach ($lgas as $lga): ?>
                    <option value="<?= (int) $lga['id']; ?>" <?= $selectedLgaId === (int) $lga['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars((string) $lga['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
            <button class="btn btn-primary" type="submit">Filter</button>
            <a class="btn btn-outline-secondary" href="<?= url('/admin/members'); ?>">Reset</a>
        </div>
    </form>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Membership</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($member['membership_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <div class="fw-semibold">
                                <?= htmlspecialchars(trim((string) ($member['surname'] ?? '') . ' ' . (string) ($member['first_name'] ?? '') . ' ' . (string) ($member['other_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="text-muted small"><?= htmlspecialchars((string) ($member['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                        </td>
                        <td><?= htmlspecialchars((string) ($member['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <div><?= htmlspecialchars((string) ($member['lga_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="text-muted small"><?= htmlspecialchars((string) ($member['ward_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </td>
                        <td><span class="badge text-bg-secondary"><?= htmlspecialchars((string) ($member['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td class="text-end">
                            <?php
                                $memberStatus = (string) ($member['status'] ?? '');
                                $isMarshal = (bool) ($member['is_marshal'] ?? false);
                                $isApproved = in_array($memberStatus, ['approved', 'active'], true);
                            ?>
                            <div class="d-grid gap-2 justify-content-end">
                                <?php if ($memberStatus === 'approved' && !$isMarshal): ?>
                                    <form method="post" action="<?= url('/admin/members'); ?>" class="d-flex flex-wrap gap-1 justify-content-end align-items-center">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="action" value="promote_marshal">
                                        <input type="hidden" name="member_id" value="<?= (int) $member['id']; ?>">
                                        <select class="form-select form-select-sm" name="polling_unit_id" data-searchable-select data-searchable-placeholder="Type to search polling units" required style="min-width: 210px;">
                                            <option value="">Select polling unit</option>
                                            <?php foreach ($pollingUnits as $pollingUnit): ?>
                                                <option value="<?= (int) $pollingUnit['id']; ?>" <?= (int) ($member['polling_unit_id'] ?? 0) === (int) $pollingUnit['id'] ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars((string) $pollingUnit['polling_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-sm btn-primary" type="submit">Promote Marshal</button>
                                    </form>
                                <?php elseif ($isMarshal): ?>
                                    <div class="text-end small text-muted">Polling marshal assigned</div>
                                <?php endif; ?>
                                <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                    <?php if (!$isApproved): ?>
                                        <form method="post" action="<?= url('/admin/members'); ?>">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="member_id" value="<?= (int) $member['id']; ?>">
                                            <button class="btn btn-sm btn-success" type="submit">Approve</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="btn btn-sm btn-outline-success disabled" aria-disabled="true">Approved</span>
                                    <?php endif; ?>
                                    <form method="post" action="<?= url('/admin/members'); ?>">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="member_id" value="<?= (int) $member['id']; ?>">
                                        <button class="btn btn-sm btn-warning" type="submit">Reject</button>
                                    </form>
                                    <form method="post" action="<?= url('/admin/members'); ?>" onsubmit="return confirm('Delete this member?');">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="member_id" value="<?= (int) $member['id']; ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($members === []): ?>
                    <tr>
                        <td colspan="6" class="text-muted">No members found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
