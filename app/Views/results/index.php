<?php
$results = $results ?? [];
$elections = $elections ?? [];
$pollingUnits = $pollingUnits ?? [];
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Results</h1>
    <p class="text-muted mb-0">Submit, attach, verify, approve, and publish result records.</p>
</div>

<div class="card p-4 mb-4">
    <h2 class="h5 mb-3">Submit Result</h2>
    <form method="post" action="<?= url('/results'); ?>" enctype="multipart/form-data" class="row g-3">
        <?= csrf_field(); ?>
        <input type="hidden" name="action" value="submit_result">
        <div class="col-md-4">
            <label class="form-label">Election</label>
            <select class="form-select" name="election_id" required>
                <option value="">Select election</option>
                <?php foreach ($elections as $election): ?>
                    <option value="<?= (int) $election['id']; ?>"><?= htmlspecialchars((string) $election['name'], ENT_QUOTES, 'UTF-8'); ?> (<?= htmlspecialchars((string) $election['status'], ENT_QUOTES, 'UTF-8'); ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Polling unit</label>
            <select class="form-select" name="polling_unit_id" data-searchable-select data-searchable-placeholder="Type to search polling units" required>
                <option value="">Select polling unit</option>
                <?php foreach ($pollingUnits as $unit): ?>
                    <option value="<?= (int) $unit['id']; ?>">
                        <?= htmlspecialchars((string) $unit['polling_name'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php if (!empty($unit['polling_code']) || !empty($unit['ward_name']) || !empty($unit['lga_name']) || !empty($unit['district_name'])): ?>
                            -
                            <?= htmlspecialchars(trim(implode(' | ', array_filter([
                                (string) ($unit['polling_code'] ?? ''),
                                (string) ($unit['ward_name'] ?? ''),
                                (string) ($unit['lga_name'] ?? ''),
                                (string) ($unit['district_name'] ?? ''),
                            ]))), ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Attached result sheet</label>
            <input class="form-control" type="file" name="attachment" accept=".pdf,image/*">
        </div>
        <div class="col-md-2">
            <label class="form-label">Accredited</label>
            <input class="form-control" type="number" min="0" name="accredited_voters" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Total votes</label>
            <input class="form-control" type="number" min="0" name="total_votes" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Rejected</label>
            <input class="form-control" type="number" min="0" name="rejected_votes" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Cancelled</label>
            <input class="form-control" type="number" min="0" name="cancelled_votes" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Remarks</label>
            <input class="form-control" name="remarks" placeholder="Optional note">
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Submit result</button>
        </div>
    </form>
</div>

<div class="card p-4">
                    latest_attachment.id AS attachment_id,
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Election</th>
                    <th>Polling Unit</th>
                    <th>Votes</th>
                    <th>Status</th>
                    <th>Attachments</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($results as $result): ?>
                <?php
                $attachmentId = (int) ($result['attachment_id'] ?? 0);
                $attachmentName = trim((string) ($result['attachment_file_name'] ?? ''));
                $attachmentMime = trim((string) ($result['attachment_mime_type'] ?? ''));
                $attachmentSize = (int) ($result['attachment_file_size'] ?? 0);
                $attachmentViewUrl = $attachmentId > 0 ? url('/results/attachment?id=' . $attachmentId . '&mode=open') : '';
                $attachmentDownloadUrl = $attachmentId > 0 ? url('/results/attachment?id=' . $attachmentId . '&mode=download') : '';
                $isImage = $attachmentViewUrl !== '' && str_starts_with($attachmentMime, 'image/');
                $isPdf = $attachmentViewUrl !== '' && $attachmentMime === 'application/pdf';
                ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars((string) $result['election_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-muted small"><?= htmlspecialchars((string) ($result['election_status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    </td>
                    <td>
                        <div><?= htmlspecialchars((string) $result['polling_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-muted small"><?= htmlspecialchars((string) ($result['polling_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    </td>
                    <td>
                        <div>Accredited: <?= (int) $result['accredited_voters']; ?></div>
                        <div class="text-muted small">Total: <?= (int) $result['total_votes']; ?> | Rejected: <?= (int) $result['rejected_votes']; ?> | Cancelled: <?= (int) $result['cancelled_votes']; ?></div>
                    </td>
                    <td><span class="badge text-bg-secondary"><?= htmlspecialchars((string) $result['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td>
                        <div class="d-flex flex-column gap-2">
                            <div class="small text-muted">
                                <?= (int) ($result['attachment_count'] ?? 0); ?> file<?= (int) ($result['attachment_count'] ?? 0) === 1 ? '' : 's'; ?> attached
                            </div>
                            <?php if ($attachmentViewUrl !== ''): ?>
                                <?php if ($isImage): ?>
                                    <a href="<?= htmlspecialchars($attachmentViewUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noreferrer" class="d-inline-block">
                                        <img src="<?= htmlspecialchars($attachmentViewUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Result attachment preview" class="img-thumbnail" style="max-width:120px;max-height:120px;object-fit:cover;">
                                    </a>
                                <?php elseif ($isPdf): ?>
                                    <div class="border rounded overflow-hidden bg-white" style="width:180px;max-width:100%;">
                                        <iframe src="<?= htmlspecialchars($attachmentViewUrl, ENT_QUOTES, 'UTF-8'); ?>" title="PDF preview" style="width:100%;height:220px;border:0;"></iframe>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted small">Preview unavailable for this file type.</div>
                                <?php endif; ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($attachmentViewUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noreferrer">Open</a>
                                    <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($attachmentDownloadUrl, ENT_QUOTES, 'UTF-8'); ?>" download="<?= htmlspecialchars($attachmentName !== '' ? $attachmentName : 'result-attachment', ENT_QUOTES, 'UTF-8'); ?>">Download</a>
                                </div>
                                <div class="text-muted small">
                                    <?= htmlspecialchars($attachmentName !== '' ? $attachmentName : 'Attachment file', ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if ($attachmentSize > 0): ?>
                                        &middot; <?= number_format($attachmentSize / 1024, 1); ?> KB
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small">No attachment uploaded.</div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                            <form method="post" action="<?= url('/results'); ?>">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="action" value="review">
                                <input type="hidden" name="result_id" value="<?= (int) $result['id']; ?>">
                                <select class="form-select form-select-sm d-inline-block w-auto" name="status">
                                    <?php foreach (['verified', 'approved', 'published', 'rejected'] as $status): ?>
                                        <option value="<?= $status; ?>" <?= $result['status'] === $status ? 'selected' : ''; ?>><?= ucfirst($status); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-outline-primary" type="submit">Update</button>
                            </form>
                            <form method="post" action="<?= url('/results'); ?>" onsubmit="return confirm('Delete this result record?');">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="result_id" value="<?= (int) $result['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($results === []): ?>
                <tr><td colspan="6" class="text-muted">No results yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
